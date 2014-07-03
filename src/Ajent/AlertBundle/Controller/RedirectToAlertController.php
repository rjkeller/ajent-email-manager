<?php
namespace Ajent\AlertBundle\Controller;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\MailBundle\Entity\Category;

class RedirectToAlertController extends RequireLoginController
{
	public function indexAction($vendor_id)
	{
		$db = MongoDb::getDatabase();
		//figure out the alert we want to redirect to.
		$alerts = MongoDb::modelQuery($db->email_alerts->find(array(
				"expiration_date" => array('$gt' => time()),
				"is_invisible" => false,
				"vendor_id" => $vendor_id,
				"user_id" => SessionManager::$user->id))
				->sort(array("expiration_date" => -1))
				->limit(1),
			"Ajent\AlertBundle\Entity\Alert");

		$alert = null;
		foreach ($alerts as $a)
			$alert = $a;

		$router = $this->get("router");
		$url = $router->generate(
			'VendorBundleViewVendor',
			array('vendor_id' => $vendor_id ));
		$url .= "?message_id=". $alert->message_id;

		header("Location: ". $url);
		die();
	}
}
