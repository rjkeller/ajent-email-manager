<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\InternalException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;

class AddVendorManualForm extends WgForm
{
	/** @Assert\Type("string") */
	public $vendor_name;

	public function getName()
	{
		return "AddVendorManual";
	}

	public function submitForm()
	{
		$vendorScan = new VendorScan();

		//if this vendor already exists, skip it.
		$vendor = new Vendor();

		$doesVendorExist = false;
		if (strpos($this->vendor_name, '@') === false)
		{
			$doesVendorExist = $vendor->tryLoadEmailSuffix($this->vendor_name);
		}
		else
		{
			$doesVendorExist = $vendor->tryLoadVendor(SessionManager::$user->id,
				$this->vendor_name);
		}
		
		if ($doesVendorExist)
		{
			MessageBoxHandler::happy("This subscription already exists in your list.");
			return;
		}


		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser();
		$folders = $externalAccount->getFolders();

		$isVendorFound = false;

		$prev = null;
		foreach ($folders as $f)
		{
			$f->reopen($prev);

			$messages = $f->searchMessages(array(
				"FROM \"". $this->vendor_name ."\""
			));

			if (sizeof($messages) > 0)
			{
				$msg = $messages[0];
				$headers = $msg->getHeaders();

				$fromAddress = $headers->from[0];

				$vendor = new Vendor();
				$vendor->num_new_messages = 0;
				$vendor->num_messages = 0;
				$vendor->email_suffix = Vendor::getDomain($fromAddress->host);

				$vendor->user_id = SessionManager::$user->id;
				$vendor->is_ignored = false;
				$vendor->is_unsubscribed = false;
				$vendor->is_invisible = true;

				$vendor->sort_index = -1;

				if (!isset($fromAddress->personal) || $fromAddress->personal == "")
					$vendor->name = $vendorScan->getCompanyName($fromAddress->host);
				else
					$vendor->name = $fromAddress->personal;

				$vendor->create();


				MessageBoxHandler::happy($vendor->name . " has been successfully added.");
				$isVendorFound = true;
				break;
			}
		}

		if (!$isVendorFound)
			MessageBoxHandler::error("Could not find email subscription ". $this->vendor_name ." in your email account.");

		if ($prev != null)
			$prev->close();
	}
}
