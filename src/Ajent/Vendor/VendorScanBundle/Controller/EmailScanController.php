<?php
namespace Ajent\Vendor\VendorScanBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\forms\FormUtility;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\SearchBundle\Entity\SearchQuery;
use Oranges\SearchBundle\Helper\SearchEngine;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\RedisBundle\Helper\Redis;

use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\ExternalMailBundle\Helper\ImportVendorsListener;
use Ajent\Vendor\VendorScanBundle\Query\VendorSpec;
use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;
use AjentApps\Ajent\MailRegistrationBundle\Form\SwitchVendorForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class EmailScanController extends RequireLoginController
{
	/**
	 * @Route("/sign-up/import_mail", name="AjentBundleEmailScan")
	 */
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

	/**
	 * @Route("/sign-up/import_mail/progress", name="AjentBundleEmailScanProgressBar")
	 */
	public function progressBarAction()
	{
		$redis = Redis::getInstance();
		return new Response($redis->get(SessionManager::$user->id ."-email-scan"));
	}

	/**
	 * @Route("/sign-up/import_mail/view_vendors", name="AjentBundleViewVendors")
	 */
	public function viewVendorsAction()
	{
		session_write_close();
		$template_vars = array();
		$redis = Redis::getInstance();
		$db = MongoDb::getDatabase();

		if (isset($_GET['scanVendors']) && $_GET['scanVendors'] == 1)
		{
			$externalAccount = new ExternalAccount();
			$externalAccount->loadUser();

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
			{
//				$redis->set(SessionManager::$user->id ."-email-scan", 100);
    			return new Response("done!");
			}

			$redis->set(SessionManager::$user->id ."-email-scan", 5);
			$scanner = new VendorScan();
			$hasMore = $scanner->scanForVendors($externalAccount);

			$redis->set(SessionManager::$user->id ."-email-scan", 98);

			$i = $db->vendors->find(array(
				"user_id" => SessionManager::$user->id))
				->count();
			$i++;

			foreach ($scanner->vendors as $v)
			{
				$v->user_id = SessionManager::$user->id;
				$v->is_ignored = false;
				$v->is_unsubscribed = false;
				$v->is_invisible = true;
				$v->sort_index = $i++;
				$v->create();
			}

            if ($hasMore)
                $searchQuery->page_num++;
            else
                $searchQuery->load_more_results = false;
            $searchQuery->save();
			$redis->set(SessionManager::$user->id ."-email-scan", 100);

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
			$vendor->is_invisible = false;
			$vendor->external_account_id = $externalAccount->id;
			$vendor->is_active = true;
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

		$template_vars['categories'] = MongoDb::modelQuery(
			$db->email_categories->find(array(
				"user_id" => SessionManager::$user->id
			)),
			"Ajent\Mail\MailBundle\Entity\Category")
				->getArray();

		$spec = new VendorSpec();
		$engine = new SearchEngine();
		$engine->loadGetParameters();
		$engine->init($spec);

		$template_vars['searchResults'] = $engine;
		$template_vars['results'] = $engine->getSqlQuery();

		$template_vars['showSignupTab'] = true;

		return $this->render("MailRegistrationBundle:pages:EmailScan.twig.html",
			$template_vars);
	}
}
