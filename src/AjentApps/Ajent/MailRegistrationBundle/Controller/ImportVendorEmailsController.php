<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\forms\FormUtility;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Ajent\Mail\MailBundle\Entity\Vendor;
use Ajent\Vendor\VendorBundle\Entity\FakeVendor;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\ExternalMailBundle\Helper\ImportVendorsListener;
use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;

class ImportVendorEmailsController extends RequireLoginController
{
	public function indexAction()
	{
		$template_vars = array();
		$db = MongoDb::getDatabase();

		if (isset($_GET['loadEmails']) && $_GET['loadEmails'] == 1)
		{
			$db = MongoDb::getDatabase();

			//for the remaining vendors, we'll import the last 3 emails for
			//that vendor.
			$q = MongoDb::modelQuery($db->vendors->find(array(
					'user_id' => SessionManager::$user->id,
					'pendingAddToAjent' => true,
					'is_unsubscribed' => false,
					'is_invisible' => false
				)),
				"Ajent\Vendor\VendorBundle\Entity\Vendor")
				->getArray();

			$externalAccount = new ExternalAccount();
			$externalAccount->loadUser();

			VendorScan::importVendorStarterEmails($externalAccount, $q);
			VendorScan::cleanUpVendors(SessionManager::$user->id);

			return new Response("done!");
		}

		return $this->render(
			"MailRegistrationBundle:shadowbox:LoadingVendorEmails.twig.html",
			$template_vars);
	}

}
