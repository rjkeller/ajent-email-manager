<?php
namespace Ajent\AlertBundle\Event;

use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Vendor\VendorBundle\Entity\VendorCategory;
use Ajent\Mail\MailBundle\Event\MailEvent;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\Category;

use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;

/**
 * Event handler for mail alerts.
 * 
 * @author R.J. Keller <rjkeller@pixonite.com>
 */
class AlertMailListener
{
	public function onEmailMessageTrash(MailEvent $event)
	{
		$db = MongoDb::getDatabase();
		$message = $event->getMessage();

		//if this message is moved to the trash, turn off alerts for it.
		if ($message->folder == "trash")
		{
			$db->email_alerts->update(
				array("message_id" => $message->_id),
				array("is_invisible" => true));
		}
		else
		{
			$db->email_alerts->update(
				array("message_id" => $message->_id),
				array("is_invisible" => false));			
		}
	}
}
