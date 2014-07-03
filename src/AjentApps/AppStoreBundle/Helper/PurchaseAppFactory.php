<?php
namespace AjentApps\AppStoreBundle\Helper;

use Pixonite\VoipBundle\Entity\VoipPrep;
use Pixonite\VoipBundle\Entity\PhoneNumber;

use Pixonite\CartBundle\Entity\CartPrep;
use Pixonite\CartBundle\Entity\CartEntry;
use Pixonite\CartBundle\Entity\ProductType;

use Pixonite\CartBundle\Helper\ProductFactory;
use Pixonite\CartBundle\Helper\CartData;

use Pixonite\BillingBundle\Entity\Invoice;

use Oranges\UserBundle\Helper\SessionManager;

use AjentApps\AppStoreBundle\Entity\PurchaseAppPrep;
use AjentApps\AppStoreBundle\Entity\UserApp;
use AjentApps\AppStoreBundle\Entity\App;

/**
 Factory that lets you buy app store apps using the WordGrab Cart system.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class PurchaseAppFactory extends ProductFactory
{
	public function prepareForPurchase(CartPrep $cartPrep, $productInfo)
	{
		$productInfo->create();
		return $productInfo->id;
	}

	public function addToCart(CartEntry $cart, $prepId)
	{
		$prep = new PurchaseAppPrep();
		$prep->load($prepId);

		$product_type = new ProductType();
		$product_type->loadType($prep->app_id, "app");
		
		$app = new App();
		$app->load($prep->app_id);

		$cart->price = $product_type->price;
		$cart->term = "flat";
		$cart->name = $app->name;
		$cart->product_type_id = $product_type->id;
	}

	public function purchase(CartData $cartdata)
	{
		$prep = new PurchaseAppPrep();
		$prep->load($cartdata->cart->product_prep_id);

		$app = new App();
		$app->load($prep->app_id);

		$invoice = new Invoice();
		$invoice->loadCartInformation(
			$cartdata->cart,
			$cartdata->product,
			$cartdata->billing_method,
			$app->name);
		$invoice->create();

		$userApp = new UserApp();
		$userApp->app_id = $app->id;
		$userApp->user_id = SessionManager::$user->id;
		$userApp->create();
	}

	/**
	 If the user has removed this item from their cart, this method is ran so you can
	 run some cleanup on removing the order from the system. Like removing any info
	 stored during $this->prepareForPurchase or $this->addToCart().
	
	 @param Cart $cart - The cart entry that is being removed by the user. Should
	   contain prepID if applicable.
	*/
	public function removeFromCart(CartEntry $cart)
	{
		$prep = new PurchaseAppPrep();
		$prep->load($cart->product_prep_id);
		$prep->delete();
	}

    public function getImage()
    {
		return "/bundles/passwordmanager/images/logout_32.png";
    }
}
