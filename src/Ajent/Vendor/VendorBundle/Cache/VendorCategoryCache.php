<?php
namespace Ajent\Vendor\VendorBundle\Cache;

use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Vendor\VendorBundle\Entity\VendorCategory;
use Ajent\Mail\MailBundle\Event\MailEvent;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\Category;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\LoggingBundle\Helper\Logger;

use Ajent\Vendor\VendorBundle\Helper\CategoryCache;

/**
 This event is ran on each email message action. This checks if we need to
 create a new vendor (or make modifications to existing vendors) based on the
 email action.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class VendorCategoryCache
{
	public function create(array $messages)
	{
		\OrangesLogger("Starting vendor category cache update", "VendorCategoryCache");
		$userIds = array();
		foreach ($messages as $message)
		{
			$new_category = new VendorCategory();
			//if this new vendor category combination doesn't already exist,
			//then create it.
			$wasSuccess = $new_category->tryLoadInfo(
				$message->vendor_id, $message->category_id);

			if (!$wasSuccess)
			{
				$new_category->vendor_id = $message->vendor_id;
				$new_category->category_id = $message->category_id;

				//setting this value to eliminate a race condition where this new
				//category might get accidentally deleted if we do a cache refresh
				//at just the wrong time.
				$new_category->num_messages = 1;

				$new_category->create();

				\OrangesLogger("Creating new vendor category...", "VendorCategoryCache",
					array('new_category' => $new_category->getArray(),
						'message' => $message->getArray()));
			}
			else
			{
				\OrangesLogger("VendorCategory already exists!", "VendorCategoryCache",
					array('new_category' => $new_category->getArray(),
						'message' => $message->getArray()));
			}
			$userIds[] = $message->recipient_user_id;
		}
		$userIds = array_unique($userIds);

		foreach ($userIds as $user_id)
		{
			$this->refreshVendorCategoryCache($user_id);
		}
	}

	public function save(array $messages)
	{
		Logger::log("Refreshing vendor category cache...", "mail");

		$userIds = array();
		foreach ($messages as $message)
		{
			$changed = $message->isChanged();

			if (isset($changed['is_read']))
			{
				$userIds[] = $message->recipient_user_id;
			}
			if (isset($changed['category_id']))
			{
				$id = $message->id;
				$vendor_id = $message->vendor_id;


				$new_category = new VendorCategory();
				//if this new vendor category combination doesn't already exist,
				//then create it.
				$wasSuccess = $new_category->tryLoadInfo(
					$vendor_id, $message->category_id);

				if (!$wasSuccess)
				{

					$id = $message->id;
					$vendor_id = $message->vendor_id;

					$new_category = new VendorCategory();
					$new_category->vendor_id = $vendor_id;
					$new_category->category_id = $message->category_id;
					$new_category->is_invisible = false;

					//setting this value to eliminate a race condition where this new
					//category might get accidentally deleted if we do a cache refresh
					//at just the wrong time.
					$new_category->num_messages = 1;

					$new_category->create();
				}

				$userIds[] = $message->recipient_user_id;

				//check for blank categories and kill them.
				$db = MongoDb::getDatabase();
				$q = MongoDb::modelQuery($db->vendor_categories
						->find(array("user_id" => $message->recipient_user_id))
					, "Ajent\Vendor\VendorBundle\Entity\VendorCategory");

				foreach ($q as $categoryVendor)
				{
					//see if there are any messages.
					$num = $db->email_messages->find(array(
						"vendor_id" => $categoryVendor->vendor_id,
						"category_id" => $categoryVendor->category_id
						))->count();

					if ($num <= 0)
						$categoryVendor->delete();
				}

			}
		}

		$userIds = array_unique($userIds);

		foreach ($userIds as $user_id)
		{
			$this->refreshVendorCategoryCache($user_id);
		}

	}

	public function refreshVendorCategoryCache($user_id = null)
	{
		if (isset(BuildOptions::$get['DisableMailCache']))
			return false;

		$refreshAll = $user_id == null;
		$db = MongoDb::getDatabase();

		$whereClause = array();
		if (!$refreshAll)
		{
			$q = $db->vendors->find(array("user_id" => $user_id));
			foreach ($q as $v)
			{
				$whereClause['$or'][] = array("vendor_id" => $v['_id']->__toString());
			}
		}

		$vendorCategories = MongoDb::modelQuery(
			$db->vendor_categories->find($whereClause),
			"Ajent\Vendor\VendorBundle\Entity\VendorCategory");

		foreach ($vendorCategories as $vc)
		{
			$vc->num_new_messages = $db->email_messages->find(array(
				'folder' => array('$ne' => 'trash'),
				'is_read' => false,
				'vendor_id' => $vc->vendor_id,
				'category_id' => $vc->category_id,
				'is_invisible' => false
				))
				->count();
			$vc->num_messages = $db->email_messages->find(array(
				'folder' => array('$ne' => 'trash'),
				'vendor_id' => $vc->vendor_id,
				'category_id' => $vc->category_id,
				'is_invisible' => false
				))
				->count();

			$out = $db->email_messages->find(array(
				'folder' => array('$ne' => 'trash'),
				'vendor_id' => $vc->vendor_id,
				'category_id' => $vc->category_id,
				'is_invisible' => false
				))
				->sort(array("creation_date" => -1))
				->limit(1);
			
			$out = $out->getNext();
			$vc->newest_message = $out['creation_date'];

			$vc->save();
		}

		$whereClause['num_messages'] = 0;
		$db->vendor_categories->update(
			$whereClause,
			array('$set' => array('is_invisible' => true)));
	}
}
