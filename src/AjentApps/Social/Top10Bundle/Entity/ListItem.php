<?php
namespace AjentApps\Social\Top10Bundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ListItem extends DatabaseModel
{
	public function __construct()
	{
		$this->date = date("Y-m-d H:i:s");
		parent::__construct();
	}

	protected function getTable()
	{
		return "list_items";
	}

	public function getFields()
	{
		return array(
			/* type: int */
			"user_id",
			/* type: int */
			"slot_num",
			/* type: int */
			"category_id",
			/* type: int */
			"vendor_id");
	}

	public function tryLoadItem($category_id, $slot_num, $user_id = -1)
	{
		if ($user_id == -1)
			$user_id = SessionManager::$user->id;

		//make sure to keep that (int) in there, or mongo gets goofy.
		return parent::loadQuery(array(
			"category_id" => $category_id,
			"slot_num" => (int)$slot_num,
			"user_id" => $user_id
			), true);
	}

	private $vendor = null;
	public function getVendorName()
	{
		if ($this->vendor == null)
		{
			$vendor = new Vendor();
			$vendor->load($this->vendor_id);
			$this->vendor = $vendor;
		}
		return $this->vendor->getTruncatedVendorName();
	}

	public function getVendor()
	{
		if ($this->vendor == null)
		{
			$vendor = new Vendor();
			$vendor->load($this->vendor_id);
			$this->vendor = $vendor;
		}
		return $this->vendor;
	}

}
