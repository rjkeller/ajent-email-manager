<?php
namespace Ajent\Vendor\VendorBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\FormsBundle\Helper\CidManager;

use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Vendor\VendorBundle\Form\DeleteVendorForm;
use Ajent\Vendor\VendorBundle\Form\EditVendorCategoryForm;
use Ajent\Mail\MailBundle\Helper\BundleManager;

use Ajent\AddonBundle\Controller\AddonController;

class EditVendorsController extends RequireLoginController
{
	public function indexAction()
	{
		if (CidManager::isCidValid("ChangeTerm"))
		{
			$vendor = new Vendor();
			$vendor->load($_POST['vid']);
			$vendor->term = $_POST['term'];
			$vendor->save();

			BundleManager::removeBundles($vendor->id);
			if ($_POST['term'] != '')
				BundleManager::createBundles($vendor->id, $_POST['term']);
		}

		if (CidManager::isCidValid("ChangeSubscription"))
		{
			$vendor = new Vendor();
			$vendor->load($_POST['vid']);
			$vendor->is_unsubscribed = !$vendor->is_unsubscribed;
			$vendor->save();
		}

        new DeleteVendorForm();
		new EditVendorCategoryForm();

		$db = MongoDb::getDatabase();
		$vendors = MongoDb::modelQuery($db->vendors->find(array(
				"is_invisible" => false,
				"is_rejected" => false,
				"user_id" => SessionManager::$user->id)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor");
		$template_vars['vendors'] = $vendors;
		$template_vars['leftNavTab'] = "Vendor";

		$addon_manager = new AddonController();
		$addon_manager->construct("EditVendors");
		$template_vars['addon_manager'] = $addon_manager;


		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$template_vars['name'] = $contact->first_name;
		$template_vars['tabs'] = "Settings";
		$template_vars['email_address'] = SessionManager::$user->username . "@". BuildOptions::$get['MailBundle']['DefaultEmailDomain'];

		return $this->render('VendorBundle:pages:EditVendors.twig.html',
			$template_vars);
	}
}
