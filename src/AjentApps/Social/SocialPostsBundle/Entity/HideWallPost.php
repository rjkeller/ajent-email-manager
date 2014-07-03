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
class HideWallPost extends DatabaseModel
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
//	protected $wall_post_id;

	/** @ORM\Column(type="boolean") */
//	protected $is_hidden;

	protected function getTable()
	{
		return "social_hide_wall_posts";
	}
}
