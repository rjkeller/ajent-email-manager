<?php
namespace Oranges\user\Form;

use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\ForceUserError;
use Oranges\errorHandling\ErrorMetaData;

use Oranges\sql\SqlUtility;
use Oranges\sql\SqlTable;
use Oranges\sql\Database;

use Oranges\misc\Mailer;

use \Build_Options;

/**
 This form processes various functions that are available when the system is
 only available for private invite. For example, beta invite authentication,
 email address submission (for more info on a beta invite) is all handled
 through this form.

 Note that when this form is submitted, execution will cease with an error if
 the beta invite was invalid.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class InviteCodeForm
{
	public function __construct()
	{
		//whether or not an invite code was submitted by the user
		$hasInviteCode = !empty($_POST['invitekey']) &&
			$_POST['invitekey'] != "Invite Code";
		//whether or not the user entered their email address into the
		//"more information" field.
		$hasEmail = !empty($_POST['email']) &&
			$_POST['email'] != "Email Address";

		if (!$hasInviteCode && $hasEmail)
		{
		    ForceError::$inst->checkEmail($_POST['email'], false,
				new ErrorMetaData("Invalid Email Address",
					"The email address you entered is invalid. Please enter
					another email address.<br>&nbsp;<br><a href=\"/\">
					Back to Login Page</a>", ""));

		    $count = SqlUtility::getCount("
				SELECT
					COUNT(*)
				FROM
					beta_emails
				WHERE
					email = '". $_POST['email'] ."'
				LIMIT
					1
			");

			//if the user has entered a valid email, then add it to the
			//beta_emails table in MySQL
		    if (count <= 0)
		    {
		        $s = new SqlTable("beta_emails");
		        $s->email = $_POST['email'];
		        $s->tableInsert();

				$q = Database::query("
					SELECT
						email
					FROM
						user
					WHERE
						role = 'RegistrarAdmin'
				");

				//send an email to all the registrar admin's in the WordGrab
				//system to alert them of this beta invite request.
				//
				//XXXrj: Hopefully in the future we might come up with
				//something better than this?
				while ($user = $q->fetch_object())
				{
					Mailer::sendMail($user->email,
						Build_Options::$COMPANY_NAME_SHORT ." Beta Request Email ",
						Build_Options::$COMPANY_NAME_SHORT ." User ".
							$_POST['email'] ." has requested a beta invite!");
				}

		    }
		    header("Location: /login?s=g");
		    die();
		}

		if (!$hasInviteCode)
		{
		    header("Location: /login?s=i");
		    die();
		}

		//search for invite key;
		ForceUserError::$inst->checkId($_POST['invitekey'], false,
			new ErrorMetaData("Invalid Invite Code",
				"The invite code you entered is invalid. Please try again."));

		$query = Database::query("
			SELECT
				*
			FROM
				beta_invite
			WHERE
				id = '". $_POST['invitekey'] ."' AND
				status = 'unused'
			LIMIT
				1
		");
		if ($query->num_rows <= 0)
		{
			ForceUserError::$inst->error(
				new ErrorMetaData("Invalid Invite Code",
					"The invite code you entered is invalid. Please try again.
					<br><a href=\"/\">Back to ".
					Build_Options::$COMPANY_NAME_SHORT ." Login</a>"));
		}

		$invite = $query->fetch_object();
		$query->close();

		if ($invite->status != "unused")
		{
			ForceUserError::$inst->error(
				new ErrorMetaData("Invalid Invite Code",
					"The invite code you entered is invalid. Please try again."
			));
		}
	}
}
