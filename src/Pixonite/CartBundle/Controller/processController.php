<?php
namespace Pixonite\CartBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\frontend\WgSmarty;

use Pixonite\BillingBundle\Helper\BillingUtility;
use Pixonite\CartBundle\Helper\Cart;

use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\ForceUserError;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\framework\BuildOptions;

class processController extends RequireLoginController
{
	public function indexAction()
	{
		$billingOptions = array();
        foreach (BuildOptions::$get['billingMethods'] as $option)
        {
            $billingOptions[] = new $option();
        }

        $billing = $billingOptions[$_POST['billingMethod']];
        $redir = $billing->configureBillingUrl();

        //if the user needs to enter additional information for billing, then
        //redirect to the appropriate billing page. If no additional
        //information needs to be entered, then continue on.
        if ($redir != null)
        {
            header("Location: ". $redir);
            die();
        }

		$cart = $this->get("pixonite.cart");
		$cart->purchaseAll($billing);

		return $this->render('CartBundle:pages:process.twig.html',
			array());
	}
}
