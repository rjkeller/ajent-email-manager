<?php
namespace Oranges\UserBundle\Helper;

use Oranges\sql\SqlUtility;
use Oranges\sql\Database;

use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\ForceUserError;
use Oranges\misc\WgTextTools;
use Oranges\framework\BuildOptions;
use Oranges\MasterContainer;

use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Permissions;

/**
Manages a user's login. Functions you can use:

- startSession() - If the user was previously logged in, loads
	that data (must be done before any HTML is loaded on the 
	page or else you'll get header errors).
- login($username, $password, $remember) - Login the user with
	the specified username/password. $remember = true if you
	want to remember the login beyond the current session.
- logout() - Logs the user out
- personate($username) - ADMIN USE ONLY - logs you in as the
	user taken in the parameter without any login credentials
	necessary. NOTE: This will logout the personated user and
	if the personated user tries to login again, it'll boot
	you out so beware.

 @author R.J. Keller <rjkeller@pixonite.com>
 */
class SessionManager
{
	public static $user;
	public static $permissions;
	public static $session_id;

	public static $logged_in;

	private $dbh;

	public function __construct($dbh)
	{
		$this->dbh = $dbh;
	}

	public function startSession()
	{
		self::$logged_in = false;
		$this->checkLogin();
	}

	/**
	 Allows you to login as a user without knowing their password. Designed for
	 admin use only.
	 */
	public function impersonate($username)
	{
		$q = $this->dbh->fetchAll("
			SELECT
				*
			FROM
				users
			WHERE
				is_deleted = FALSE AND
				username = '". $username ."'
			LIMIT
				1
		");

		//if username does not exist
		if (sizeof($q) <= 0) {
			ForceUserError::$inst->error(new ErrorMetaData("Invalid Username or Password", "The username you entered could not be found. Please enter a valid username."));
			return;
		}
		$user = new User();
		$user->fill($q[0]);
		self::loadUserData($user);
		$q->close();

        $ip = "";
        if (isset($_SERVER["REMOTE_ADDR"]))
		    $ip = $_SERVER["REMOTE_ADDR"];
		self::$session_id = md5(uniqid(rand(), true));

		$cookieExpirationTime = time()+60*60*24*7;
		setcookie("wgsid",	 self::$session_id,	$cookieExpirationTime, "/");
		setcookie("wgsid2",	 self::$user->id,	$cookieExpirationTime, "/");

		$this->dbh->executeUpdate("UPDATE users SET session_ip = '$ip', session_id = '". self::$session_id ."' WHERE id = '". self::$user->id ."'");

		return true;
	}

	public function login($username = null, $password = null, $remember = false)
	{
		//check IP address to make sure this user hasn't exceeded the maximum number of
		//login attempts
		$userip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
		$count = $this->dbh->fetchArray("
		    SELECT
		        COUNT(*)
		    FROM
		        user_attempts
		    WHERE
		        ip = '$userip' AND
		        timestamp >= NOW() - INTERVAL 1 DAY
		    LIMIT
		        21
		");
		$count = $count[0];

		if ($count > 20) {
			UserErrorHandler::$inst->error(new ErrorMetaData("You have exceeded the number of login attempts permitted in one day. Please try again tomorrow."));
			return;
		}

		//if this function was called without login credentials, try and read it
		//from the $_POST data.
		if ($username == null)
		{
			UserErrorHandler::$inst->checkUsername($_POST['user'], new ErrorMetaData("Invalid Username or Password"));
			if (UserErrorHandler::$inst->hasErrors)
			{
				$this->dbh->executeUpdate("
					INSERT INTO
						user_attempts
					VALUES
						(
						NULL,
						?,
						NOW()
					)
					",
					array($userip));
				return false;
			}
			UserErrorHandler::$inst->checkUsername($_POST['pass'], new ErrorMetaData("Invalid Username or Password"));
			if (UserErrorHandler::$inst->hasErrors)
			{
				$this->dbh->executeUpdate("
					INSERT INTO
						user_attempts
					VALUES
						(
						NULL,
						?,
						NOW()
					)
					",
					array($userip));
				return false;
			}
			$username = $_POST['user'];
			$password = $_POST['pass'];
		}

		$hashpassword = "";
		if (isset(BuildOptions::$get['disable_hash_salting']) &&
			BuildOptions::$get['disable_hash_salting'])
			$hashpassword = WgTextTools::hash($password);
		else
			$hashpassword = WgTextTools::hash($password, $username);

		$q = $this->dbh->fetchAll("SELECT * FROM users WHERE is_deleted = FALSE AND username = '$username' AND password = '$hashpassword' LIMIT 1");

		//if username/password is invalid
		if (sizeof($q) <= 0) {
			UserErrorHandler::$inst->error(new ErrorMetaData("Invalid Username or Password"));
			$this->dbh->executeUpdate("
				INSERT INTO
					user_attempts
				VALUES
					(
					NULL,
					?,
					NOW()
				)
				",
				array($userip));
			return;
		}
		//if authentication was successful
		$this->dbh->executeUpdate("
			DELETE FROM
				user_attempts
			WHERE
				ip = ?
			",
			array($userip));

		$user = new User();
		$user->fill($q[0]);
		$user->session_id = \Oranges\misc\WgTextTools::uniqueid();
		self::loadUserData($user);

		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "";
		self::$session_id = md5(uniqid(rand(), true));

		$cookieExpirationTime = time()+60*60*24*7;

		//if this is a unit test, don't set this.
		if (!MasterContainer::$isTesting)
		{
			setcookie("wgsid",	 $user->session_id,	$cookieExpirationTime, "/");
			setcookie("wgsid2",	 $user->id,			$cookieExpirationTime, "/");

			$_COOKIE['wgsid'] = $user->session_id;
			$_COOKIE['wgsid2'] = $user->id;
		}

		$this->dbh->executeUpdate("
			UPDATE
				users
			SET
				session_ip = ?,
				session_id = ?
			WHERE
				id = ?
			",
			array($ip, $user->session_id, $user->id));

		return true;
	}

	public function logout()
	{
		//delete cookies
		$cookieExpirationTime = time()-60*60*24*7;
		setcookie("wgsid",	"", $cookieExpirationTime, "/");
		setcookie("wgsid2", "", $cookieExpirationTime, "/");

		//delete session
		unset($_COOKIE['wgsid']);
		unset($_COOKIE['wgsid2']);

		//update vars
		self::$logged_in = false;
		self::$user	 = null;
	}

	/**
	 Logs in a user, but only for this specific page. So if the user goes to
	 another page, they are logged out.
	 */
	public function sessionOnlyLogin($user, $pass = null)
	{
		if ($user == null)
			return logout();
		if ($pass != null)
		{
			$hashpassword = WgTextTools::hash($pass, $user);

			$q = $this->dbh->fetchAll("
				SELECT
					*
				FROM
					users
				WHERE
					is_deleted = FALSE AND
					username = ? AND
					password = ?
				",
				array($user, $hashpassword));
			if ($q->num_rows > 0)
			{
				$userObj = new User();
				$userObj->loadUsername($user);
				self::loadUserData($userObj);
			}
			else
				return false;

			return true;
		}

		$userObj = new User();
		$userObj->loadUsername($user);
		self::loadUserData($userObj);
	}

	public function sessionOnlyLoginId($id)
	{
		$user = new User();
		$user->load($id);
		self::loadUserData($user);
	}

	/*****************************************/
	/********* INTERNAL USE ONLY *************/
	/********* DO NOT LOOK BELOW!*************/
	/*****************************************/
	private function loadUserData(User $user)
	{
		/* Username and password correct, register session variables */
		self::$user      = $user;
		self::$session_id = $_COOKIE['wgsid'] = $user->session_id;
		self::$logged_in = true;

		$permissions = new Permissions();
		$permissions->loadRole($user->role);
		self::$permissions = $permissions;
	}

	private function checkLogin()
	{
	    $ip = "";
	    if (isset($_SERVER['REMOTE_ADDR']))
	        $ip = $_SERVER["REMOTE_ADDR"];
	    else
	        $ip = "";

		if (isset($_COOKIE['wgsid']))
			self::$session_id = $_COOKIE['wgsid'];

		//if the user has session data
		if (isset($_COOKIE['wgsid']) && isset($_COOKIE['wgsid2']))
		{
			ForceError::$inst->checkId($_COOKIE['wgsid']);
			ForceError::$inst->checkId($_COOKIE['wgsid2']);

			$user = new User();
			//if session data is invalid, log user out.
			if (!$user->loadSessionData(
					$ip,
					$_COOKIE['wgsid'],
					$_COOKIE['wgsid2']))
			{
				//this might be a hacking attempt, so slap the user in the face
				//for being too stupid to crack my system.
				self::logout();
				return false;
			}

			//session data is good, so set the system to the "logged in" state
			self::loadUserData($user);
			return true;
		}

		return false;
	}
}
