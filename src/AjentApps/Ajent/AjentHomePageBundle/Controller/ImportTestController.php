<?php
namespace AjentApps\Ajent\AjentHomePageBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;

use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\forms\FormUtility;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Ajent\AjentBundle\Form\ImportMailForm;
use Ajent\Mail\MailBundle\Entity\Vendor;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\ExternalMailBundle\Helper\ImportVendorsListener;
use Ajent\Mail\ExternalMailBundle\Helper\SendMessageListener;

class ImportTestController extends RequireLoginController
{
	public function indexAction()
	{
		$scanner = new ExternalAccount();
		$scanner->loadUser();
		$vendors = new ImportVendorsTestListener();
		$scanner->scanForVendors(
			$vendors
		);

		echo "<h1>Vendor Results:</h1><pre>";
		foreach ($vendors->vendors as $v)
		{
			echo "$v->name ($v->email_suffix)<br>";
		}
		echo "</pre>";

		die();
	}

	public function scanSuccessAction()
	{
		$template_vars = array();
		$template_vars['user'] = SessionManager::$user;
		return $this->render("AjentBundle:pages:ScanSuccess.twig.html",
			$template_vars);
		
	}
}

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ImportVendorsTestListener implements SendMessageListener
{
	public $vendors;

	public function __construct()
	{
		$this->vendors = array();
	}

	public function isVendorCapHit()
	{
		return sizeof($this->vendors) >= 10;
	}

	public function isParsable($heaaders, $fromAddress)
	{
		echo "*** <strong>Checking Email for Vendors</strong>:<br>Headers: <pre>". print_r($heaaders, true) ."</pre><br>From Email: ". print_r($fromAddress, true) . "<br>&nbsp;<br>&nbsp;<br>";
		$vendor = new Vendor();

		foreach ($this->vendors as $v)
		{
			if ($v->email_suffix == Vendor::getDomain($fromAddress->host))
			{
				echo "<em>Result</em>: Vendor ". $fromAddress->host . " already found in system, so skipping email.<br>&nbsp;<br>&nbsp;<br>";
				return false;
			}
		}
		echo "<em>Result</em>: Existing vendor not found. Downloading message...<br>&nbsp;<br>";
		return true;

//		return $vendor->tryLoadVendor(SessionManager::$user->id, $fromAddress);
	}

	public function parseMessage($subject, $fromAddress, $body)
	{
		$vendor = new Vendor();
//		$vendor->user_id = SessionManager::$user->id;
		$vendor->email_suffix = Vendor::getDomain($fromAddress->host);
		if (!isset($fromAddress->personal) || $fromAddress->personal == "")
			$vendor->name = $this->getCompanyName($fromAddress->host);
		else
			$vendor->name = $fromAddress->personal;

		$this->vendors[] = $vendor;

		echo "<em>Message Body</em>:<br>$body<br>&nbsp;<br><em>Result:</em> Vendor Detected! Creating vendor for $vendor->name @ $vendor->email_suffix <br>&nbsp;<br>&nbsp;<br>";

		return true;
	}

	public function getCompanyName($host)
	{
		$host = substr($host, 0, strrpos($host, "."));
		if (strpos($host, ".") !== false)
			$host = substr($host, strpos($host, ".")+1);
		return ucfirst($host);
	}
}
