<?php
namespace AjentApps\Webmail\MailLandingBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\sql\Database;

use Ajent\Mail\MailBundle\Form\AddCategoryForm;
use Ajent\Mail\MailBundle\Form\EditCategoryForm;
use Ajent\Mail\MailBundle\Form\DeleteCategoryForm;

use Ajent\AddonBundle\Controller\AddonController;

use Ajent\Mail\MailBundle\Entity\Category;

use Oranges\sql\SqlProfiler;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class LandingController extends RequireLoginController
{
	/**
	 * @Route("/mail", name="SocialMailBundleLanding")
	 */
	public function indexAction()
	{
		$template_vars = array();

		$db = MongoDb::getDatabase();

		$query = $db->email_categories->find(array(
				"user_id" => SessionManager::$user->id));
		if (isset($_POST['sort']) && $_POST['sort'] == "newest_message")
		{
			$query = $query->sort(array("newest_message" => -1));
			$template_vars['sort_by'] = "newest_message";
		}
		else if (isset($_POST['sort']) && $_POST['sort'] == "num_messages")
		{
			$query = $query->sort(array("num_messages" => -1));
			$template_vars['sort_by'] = "num_messages";
		}
		else
		{
			$query = $query->sort(array(
				"num_new_messages" => -1,
				"num_messages" => -1,
				"name" => 1
				));
			$template_vars['sort_by'] = "";
		}

		$categories = MongoDb::modelQuery(
			$query,
			"Ajent\Mail\MailBundle\Entity\Category");

		$template_vars['categories'] = $categories;


		$addon_manager = new AddonController();
		$addon_manager->construct("ViewVendorsPage");
		$template_vars['addon_manager'] = $addon_manager;
		$template_vars['showWelcomeMessage'] = isset($_GET['welcome']) && !isset($_COOKIE['show_welcome']);

		$template_vars['messages'] = MongoDb::modelQuery(
			$db->email_messages->find(array(
				"is_read" => false,
				"recipient_user_id" => SessionManager::$user->id))
				->sort(array(
					'date' => -1
				))
				->limit(5),
			"Ajent\Mail\MailBundle\Entity\EmailMessage");

		//make sure this welcome message doesn't pop up again for any form
		//submits or anything like that.
		if ($template_vars['showWelcomeMessage'])
			setcookie("show_welcome", 1);

		$out = $this->render("SocialMailBundle:pages:Landing.twig.html",
			$template_vars);

		$addon_manager->destruct("ViewVendorsPage");

		return $out;
	}

	public function printCategoryAction(Category $category, AddonController $addon_manager, $isFirst, $i)
	{
		$template_vars = array();
		$db = MongoDb::getDatabase();

		//compile the list of results.
		$results = array();

		//add normal vendors
		$q = MongoDb::modelQuery(
			$db->vendor_categories->find(array(
				"is_invisible" => false,
				"category_id" => $category->id))
				->sort(array(
					'num_new_messages' => -1,
					'num_messages' => -1
				)),
			"Ajent\Vendor\VendorBundle\Entity\VendorCategory");

		foreach ($q as $v)
		{
			$vendor = $v->getVendor();
			$vendor->num_new_messages = $v->num_new_messages;
			$results[] = $vendor;
		}

		$template_vars['vendors'] = $results;

		$template_vars['category'] = $category;
		$template_vars['addon_manager'] = $addon_manager;
		$template_vars['isFirst'] = $isFirst;
		$template_vars['row'] = (int)($i / 3);
		$template_vars['col'] = $i % 3;
		$template_vars['zindex'] = 10000 - $i;

		return $this->render("SocialMailBundle:pages:LandingCategory.twig.html",
			$template_vars);

	}
}
