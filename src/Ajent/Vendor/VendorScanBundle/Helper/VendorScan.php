<?php
namespace Ajent\Vendor\VendorScanBundle\Helper;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalMessage;

use Ajent\Mail\PeopleScannerBundle\Helper\PeopleScanner;
use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\MailBundle\Entity\EmailAccount;

use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\RedisBundle\Helper\Redis;
use Oranges\UserBundle\Helper\SessionManager;

/**
 Searches through an IMAP account for certain query patterns in the sender.
 Used for sender identification.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class VendorScan
{
	public $vendors;
	public $existing_vendors;

	/**
	 Reads the emails in this external email account and passes each vendor
	 email detected into a listener class.
	*/
	public function scanForVendors(ExternalAccount $emailAccount)
	{
		$redis = Redis::getInstance();

		//check if messages have already been scanned.
		$num_messages = Database::scalarQuery("
			SELECT
				COUNT(*)
			FROM
				vendor_scan_results
			WHERE
				user_id = '". $emailAccount->user_id . "'
			LIMIT
				1
		");
		//if no messages have been scanned, then run the scanner search and
		//load the messages into the database.
		$prev = null;
		if ($num_messages <= 0)
			$prev = $this->loadMessagesIntoScanner($emailAccount);

		$redis->set(SessionManager::$user->id ."-email-scan", 40);

		$q = Database::modelQuery("
			SELECT
				*
			FROM
				vendor_scan_results
			WHERE
				user_id = '". $emailAccount->user_id ."'
			ORDER BY
				message_id DESC
			",
			"Ajent\Vendor\VendorScanBundle\Entity\VendorScanResult")
				->getArray();

		$curMailbox = null;
		$mailbox = null;

		$this->vendors = array();
		$this->existing_vendors = array();

		$len = sizeof($q);
		for ($i = 0; $i < $len; $i++)
		{
			//update the progress bar every so often.
			$progress = (int)((sizeof($this->vendors) / 12) * 58);
			$redis->set(SessionManager::$user->id ."-email-scan", 40 + $progress);
			\OrangesLogger("progress set: ". $progress ." => $i $len");

			$message = $q[$i];

			$folder = $emailAccount->getFolder(
				html_entity_decode($message->mailbox));
			$folder->reopen($prev);
			$prev = $folder;

			$externalMessage = new ExternalMessage($folder->conn,
				$message->message_id, $emailAccount);
			$headers = $externalMessage->getHeaders();

			$subject = "";
			if (isset($headers->subject))
				$subject = strtolower($headers->subject);

			//after we've successfully parsed the message, we'll delete
			//this reference so we don't look at it again.
			Database::query("
				DELETE FROM
					vendor_scan_results
				WHERE
					id = '". $message->id ."'
				");

			//--------------- BEGIN SCAN MESSAGE CONSTRAINTS ---------------//

			//sometimes we get messages without a "From" address (yeah, it's
			//weird, i know :P), so we'll just ignore that message if we run
			//into that again.
			if (!isset($headers->from[0]))
			{
				\OrangesLogger("BAD Message. No from address. Moving on...", "scanParser", array("external_message" => array(
						"mailbox" => $message->mailbox,
						"message_id" => $message->message_id),
					"is_good" => "false")
				);

				continue;
			}


			//we don't scan "Re:" or "Fwd:" messages.
			$firstThree = substr($subject, 0, 3);
			if ($firstThree == "re:" ||
				$firstThree == "fw:" ||
				substr($subject, 0, 4) == "fwd:" ||
				strtolower($headers->from[0]->mailbox) == "mailer-daemon" ||
				strpos($subject, "invite") !== false ||
				strpos($subject, "invitation") !== false)
			{
				\OrangesLogger("BAD Message. We don't scan Re: or Fwd: messages. Moving on...", "scanParser", array("external_message" => array(
						"mailbox" => $message->mailbox,
						"message_id" => $message->message_id,
						"headers" => $headers),
					"is_good" => false)
				);
				continue;
			}

			//we don't accept emails from certain domains.
			if ($headers->from[0]->host == "gmail.com")
			{
				\OrangesLogger("BAD Message. From blocked domain. Moving on...", "scanParser", array("external_message" => array(
						"mailbox" => $message->mailbox,
						"message_id" => $message->message_id,
						"headers" => $headers),
					"is_good" => false)
				);
				continue;
			}

			//we don't accept vendors that equal people's names.
			if (isset($headers->from[0]->personal) &&
				PeopleScanner::isName($headers->from[0]->personal))
			{
				\OrangesLogger("BAD Message. People name hit", "scanParser", array("external_message" => array(
						"mailbox" => $message->mailbox,
						"message_id" => $message->message_id,
						"headers" => $headers),
					"is_good" => false)
				);
				continue;
			}

			//if we've already found this vendor
			$fromAddress = $headers->from[0];
			$isBad = false;
			foreach ($this->vendors as $v)
			{
				if ($v->email_suffix == Vendor::getDomain($fromAddress->host))
				{
					\OrangesLogger("BAD Message. Vendor ". $fromAddress->host ." already found [". $message->message_id ."]. Moving on...", "scanParser", array("external_message" => array(
							"mailbox" => $message->mailbox,
							"message_id" => $message->message_id,
							"headers" => $headers,
							"vendor" => $v->getArray()),
						"is_good" => false)
					);
					$isBad = true;

					//let's make sure we don't scan anymore emails of this vendor.
					if (self::deleteDuplicateVendorEmails($headers, $message, $folder))
					{
						$q = Database::modelQuery("
							SELECT
								*
							FROM
								vendor_scan_results
							WHERE
								user_id = '". $emailAccount->user_id ."'
							ORDER BY
								message_id DESC
							",
							"Ajent\Vendor\VendorScanBundle\Entity\VendorScanResult")
								->getArray();
						$len = sizeof($q);
						$i = 0;
					}

					break;
				}
			}
			foreach ($this->existing_vendors as $v)
			{
				if ($v->email_suffix == Vendor::getDomain($fromAddress->host))
				{
					\OrangesLogger("BAD Message. Vendor already found. Moving on...", "scanParser", array("external_message" => array(
							"mailbox" => $message->mailbox,
							"message_id" => $message->message_id,
							"headers" => $headers,
							"vendor" => $v->getArray()),
						"is_good" => false)
					);
					$isBad = true;

					//let's make sure we don't scan anymore emails of this vendor.
					if (self::deleteDuplicateVendorEmails($headers, $message, $folder))
					{
						$q = Database::modelQuery("
							SELECT
								*
							FROM
								vendor_scan_results
							WHERE
								user_id = '". $emailAccount->user_id ."'
							ORDER BY
								message_id DESC
							",
							"Ajent\Vendor\VendorScanBundle\Entity\VendorScanResult")
								->getArray();
						$len = sizeof($q);
						$i = 0;
					}


					break;
				}
			}
			if ($isBad)
			{
				continue;
			}

			//----------------- END SCAN MESSAGE CONSTRAINTS ---------------//

			//OK, so if we made it this far, then the email scan has found a
			//vendor in the email message and we should load that message.

			\OrangesLogger("SUCCESS Message. Parsing...", "scanParser", array("external_message" => array(
				"mailbox" => $message->mailbox,
				"message_id" => $message->message_id,
				"subject" => $subject,
				"headers" => $headers),
				"is_good" => true)
			);

			$body = "";
			$fromAddress = $headers->from[0];

			$vendor = new Vendor();
			$vendor->num_new_messages = 0;
			$vendor->num_messages = 0;
			$vendor->email_suffix = Vendor::getDomain($fromAddress->host);

			//let's make sure we don't scan anymore emails of this vendor.
			if (self::deleteDuplicateVendorEmails($headers, $message, $folder))
			{
				$q = Database::modelQuery("
					SELECT
						*
					FROM
						vendor_scan_results
					WHERE
						user_id = '". $emailAccount->user_id ."'
					ORDER BY
						message_id DESC
					",
					"Ajent\Vendor\VendorScanBundle\Entity\VendorScanResult")
						->getArray();
					$len = sizeof($q);
					$i = 0;
			}

			if (!isset($fromAddress->personal) || $fromAddress->personal == "")
				$vendor->name = $this->getCompanyName($fromAddress->host);
			else
				$vendor->name = $fromAddress->personal;
			$this->vendors[] = $vendor;


			//so because our performance sucks ass, we're going to cap the scan
			//to 12 vendors.
			if (sizeof($this->vendors) > 12)
			{
				\OrangesLogger("Scan limit hit. We're done here.", "scanParser", array("external_message" => array(
						"mailbox" => $message->mailbox,
						"message_id" => $message->message_id),
					"is_good" => "neutral")
				);

				break;
			}
		}
		$prev->close();

		$num_messages = Database::scalarQuery("
			SELECT
				COUNT(*)
			FROM
				vendor_scan_results
			WHERE
				user_id = '". $emailAccount->user_id . "'
			LIMIT 1
		");
		return $num_messages > 0;
	}

	private static function deleteDuplicateVendorEmails($headers, $message, $folder)
	{
		$fromAddress = $headers->from[0];
		$fromEmail = $fromAddress->mailbox ."@". $fromAddress->host;
		$delMe = $folder->searchMessages(array(
				"FROM \"". $fromEmail ."\""
			));

		$wasSomeDeleted = false;
		$ids = "";
		foreach ($delMe as $delMeMessage)
		{
			if ($ids == "")
				$ids .= "'". $delMeMessage->message_id ."'";
			else
				$ids .= ", '". $delMeMessage->message_id ."'";
			$wasSomeDeleted = true;
		}
		if ($wasSomeDeleted)
		{
			Database::query("
				DELETE FROM
					vendor_scan_results
				WHERE
					message_id IN (". $ids .") AND
					user_id = '". $message->user_id ."' AND
					mailbox = '". htmlentities($message->mailbox) ."'
				");
		}
		return $wasSomeDeleted;
		
	}

	/**
	 Deletes ignored vendors for the specified user.
	*/
	public static function cleanUpVendors($user_id)
	{
		$db = MongoDb::getDatabase();

		//clean up some of the vendors
		$db->vendors->remove(
			array("user_id" => $user_id,
			'$or' => array(
				array('is_ignored' => true),
				array('pendingAddToAjent' => false, 'is_invisible' => true)
				))
			);
	}

	/**
	 Imports the first 3 emails from each vendor in this user's account, and
	 then sets the email system to not import emails before the most recent
	 email the user has received.
	
	 The idea is to import a couple of emails for the user when they register
	 for the first time, 
	
	 @return boolean - Returns true if we were successfully able to open the
		user's email box. If the PHP imap_open function fails to open the
		user's inbox, this function returns false. See the PHP documentation on
		imap_open: http://php.net/manual/en/function.imap-open.php
	*/
	public static function importVendorStarterEmails(ExternalAccount $extAct, $vendorList)
	{
		$emailAccount = new EmailAccount();
		$emailAccount->loadUser($extAct->user_id);

        $folders = $extAct->getFolders();
		$syncIds = array();

		$numMessagesImported = array();

		$prev = null;

		$createMe = array();

        foreach ($folders as $mailbox)
        {
            $mailbox->reopen($prev);
			$prev = $mailbox;

    		$date = date('d-M-Y', strtotime('-1 month'));

            foreach ($vendorList as $vendor)
            {
				if (!isset($numMessagesImported[$vendor->id]))
					$numMessagesImported[$vendor->id] = 0;

				//cap overall message imports to 10 for this vendor.
				if ($numMessagesImported[$vendor->id] >= 10)
					continue;

        		//get all emails in the last month.
        		$allMessages = $mailbox->searchMessages(array("SINCE \"". $date ."\" FROM \"". $vendor->email_suffix ."\""));

        		$len = 3;
        		//if we're received less than 3 messages in the last month, then just
        		//retrieve the last 3 messages.
        		if (sizeof($allMessages) < 3)
        		{
        			$allMessages = $mailbox->searchMessages(array(
        				"FROM \"". $vendor->email_suffix ."\""));

        			if (sizeof($allMessages) < 3)
        				$len = sizeof($allMessages);
        		}

        		foreach ($allMessages as $msg)
        		{
					if ($numMessagesImported[$vendor->id] > 10)
						break;
					$numMessagesImported[$vendor->id]++;
        			$createMe[] = $msg->copyToEmailAccount($emailAccount);
        		}
        	}

            $syncIds[$mailbox->getFolderName()] = $mailbox->getNumMessages();
        }
		$prev->close();

		if (!empty($createMe))
		{
			$createMe[0]->bulkCreate($createMe);
		}

		//now that the initial sync is complete, only import messages after
		//this point.
		$extAct->sync_messages = $syncIds;
		$extAct->save();

		//active the vendor Switch on the external mail server
		foreach ($vendorList as $vendor)
        {
        	$vendor->is_switch = true;
			$vendor->save();
		}
		return true;
	}

	//--------------- PRIVATE FUNCTIONS ----------------//
	private function loadMessagesIntoScanner(ExternalAccount $account)
	{
		$redis = Redis::getInstance();

		//this is a hard-coded list of mailboxes to NOT scan for.
		$do_not_scan_list = array("Sent", "Draft", "Spam", "Trash");

		$folders = $account->getFolders();

		//generate SQL queries to insert the results.
		$q = "INSERT INTO vendor_scan_results (user_id, mailbox, message_id) VALUES ";
		$isStart = true;

		$numQueries = 0;
		$prev = null;

		$len = sizeof($folders);
		$i = 0;
		foreach ($folders as $folder)
		{
			$progress = (int)(($i / $len) * 35)+5;
			$redis->set(SessionManager::$user->id ."-email-scan", $progress);
			$i++;
			
			$folder->reopen($prev);
			$prev = $folder;

			$messages = $folder->searchMessages(array(
				"TEXT \"unsubscribe\"",
				"TEXT \"All Rights Reserved\"",
				"TEXT \"click here\"",
				"TEXT \"subscription\""
			));

			//----> start LOGGER
			$debug = null;
			if (!empty($messages))
			{
				$debug = array("conn_string" => $folder->connString);
				foreach ($messages as $msg)
					$debug[] = array($msg->message_id, $folder->connString);
			}
			\OrangesLogger("IMAP Search Successful", "imapSearch", $debug);
			//----> end LOGGER

			foreach ($messages as $msg)
			{
				if ($isStart)
					$q .= "('". $account->user_id ."', '". htmlentities($folder->connString) ."', '". $msg->message_id ."')";
				else
					$q .= ",('". $account->user_id ."', '". htmlentities($folder->connString) ."', '". $msg->message_id ."')";
				$isStart = false;

				//MySQL crashes if we give it too much data at once. so let's
				//cap things to 100 queries.
				$numQueries++;
				if ($numQueries > 100)
				{
					Database::query($q);
					$numQueries = 0;
					$isStart = true;
					$q = "INSERT INTO vendor_scan_results (user_id, mailbox, message_id) VALUES ";
				}
			}
		}

		//if we don't have any scan results, don't run the SQL because the
		//syntax won't be right.
		if (!$isStart)
			Database::query($q);

		return $prev;
	}

	public function getCompanyName($host)
	{
		$host = substr($host, 0, strrpos($host, "."));
		if (strpos($host, ".") !== false)
			$host = substr($host, strpos($host, ".")+1);
		return ucfirst($host);
	}
}
