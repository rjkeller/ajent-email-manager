<?php
namespace AjentApps\Social\SocialPostsBundle\Entity;

use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\DatabaseModel;

use Doctrine\ORM\Mapping as ORM;

use Ajent\Mail\MailBundle\Entity\Category;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class WallPost extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="string") */
//	protected $type;

	/** @ORM\Column(type="string") */
//	protected $message;

	/** @ORM\Column(type="string") */
//	protected $product_name;

	/** @ORM\Column(type="string") */
//	protected $url;

	/** @ORM\Column(type="string") */
//	protected $picture_id;

	/** @ORM\Column(type="boolean") */
//	protected $is_favorite;

	/** @ORM\Column(type="string") */
//	protected $category_id;

	/**
	 READ ONLY OUTSIDE OF NAMESPACE MODEL.
	 Comment class will set this automatically.
	 @ORM\Column(type="integer")
	*/
//	protected $num_comments;

	/** @ORM\Column(type="datetime") */
//	protected $date;

	public function __construct()
	{
		parent::__construct();
		$this->type = "message";
		$this->date = date("Y-m-d H:i:s");
		$this->is_favorite = false;
		$this->num_comments = 0;
	}

	protected function getTable()
	{
		return "social_wall_posts";
	}

	public function printr()
	{
		return print_r($this, true);
	}

	public function getCategory()
	{
		if (isset($this->category_id))
		{
			$category = new Category();
			$category->load($this->category_id);
			return $category;
		}
		$category = new Category();
		$category->loadGeneralCategory($this->user_id);
		return $category;
	}

	public function getFields()
	{
		return array(
			"user_id",
			"type",
			"message",
			"is_favorite",
			"date");
	}

	public function isPostedByMe()
	{
		return $this->user_id == SessionManager::$user->id;
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

	public function getDate()
	{
		return date('F jS, Y, g:iA T', strtotime($this->date));
	}
}
