<?php
namespace Pixonite\BillingBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Pixonite\BillingBundle\Entity\UserFunds;
use Oranges\frontend\WgSmarty;

use Oranges\errorHandling\ForceError;
use Oranges\sql\Database;
use Oranges\sql\SqlModelIterator;
use Oranges\gui\MessageBoxHandler;
use Oranges\SearchBundle\Helper\SearchResults;
use Oranges\misc\WgTextTools;

use Pixonite\BillingBundle\Entity\BillingSettings;
use Pixonite\BillingBundle\Query\BillingSpec;
use Pixonite\BillingBundle\Helper\BillingSearchResultsSpec;

use Oranges\FormsBundle\Entity\FormCoder;

class indexController extends RequireLoginController
{
	public function indexAction()
	{
		//set active tab equal to billing
		$active = "billing";


		$user = SessionManager::$user->id;
		$userinfo = null;

		$contact = new Contact();
		$contact->load(SessionManager::$user->contact_id);

		$balance = UserFunds::getInstance();
/*
		if (FormCoder::checkCode("balance_notification"))
		{
			ForceError::$inst->checkDouble($_POST['amount']);
			$uid = $user;

			Database::query("UPDATE user_alerts SET balanceLowAlert = '$_POST[amount]' WHERE uid = '$uid' LIMIT 1");
			MessageBoxHandler::happy("Your balance alert has been set at $". number_format($_POST['amount'], 2, '.', ','), "Balance notification has been successfully set");
		}
*/
        $template_vars = array();

		$defaultbilling = BillingSettings::getDefaultBilling();
		if ($defaultbilling == null)
			$template_vars['billing'] = "No default billing specified";
		else
			$template_vars['billing'] = $defaultbilling->toString();

		$template_vars['balance'] = number_format($balance->getBalance(), 2, '.', ',');
		$template_vars['contact'] = $contact->toString();

		$spec = new BillingSpec();
		$billing = new SearchResults();
		$billing->init($spec);
		$q = $billing->getSqlQuery();

		$template_vars['searchResults'] = $billing;
		$template_vars['invoicelist'] = $q;

		return $this->render('BillingBundle:pages:index.twig.html',
			$template_vars);
	}
}
