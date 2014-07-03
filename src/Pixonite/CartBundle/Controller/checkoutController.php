<?php
namespace Pixonite\CartBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;

use Oranges\gui\MessageBoxHandler;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\framework\BuildOptions;

use Pixonite\BillingBundle\Entity\UserFunds;
use Pixonite\BillingBundle\Helper\Billing;
use Pixonite\BillingBundle\Helper\BillingUtility;

class checkoutController extends RequireLoginController
{
	public function indexAction()
	{
		//load up the cart entries into this class, so they can be shown on
		//the checkout page.
		$cartController = new cartController();
		$template_vars = $cartController->getTemplateVars();

		//if this user's cart is empty, the redirect them to their cart, and off of the
		//checkout page.
		$dbh = $this->get("database_connection");
		$q = $dbh->fetchAll("
			SELECT
				price
			FROM
				cart
			WHERE
				user_id = '". SessionManager::$user->id ."'
		");
		if (sizeof($q) <= 0)
		{
			header("Location: /purchase/cart");
			return;
		}

		//calculate the order total
		$total = 0.0;
		foreach ($q as $o)
		{
		    $total += $o['price'];
		}

		$template_vars["currentOrder"] = number_format($total, 2, '.', ',');
		$template_vars["hasOrder"] = !empty($q);


		if (isset($_GET['e']) && $_GET['e'] == "c")
			MessageBoxHandler::error("Please select a billing option to continue.");

		$contact = new Contact();
		$contact->load(SessionManager::$user->contact_id);

		$template_vars["contactInfo"] = $contact->toString();
		
		$userFunds = UserFunds::getInstance();
		$template_vars["currentBalance"] = number_format($userFunds->getBalance() , 2, '.', ',');

		$billingOptions = array();
        foreach (BuildOptions::$get['billingMethods'] as $option)
        {
            $billingOptions[] = new $option();
        }
        $template_vars["billingOptions"] = $billingOptions;

		$template_vars["creditCardString"] = "No card is on file";
		$template_vars["funcs"] = $this;

		return $this->render('CartBundle:pages:checkout.twig.html',
			$template_vars);
	}
}
