<?php
namespace AjentApps\AppStoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oranges\UserBundle\Helper\RequireLoginController;

use Pixonite\CartBundle\Helper\Cart;

use AjentApps\AppStoreBundle\Entity\PurchaseAppPrep;
use AjentApps\AppStoreBundle\Helper\PurchaseAppFactory;

class PurchaseAppController extends RequireLoginController
{
	/**
	 * @Route("/app-store/{id}/purchase", name="AppStoreBundlePurchaseApp")
	 */
	public function indexAction($id)
	{
		$prep = new PurchaseAppPrep();
		$prep->app_id = $id;

		$cart = $this->get("pixonite.cart");
		$cartPrep = $cart->prepCartItem(
			new PurchaseAppFactory(),
			$prep);

		$cart->addPrepToCart($cartPrep);
		$cart->redirToConfigure();

		throw new Exception("Error: Cart Redirect Failed");
	}
}
