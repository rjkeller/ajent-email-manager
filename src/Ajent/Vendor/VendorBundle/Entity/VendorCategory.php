<?php
namespace Ajent\Vendor\VendorBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;
use Oranges\RedisBundle\Helper\Redis;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class VendorCategory extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $vendor_id;

	/** @ORM\Column(type="string") */
//	protected $category_id;

	/** @ORM\Column(type="string") */
//	protected $is_invisible = false;

	/** @ORM\Column(type="string") */
//	protected $num_messages;

	/** @ORM\Column(type="integer") */
//	protected $num_new_messages;

	/** @ORM\Column(type="datetime") */
//	protected $newest_message;

	protected function getTable()
	{
		return "vendor_categories";
	}

	public function getFields()
	{
		return array(
			"vendor_id",
			"category_id",
			"is_invisible",
			"num_messages",
			"num_new_messages");
	}

	public function __construct()
	{
		parent::__construct();
		$this->is_invisible = false;
		$this->num_messages = 0;
		$this->num_new_messages = 0;
	}

	public function tryLoadInfo($vendor_id, $category_id)
	{
		return parent::loadQuery(array(
			"vendor_id" => $vendor_id,
			"category_id" => $category_id
			),
			true);
	}

	public function loadInfo($vendor_id, $category_id)
	{
		parent::loadQuery(array(
			"vendor_id" => $vendor_id,
			"category_id" => $category_id
			));
	}

	private $vendor = null;
	public function getVendor()
	{
		if ($this->vendor == null)
		{
			$this->vendor = new Vendor();
			$this->vendor->load($this->vendor_id);
		}
		return $this->vendor;
	}
}
