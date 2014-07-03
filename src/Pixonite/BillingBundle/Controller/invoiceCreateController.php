<?php
namespace Pixonite\BillingBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;

class invoiceCreateController extends RequireLoginController
{
	public function runAction(PageRequest $request)
	{
		ForceError::$inst->checkAccess(User::$permissions->reseller_options);
		UserExtras::checkPermissions($_GET['userid']);

		if (FormCoder::checkCode("makeinvoice"))
		{
			$curuserid = User::$username;

			$user = SqlUtility::getObject("user", $_GET['userid']);
			User::impersonate($user->username);
			$prep = Cart::prepCartItem(Cart::$factories['generic'], null);
			Cart::addPrepToCart($prep->id);
			Cart::purchaseAll(null);
			User::impersonate($curuserid);

			MessageBoxHandler::happy("You have successfully added this product to this user for purchase.");
		}

		$user = SqlUtility::getObject("user", $_GET['userid']);
		$cid = new Contact($user->contactid);

		$smarty = WgSmarty::getInstance();
		$smarty->assign("contact", $cid->toString);
		$smarty->assign('today', date("Y-m-d H:i:s"));
		$smarty->display("billing/invoice_create.smarty");
	}
}