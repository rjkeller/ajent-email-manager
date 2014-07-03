<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\sql\Database;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\misc\KDate;

use Doctrine\ORM\Mapping as ORM;

/**
 A bunch of email messages grouped together.
 
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Bundle extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $message_id;

	/** @ORM\Column(type="integer") */
//	protected $vendor_id;

	/** @ORM\Column(type="datetime") */
//	protected $start_date;

	/** @ORM\Column(type="datetime") */
//	protected $end_date;

	protected function getTable()
	{
		return "email_bundles";
	}

	public function loadMessageId($message_id)
	{
		return parent::loadQuery("message_id = '". $message_id ."'");
	}

	public function loadBundleForDateRange($vendor_id, $date)
	{
		//XXX: What the heck is this thing doing?
		$wasSuccessful = parent::loadQuery(array(
				'start_date' => array('$gt' => $this->start_date),
				'end_date' => array('$lt' => $this->end_date),
				'vendor_id' => $vendor_id
			)
			, true);
		
		if ($wasSuccessful)
			return true;

		//otherwise, get the most recent bundle
		$db = MongoDb::getDatabase();
		$q = MongoDb::modelQuery($db->email_bundles->find(array(
				"vendor_id" => $vendor_id))
				->sort(array("end_date" => -1)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor");
		$mostRecentBundle = $q->current();

		$vendor = new Vendor();
		$vendor->load($vendor_id);

		$date = new KDate($mostRecentBundle->end_date);

		$message = new EmailMessage();
		$message->load($this->message_id);
		$message->subject = "Your ". $vendor->name ." Newsletter for ". $date->format("F j, Y");
		$message->id = "NULL";

		switch ($vendor->term)
		{
		case 'yearly':
			$message->create();
			$this->message_id = $message->id;

			$this->start_date = $mostRecentBundle->end_date;
			$date->addYear();
			$this->end_date = $date->__toString();
			$this->create();

			break;

		case 'monthly':
			$message->create();
			$this->message_id = $message->id;

			$this->start_date = $mostRecentBundle->end_date;
			$date->addMonth();
			$this->end_date = $date->__toString();
			$this->create();

			break;

		case 'weekly':
			$message->create();
			$this->message_id = $message->id;

			$this->start_date = $mostRecentBundle->end_date;
			$date->addMonth();
			$this->end_date = $date->__toString();
			$this->create();

			break;

		default:
			throw new Exception(
				"No vendor date range specified. This bundle is invalid.");
		}
		return true;
	}
}

