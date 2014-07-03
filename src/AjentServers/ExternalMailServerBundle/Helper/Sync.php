<?php
namespace AjentServers\ExternalMailServerBundle\Helper;

use Oranges\UserBundle\Entity\User;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\framework\BuildOptions;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

use Ajent\Mail\MailBundle\Event\CategoryMailListener;
use Ajent\Vendor\VendorBundle\Event\VendorCacheListener;
use Ajent\Vendor\VendorBundle\Event\VendorCategoryMailListener;

use AjentServers\ExternalMailServerBundle\Handlers\MoveHandler;
use AjentServers\ExternalMailServerBundle\Handlers\UnsubscribeHandler;

/**
 * Helps sync a user's external emails with their Ajent account.
 */
class Sync
{
	public static function syncUser(ExternalAccount $extAct)
	{
		$db = MongoDb::getDatabase();

		$emailAccount = new EmailAccount();
		$emailAccount->loadUser($extAct->user_id);

		//if this method is ran without the external account being initialized
		if (!isset($extAct->sync_messages))
			$extAct->sync_messages = array();

		//now that the initial sync is complete, only import messages after
		//this point.
		$folders = $extAct->getFolders();
		if (isset($extAct->sync_messages))
			$syncIds = $extAct->sync_messages;
		else
			$syncIds = array();

		$handlers = array(
			"unsubscribe" => new UnsubscribeHandler(),
			"move" => new MoveHandler()
		);




		$delQueries = array();
		$copyQueries = array();
		$vendors = MongoDb::modelQuery($db->vendors->find(
			array("user_id" => $extAct->user_id)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor");

		foreach ($vendors as $vendor)
		{
			print_r($vendor->getArray());
			if (isset($vendor->is_unsubscribed) && $vendor->is_unsubscribed)
			{
				$delQueries[] = "FROM \"". $vendor->email_suffix ."\"";
			}
			else if (isset($vendor->is_switch) && $vendor->is_switch &&
					isset($vendor->is_active) && $vendor->is_active)
			{
				$copyQueries[] = "FROM \"". $vendor->email_suffix ."\"";
			}
		}

		if (empty($delQueries) && empty($copyQueries))
			return;

		//Disable the mail cache, or else it'll try and refresh after every
		//new message, which causes craziness.
		BuildOptions::$get['DisableMailCache'] = true;

		$createMe = array();

		foreach ($folders as $f)
		{
			//open with write access
			if (!$f->open(true))
				continue;

			if (!isset($syncIds[$f->connString]))
			{
				$syncIds[$f->connString] = 1;
			}

			$deleteMessages = array();
			if (!empty($delQueries))
				$deleteMessages = $f->searchMessages($delQueries);


			$moveMessages = array();
			if (!empty($copyQueries))
				$moveMessages = $f->searchMessages($copyQueries);

			echo "SEARCH QUERIES ON FOLDER ". $f->connString ."\n";
			echo "Delete: \n";
			print_r($delQueries);
			echo "Copy: \n";
			print_r($copyQueries);
			echo "SEARCH RESULTS ON FOLDER ". $f->connString ."\n";
			echo "Delete: \n";
			foreach ($deleteMessages as $msg)
			{
				//make sure that this is a new email to delete.
				if ($msg->message_id > $syncIds[$f->connString])
				{
					echo "-- ". $msg->message_id ."\n";
					$msg->delete();
				}
				else
				{
					echo "-- SKIPPING old message ". $msg->message_id ."\n";
				}
			}
			echo "Copy: \n";
			$k = 0;
			foreach ($moveMessages as $msg)
			{
				echo "++ ". $msg->message_id ."\n";

				$createMe[] = $msg->copyToEmailAccount($emailAccount);
				$msg->delete();
			}

/*
			echo "Opening folder ". $f->connString . "\n";
			foreach ($deleteMessages as $msg)
			{
				if ($msg->message_id <= $syncIds[$f->connString])
					continue;

				$handlers['unsubscribe']->receiveMessage($extAct->user_id, $msg);
			}


			foreach ($moveMessages as $msg)
			{
				if ($msg->message_id <= $syncIds[$f->connString])
					continue;

				$handlers['move']->receiveMessage($extAct->user_id, $msg);
			}
*/
			$f->close();

		}

		if (!empty($createMe))
		{
			$createMe[0]->bulkCreate($createMe);
		}
	}
}