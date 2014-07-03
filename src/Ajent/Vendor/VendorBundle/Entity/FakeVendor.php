<?php
namespace Ajent\Vendor\VendorBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;
use Oranges\RedisBundle\Helper\Redis;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Helper\CategoryCache;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class FakeVendor extends DatabaseModel
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
//	protected $email_suffix;

	/** @ORM\Column(type="string") */
//	protected $name;

	/** @ORM\Column(type="integer") */
//	protected $category_id;

	protected function getTable()
	{
		return "vendors_fake";
	}
}
