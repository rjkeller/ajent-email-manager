<?php
namespace AjentApps\Social\SocialPostsBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;

use AjentApps\Social\SocialPostsBundle\Entity\WallPost;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Comment extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $profile_user_id;
	
	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="string") */
//	protected $wall_message_id;

	/** @ORM\Column(type="string") */
//	protected $message;

	/** @ORM\Column(type="datetime") */
//	protected $date;

	public function __construct()
	{
		parent::__construct();
		$this->date = date("Y-m-d H:i:s");
	}

	protected function getTable()
	{
		return "social_wall_comments";
	}

	public function isPostedByMe()
	{
		return $this->user_id == SessionManager::$user->id;
	}

	public function create()
	{
		parent::create();

		Database::query("
			UPDATE
				social_wall_posts
			SET
				num_comments = num_comments + 1
			WHERE
				id = '". $this->wall_message_id ."'
			");
	}

	public function getUsername($profile)
	{
		if ($this->isPostedByMe())
			return "Me";

		$user = new User();
		$user->load($this->user_id);
		return $user->username;
	}

	public function getMessage()
	{
		return html_entity_decode($this->message);
	}

	public function getProduct()
	{
		$msg = new WallPost();
		$msg->load($this->wall_message_id);
		return $msg;
	}

	private $author_user_profile = null;
	public function getAuthorUserProfile()
	{
		if ($this->author_user_profile == null)
		{
			$author_user_profile = new UserProfile();
			$author_user_profile->loadUser($this->user_id);

			$this->author_user_profile = $author_user_profile;
		}

		return $this->author_user_profile;
	}
}
