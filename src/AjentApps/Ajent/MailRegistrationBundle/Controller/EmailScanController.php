<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\forms\FormUtility;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\SearchBundle\Entity\SearchQuery;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\ExternalMailBundle\Helper\ImportVendorsListener;
use AjentApps\Ajent\MailRegistrationBundle\Query\VendorSpec;
use AjentApps\Ajent\MailRegistrationBundle\Form\SwitchVendorForm;

class EmailScanController extends RequireLoginController
{
	public function indexAction()
	{
		$db = MongoDb::getDatabase();
		$num_vendors = $db->vendors->find(
				array("user_id" => SessionManager::$user->id)
				)->count();
		$lastPage = (int)($num_vendors / 10);

		return $this->render("MailRegistrationBundle:shadowbox:LoadingEmailScan.twig.html",
			array("lastPage" => $lastPage));
	}

	public function viewVendorsAction()
	{
		$template_vars = array();

		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser();

		if (isset($_GET['scanVendors']) && $_GET['scanVendors'] == 1)
		{
            $searchQuery = new SearchQuery();
            if (!$searchQuery->loadQuery("vendors"))
            {
                $searchQuery->user_id = SessionManager::$user->id;
                $searchQuery->query_name = "vendors";
                $searchQuery->page_num = 0;
                $searchQuery->load_more_results = true;
                $searchQuery->create();
            }

            if (!$searchQuery->load_more_results)
    			return new Response("done!");

			$vendors = new ImportVendorsListener();

			$hasMore = $externalAccount->scanForVendors(
				$vendors
			);

			foreach ($vendors->vendors as $v)
			{
				$v->user_id = SessionManager::$user->id;
				$v->is_ignored = false;
				$v->is_unsubscribed = false;
				$v->is_invisible = true;
				$v->create();
			}

            if ($hasMore)
                $searchQuery->page_num++;
            else
                $searchQuery->load_more_results = false;
            $searchQuery->save();

			die("done");
			return new Response("done!");
		}
/*
		if (isset($_POST['cid']) && $_POST['cid'] == "AddToAdjent")
		{
			$vendor = new Vendor();
			$vendor->load($_POST['vid']);
			$vendor->pendingAddToAjent = true;
			$vendor->save();
		}
*/
		if (isset($_POST['cid']) && $_POST['cid'] == "Unsubscribe")
		{
			$vendor = new Vendor();
			$vendor->load($_POST['vid']);
			$vendor->is_unsubscribed = true;
			$vendor->external_account_id = $externalAccount->id;
			$vendor->is_active = false;
			$vendor->save();
			\OrangesLogger("Vendor Unsubscribe: ". $vendor->name, "switch");
		}

		new SwitchVendorForm();

		if (isset($_POST['cid']) && $_POST['cid'] == "IgnoreVendor")
		{
			$vendor = new Vendor();
			$vendor->load($_POST['vid']);
			$vendor->is_ignored = true;
			$vendor->external_account_id = $externalAccount->id;
			$vendor->is_active = false;
			$vendor->save();
			\OrangesLogger("Vendor Ignore: ". $vendor->name, "switch");
		}

		$db = MongoDb::getDatabase();
		$template_vars['categories'] = MongoDb::modelQuery(
			$db->email_categories->find(array(
				"user_id" => SessionManager::$user->id
			)),
			"Ajent\Mail\MailBundle\Entity\Category")
				->getArray();

		$spec = new VendorSpec();
		$engine = new SearchEngine();
		$engine->init($spec);

		$template_vars['searchEngine'] = $engine;
		$template_vars['results'] = $engine->getSqlQuery();
		$template_vars['showSignupTab'] = true;
		print_r($template_vars);die();

		return $this->render("MailRegistrationBundle:pages:EmailScan.twig.html",
			$template_vars);
	}
}
