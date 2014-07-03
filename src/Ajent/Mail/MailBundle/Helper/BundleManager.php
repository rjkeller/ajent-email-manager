<?php
namespace Ajent\Mail\MailBundle\Helper;

use Oranges\misc\KDate;
use Oranges\sql\Database;

use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Entity\Bundle;
use Ajent\Mail\MailBundle\Entity\BundleMessage;
use Ajent\Mail\MailBundle\Entity\Vendor;

use Oranges\MongoDbBundle\Helper\MongoDb;

/**
 Utlility class that provides a convenient interface for bundling emails
 together, and managing those bundles in bulk.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class BundleManager
{
	/**
	 Bundles emails with a specific vendor together on a weekly, monthly, or
	 yearly basis (based on what you passed in to $incr).
	
	 @param $vendor_id - The vendor to bundle the emails together.
	 @param $incr - Either the string "monthly", "weekly", "yearly"
	*/
	public static function createBundles($vendor_id, $incr)
	{

		//retrieve the oldest email for this vendor.
		$db = MongoDb::getDatabase();
		$q = MongoDb::modelQuery($db->email_messages
				->find(array("vendor_id" => $vendor_id))
				->sort(array("date" => 1))
				->limit(1),
			"Ajent\Mail\MailBundle\Entity\EmailMessage");

		if ($q->size() <= 0)
			return;

		$oldestEmail = $q->current();
		$oldestDate = new KDate($oldestEmail->date);

		//for each bundle we'll create
		$prevDate = new KDate();
		$curDate = new KDate();

		$vendor = new Vendor();
		$vendor->load($vendor_id);

		self::goBackIncrement($curDate, $incr);

		$isFirstRan = true;
		while ($isFirstRan || !$oldestDate->isAfter($prevDate))
		{
			$isFirstRan = false;
			$bundleMessage = new EmailMessage();
			$bundleMessage->from_address = html_entity_decode($oldestEmail->from_address);
			$bundleMessage->from_name = html_entity_decode($oldestEmail->from_address);
			$bundleMessage->from_email = html_entity_decode($oldestEmail->from_email);
			$bundleMessage->recipient_user_id = $oldestEmail->recipient_user_id;
			$bundleMessage->subject = "Your ". $vendor->name ." Newsletter for ". $prevDate->format("F j, Y");
			$bundleMessage->category_id = $oldestEmail->category_id;
			$bundleMessage->vendor_id = $vendor_id;
			$bundleMessage->type = "bundle";
			$bundleMessage->date = $prevDate->__toString();

			$hasEmails = false;

			$q = MongoDb::modelQuery($db->email_messages
					->find(array("vendor_id" => $vendor_id,
						"date" => array(
							'$gt' => $curDate->mktime(),
							'$lte' => $prevDate->mktime())
						)
					)
					->sort(array("date" => 1))
					->limit(1),
				"Ajent\Mail\MailBundle\Entity\EmailMessage");

			//if there are no emails in this date range, then do nothing. If
			//there are emails in this date range, then create the bundle,
			//hide the emails in this date range, and add them to the bundle.
			if ($q->size() > 0)
			{
				$bundleMessage->create(true);

				$bm = new Bundle();
				$bm->message_id = $bundleMessage->_id;
				$bm->start_date = $curDate->__toString();
				$bm->end_date = $prevDate->__toString();
				$bm->vendor_id = $vendor_id;
				$bm->create();

				foreach ($q as $message)
				{
					$hasEmails = true;
					$message->load($message->_id);
					$message->is_invisible = true;
					$message->save();

					$bundle = new BundleMessage();
					$bundle->bundle_id = $bm->_id;
					$bundle->message_id = $message->_id;
					$bundle->vendor_id = $vendor_id;
					$bundle->create();
				}
			}

			self::goBackIncrement($prevDate, $incr);
			self::goBackIncrement($curDate, $incr);

			$q = null;
			$bundleMessage = null;
		}
	}

	/**
	 Removes the specified increment from the current date passed in. For
	 example, if you pass in today with the value "month", it'll revert the
	 date back to the previous month.
	*/
	private static function goBackIncrement(KDate $date, $incr)
	{
		switch ($incr)
		{
		case 'monthly':
			return $date->removeMonth();
		case 'daily':
			return $date->removeDay();
		case 'weekly':
			return $date->removeDay(7);
		default:
			return $date->removeYear();
		}
	}

	/**
	 Removes all email bundles created for the specified vendor.
	
	 @param $vendor_id - The vendor ID of the bundles to delete.
	*/
	public static function removeBundles($vendor_id)
	{
		//Make all emails in this bundle visible (since it's no longer part of
		//a bundle).
		$db = MongoDb::getDatabase();
		$db->email_messages->update(
			array("vendor_id" => $vendor_id),
			array('$set' => array("is_invisible" => false)),
			false, true
		);

		//for each bundle
		$q = MongoDb::modelQuery($db->email_bundles->find(array(
			"vendor_id" => $vendor_id
			)),
			"Ajent\Mail\MailBundle\Entity\Bundle");

		foreach ($q as $bundle)
		{
			$db->email_bundle_messages->remove(
				array("bundle_id" => $bundle->_id));

			$db->email_messages->remove(
				array("vendor_id" => $vendor_id,
					"type" => "bundle"));

			$bundle->delete();
		}
	}
}