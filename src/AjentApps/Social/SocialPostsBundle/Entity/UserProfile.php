<?php
namespace AjentApps\Social\SocialPostsBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Entity\Contact;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class UserProfile extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="boolean") */
//	protected $is_profile_public;

	/** @ORM\Column(type="boolean") */
//	protected $is_gender_male;

	/** @ORM\Column(type="string") */
//	protected $occupation;

	/** @ORM\Column(type="string") */
//	protected $website;

	/**
	This field is automatically maintained. Please do not manually change, as
	the system will override your new value in the save() and create()
	functions.
	
	@ORM\Column(type="string")
	*/
//	protected $name;

	/** @ORM\Column(type="string") */
//	protected $about_me;

	/** @ORM\Column(type="datetime") */
//	protected $creation_date;

	/** @ORM\Column(type="boolean") */
//	protected $is_deleted;

	/** @ORM\Column(type="string") */
//	protected $picture_id;

//	protected $__EnableModelCache = true;

	public function __construct()
	{
		parent::__construct();
		$this->creation_date = date("Y-m-d H:i:s");
		$this->is_profile_public = false;
		$this->is_gender_male = true;
		$this->name = "";
		$this->is_deleted = false;
	}

	protected function getTable()
	{
		return "social_user_profiles";
	}

	public function isFollower($user_id = null)
	{
		if ($user_id == null)
			$user_id = SessionManager::$user->id;

		$db = MongoDb::getDatabase();

		$count = $db->social_friends->find(array(
				"user_id" => $this->user_id,
				"friend_user_id" => $user_id
			))->count();

		return $count > 0;
	}


	public function loadUser($user = null)
	{
		if ($user == null)
			$user = SessionManager::$user->id;
		$myArray = array("user_id" => $user);
		if (!parent::loadQuery($myArray, true))
		{
			$this->user_id = SessionManager::$user->id;
			$this->create();
		}
	}

	public function create()
	{
		$contact = new Contact();
		$contact->loadUser($this->user_id);

		$this->name = $contact->first_name . " ". $contact->last_name;

		parent::create();
	}

	public function save()
	{
		$contact = new Contact();
		$contact->loadUser($this->user_id);

		$this->name = $contact->first_name . " ". $contact->last_name;

		parent::save();
	}

	public function getProfilePictureUrl()
	{
		return "/bundles/social/images/profile_pic.jpg";
	}

	public function isFriend()
	{
		$friend = new Friend();
		return $friend->tryLoadingFriendUserId($this->user_id);
	}
}
