<?php
namespace Ajent\Vendor\VendorBundle\Event;

use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Mail\MailBundle\Event\MailEvent;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

use Oranges\framework\BuildOptions;
use Oranges\LoggingBundle\Helper\Logger;

use Oranges\MongoDbBundle\Helper\ModelEvent;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class MailEventsListener implements ModelEvent
{
	public function create(array $db_models)
	{
		foreach ($db_models as $message)
		{
			\OrangesLogger("Setting vendor for email...", "VendorSet",
				array(
					'message' => $message->getArray()));

			//sets the vendor. If this returns false, then we shouldn't create the
			//email message, so we'll return false
			if ($this->setVendor($message))
			{
				Logger::log("Loading vendor: Success! => ". $message->vendor_id, "mail");
			}
			else
			{
				Logger::log("Loading vendor: FAIL, deleting email...", "mail");
				//email should not be saved, so clean up and return.
				$message->hardDelete();
				return false;
			}

			Logger::log("Checking for Email bundle...", "mail");


			//check if this email is part of a bundle. If so, then add this email
			//to that bundle.
	/*	XXXrj: alright, this is messed up for now. Forcing no bundle

			if (!$isBundleCreate)
			{
				$count = Database::scalarQuery("
					SELECT
						COUNT(*)
					FROM
						email_bundles
					WHERE
						vendor_id = '". $message->vendor_id ."'
					LIMIT
						1
				");
				if ($count > 0)
				{
					Logger::log("+++ Bundle: Creating...", "mail");
					$bundle = new Bundle();
					$bundle->loadBundleForDateRange($message->vendor_id, $message->date);

					$bundleMessage = new BundleMessage();
					$bundleMessage->bundle_id = $bundle->id;
					$bundleMessage->vendor_id = $message->vendor_id;

					//if all emails of this vendor should be placed in a certain
					//category, then make sure this email belongs to that category.
					$vendor = new Vendor();
					$vendor->load($message->vendor_id);
					if ($vendor->category_id != -1)
					{
						$message->category_id = $vendor->category_id;
						$message->save();
					}

					$bundleMessage->message_id = $message->id;
					$bundleMessage->create();

					$message->is_invisible = true;
					$message->save();
					Logger::log("+++ Bundle: Done!", "mail");
				}
			}
			else
			{
	*/
	/*		}
	*/

			//so something stupid is happening here.
			//alright, this is the email category sanity check.
			if (!isset($message->vendor_id) ||
				$message->vendor_id == -1 ||
				$message->vendor_id == "")
			{
				throw new \Exception("Invalid Vendor ID detected for message: ". 
					print_r($message->getArray(), true));
			}


			Logger::log("Setting Category...", "mail");
			//if all emails of this vendor should be placed in a certain
			//category, then make sure this email belongs to that category.
			$vendor = new Vendor();
			$vendor->load($message->vendor_id);
			if ($vendor->category_id != -1)
			{
				$message->category_id = $vendor->category_id;
			}

			//If we got a category_id for this vendor, then, load it up. If
			//not, load the Miscellaneous category.
			$vendor = new Vendor();
			$vendor->load($message->vendor_id);

			if (isset($vendor->category_id) &&
				$vendor->category_id != -1)
			{
				$message->category_id = $vendor->category_id;
			}
			else
			{
				$c = new Category();
				$c->loadGeneralCategory($message->recipient_user_id);
				$message->category_id = $c->id;
			}
		}
	}

	public function save(array $db_models)
	{	}

	public function delete(array $db_models)
	{	}

	/**
	 Sets the vendor for this user if a vendor is detected in this email. This
	 method assumes that all the fields except for vendor_id and id are
	 already set.
	*/
	private function setVendor($message, $user_id = null)
	{
		if (isset($message->vendor_id) && $message->vendor_id != -1 && !empty($message->vendor_id))
		{
			\OrangesLogger("skipping out");
			return true;
		}

		$emailAccount = new EmailAccount();
		$emailAccount->loadUser($message->recipient_user_id);

		//if the name and email hasn't yet been extracted from the email
		//headers, then extract that information now.
		if (empty($message->from_email))
			$message->parseFromEmail();

		//check for an existing vendor match for this email. If it exists, then
		//set that equal to the vendor and return.
		$vendor = new Vendor();
		if ($vendor->tryLoadVendor(
				$emailAccount->user_id,
				$message->from_email))
		{
			$message->vendor_id = $vendor->id;

			//yeah, i know, this is goofy, but sometimes it seems to
			//happen.
			if (!isset($vendor->category_id))
			{
				$category = new Category();
				$category->loadGeneralCategory($emailAccount->user_id);

				$vendor->category_id = $category->id;
				$vendor->save();
			}

			$message->category_id = $vendor->category_id;
			\OrangesLogger("hit vendor!");
			return true;
		}
		\OrangesLogger("making new vendor");

		//If we reached this far, then this is a new vendor the user must've
		//subscribed to on their own, so let's add it to the vendor list.
		$email_addr = explode('@', $message->from_email);

		if (!isset($email_addr[1]))
		{
			echo "WARNING: invalid from email address: ". $message->from_email ."\n";
		}
		$vendor = new Vendor();
		$vendor->email_suffix = $message->from_email;
		if ($message->from_name == "")
			$vendor->name = $message->from_email;
		else
			$vendor->name = $message->from_name;
		$vendor->user_id = $emailAccount->user_id;
		$vendor->create();

		$message->vendor_id = $vendor->id;
		return true;
	}

}
