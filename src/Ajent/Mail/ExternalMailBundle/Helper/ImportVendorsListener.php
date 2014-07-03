<?php
namespace Ajent\Mail\ExternalMailBundle\Helper;

use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\PeopleScannerBundle\Helper\PeopleScanner;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ImportVendorsListener implements SendMessageListener
{
	public $vendors;
	public $existing_vendors;

	public function __construct()
	{
		$this->vendors = array();
		$db = MongoDb::getDatabase();
		$this->existing_vendors = MongoDb::modelQuery($db->vendors
				->find(array(
					"user_id" => SessionManager::$user->id))
			, "Ajent\Vendor\VendorBundle\Entity\Vendor");
	}

	public function isParsable($heaaders, $fromAddress)
	{
	    //we don't accept vendors that equal people's names.
	    if (isset($fromAddress->personal) && PeopleScanner::isName($fromAddress->personal))
	        return false;

		foreach ($this->vendors as $v)
		{
			if ($v->email_suffix == Vendor::getDomain($fromAddress->host))
				return false;
		}
		foreach ($this->existing_vendors as $v)
		{
			if ($v->email_suffix == Vendor::getDomain($fromAddress->host))
				return false;
		}
		return true;
	}

	public function parseMessage($subject, $fromAddress, $body)
	{
		$vendor = new Vendor();
		$vendor->num_new_messages = 0;
		$vendor->num_messages = 0;
		$vendor->email_suffix = Vendor::getDomain($fromAddress->host);

		if (!isset($fromAddress->personal) || $fromAddress->personal == "")
			$vendor->name = $this->getCompanyName($fromAddress->host);
		else
			$vendor->name = $fromAddress->personal;
/*
		echo "<p><strong>Vendor Detected in Email!</strong><br>Vendor: ". $vendor->name ." => ". $vendor->email_suffix .
			"<br>Subject: ". $subject .
			"<br>From Address: ". print_r($fromAddress, true) .
			"<br>Body:<br>&nbsp;<br>". $body .
			"<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>";
*/
		$this->vendors[] = $vendor;

		return true;
	}

	/**
	 * For UI reasons, we don't want to show more than this many vendors. Use
	 * this method to check that number.
	 */
	public function isVendorCapHit()
	{
		return sizeof($this->vendors) >= 12;
	}

	public function getCompanyName($host)
	{
		$host = substr($host, 0, strrpos($host, "."));
		if (strpos($host, ".") !== false)
			$host = substr($host, strpos($host, ".")+1);
		return ucfirst($host);
	}
}
