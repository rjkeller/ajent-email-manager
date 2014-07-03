<?php
namespace AjentApps\Social\SocialPostsBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Follower extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="integer") */
//	protected $follower_user_id;

	/** @ORM\Column(type="datetime") */
//	protected $creation_date;

	public function __construct()
	{
		parent::__construct();
		$this->creation_date = date("Y-m-d H:i:s");
	}

	protected function getTable()
	{
		return "social_followers";
	}

	private $friend_user_profile = null;
	public function getFriendUserProfile()
	{
		if ($this->friend_user_profile == null)
		{
			$friend_user_profile = new UserProfile();
			$friend_user_profile->loadUser($this->friend_user_id);

			$this->friend_user_profile = $friend_user_profile;
		}

		return $this->friend_user_profile;
	}
}
