<?php
namespace AjentApps\Social\SocialPostsBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class FavoriteSite extends DatabaseModel
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
//	protected $vendor_id;

	/** @ORM\Column(type="string") */
//	protected $url;

	/** @ORM\Column(type="datetime") */
//	protected $date;

	/** @ORM\Column(type="string") */
//	protected $message;

	public function __construct()
	{
		$this->date = date("Y-m-d H:i:s");
		parent::__construct();
	}

	protected function getTable()
	{
		return "favorite_sites";
	}
}
