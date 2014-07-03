<?php
namespace Pixonite\CartBundle\Helper;

use Oranges\misc\WgTextTools;

use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\errorHandling\UnrecoverableSystemException;

use Oranges\sql\SqlModelIterator;

use Oranges\UserBundle\Helper\SessionManager;

use Pixonite\CartBundle\Entity\Reseller;
use Pixonite\CartBundle\Entity\CartPrep;
use Pixonite\CartBundle\Entity\Product;
use Pixonite\CartBundle\Entity\CartPurchased;
use Pixonite\CartBundle\Entity\CartEntry;

use Pixonite\BillingBundle\Helper\BillingUtility;
use Pixonite\BillingBundle\Helper\BillingMethod;

/**
 WORDGRAB CART SYSTEM

 This system manages and processes purchases made through the WordGrab system.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class Cart
{
	private $dbh;

    public function __construct($dbh)
    {
		$this->dbh = $dbh;
    }

	/**
	 Prep an item for adding to the cart, but we don't want to add the item to the
	 user's cart yet until we get more information. So this is temporary storage
	 of cart data until the user is done entering all requested information. Then
	 it is officially added to the user's cart.
	
	 @param Factory $factory - A ProductFactory class instance.
	 @param $productInfo - Any piece of information that needs to be passed to
	   the factory. This is factory-dependent, so see your specified factory
	   for more information.
	 @return CartPrep - The "CartPrep" object that was just created
	 */
	public function prepCartItem(ProductFactory $factory, $productInfo)
	{
		UserErrorHandler::$inst->assert(SessionManager::$logged_in,
			new ErrorMetaData("You must be logged in to purchase products."));

		$cart = new CartPrep();
		$cart->factory = get_class($factory);
		$cart->product_prep_id = $factory->prepareForPurchase($cart, $productInfo);
		$cart->user_id = SessionManager::$user->id;

		if (UserErrorHandler::$inst->hasErrors)
			return null;

		$cart->create();
		return $cart;
	}

	public function removeFromCart($cartid)
	{
		$cart = new CartEntry();
		$cart->load($cartid);

		$str = $cart->factory;
		$factory = new $str();
		$factory->removeFromCart($cart);

		$cart->delete();
	}

	/**
	 Remove all items stored in this user's cart.
	*/
	public function emptyCart()
	{
		$q = new SqlModelIterator($this->dbh->fetchAll("
			SELECT
				*
			FROM
				cart
			WHERE
				user_id = '". SessionManager::$user->id ."'

		"), "Pixonite\CartBundle\Entity\CartEntry");
		foreach ($q as $cart)
		{
			$str = $cart->factory;
			$factory = new $str();

			if (!empty($cart->factory))
				$factory->removeFromCart($cart);
			$cart->delete();
		}
	}

	/**
	 Prepares a bulk purchase of a specific type of product.

	 @param $factory - A ProductFactory instance.
	 @param $arrayOfProductInfos - An array of product info. See prepCartItem
	   for more info.
	 @see prepCartItem
	 @return The most recent cart entry created. NOTE: Not all of the cart
	   prep entries created will be returned!
	*/
	public function prepMultipleItems(ProductFactory $factory, $arrayOfProductInfos)
	{
		UserErrorHandler::$inst->assert(!empty($arrayOfProductInfos),
			new ErrorMetaData("No products have been selected to purchase.
				Please select some products to purchase."));
		if (UserErrorHandler::$inst->hasErrors)
			return null;

		$cart = new CartPrep();
		$cart->factory = get_class($factory);
		$cart->userid = SessionManager::$user->id;
		$cart->resellerid = Reseller::getInstance()->id;

		foreach ($arrayOfProductInfos as $productInfo)
		{
			if (empty($productInfo))
				continue;
			$cart->product_prep_id .= $factory->prepareForPurchase($cart, $productInfo);
			if (UserErrorHandler::$inst->hasErrors)
				return null;
			$cart->create();
		}

		if (UserErrorHandler::$inst->hasErrors)
			return null;

		return $cart;
	}

	/**
	 This method will do one of 2 things:
	
	 1) If a product needs extra configuration by the user (for domain
		 registration, this would be entering domain contact details), then
		 this will redirect to the appropriate page to enter this information.
	 2) If all products in the cart are already configured (or no items are in
		 the cart), then this will redirect to the cart page.
	
	 After running this method, program execution will end. So any calls after
	 this method will never be hit.
	*/
	public function redirToConfigure()
	{
		$conf = new SqlModelIterator($this->dbh->fetchAll("
			SELECT
				*
			FROM
				cart_prep
			WHERE
				user_id = '". SessionManager::$user->id ."'
		"), "Pixonite\CartBundle\Entity\CartPrep");
		foreach ($conf as $prep)
		{
			//if no more configuration is needed for this prepared item, then
			//add it to the cart (since it's now fully configured), and move on
			if ($prep->redirect == "")
			{
				Cart::addPrepToCart($prep);
				continue;
			}
			header("Location: ". $prep->redirect ."?pid=". $prep->id);
			die();
		}

		header("Location: /purchase/cart");
		die();
	}

	/**
	 Takes in previous cart preparation information and adds that entry to
	 the current user's cart.
	
	 @param CartPrep $prepinfo - The CartPrep entry that you want to add to
	   the cart.
	 @return The MySQL entry just inserted in the "cart" table.
	 */
	public function addPrepToCart(CartPrep $prepinfo)
	{
		$factory = $prepinfo->factory;

		//create a factory object
		$factory = new $factory();

		$cart = new CartEntry();
		$cart->user_id = SessionManager::$user->id;
		$cart->factory = $prepinfo->factory;
		$cart->product_prep_id = $prepinfo->product_prep_id;

		$factory->addToCart($cart, $prepinfo->product_prep_id);

        if ($cart->name == "")
            throw new UnrecoverableSystemException(
                "", "", "Cart entry not properly initialized. There is no name for this item being purchased!");

		if (UserErrorHandler::$inst->hasErrors)
			return false;

		$cart->create();

		$prepinfo->delete();

		return $cart;
	}

	/**
	  Purchases all items in the user's cart.

	  @param $billingType The type of billing the user will use.
	  @throws UserErrorHandler, ForceUserError
	 */
	public function purchaseAll(BillingMethod $billing)
	{
		$q = new SqlModelIterator($this->dbh->fetchAll("
			SELECT
				*
			FROM
				cart
			WHERE
				user_id = '". SessionManager::$user->id ."'
		"),
		"Pixonite\CartBundle\Entity\CartEntry");
		$total = 0;
		$cartEntries = array();
		foreach ($q as $obj)
		{
		    $cartEntries[] = $obj;
			$total += $obj->price;
		}

	    $billing->deductFunds($total);
		if (UserErrorHandler::$inst->hasErrors)
			return false;

		//go back to the start of the query
		foreach ($cartEntries as $cart_entry)
		{
			$this->process_purchase($billing, $cart_entry);

			if (UserErrorHandler::$inst->hasErrors)
				break;
		}
	}

	/**
	  Purchases a specific item in the user's cart.
	
	  @param $billingType The type of billing the user will use.
	  @param $cart - The CartEntry of the item you would like to purchase.
	  @throws UserErrorHandler
	 */
	public function purchase(BillingMethod $billingtype, CartEntry $cart)
	{
	    $billing->deductFunds($cart->price);

		if (UserErrorHandler::$inst->hasErrors)
			return false;

		$this->process_purchase($billingtype, $cart);
	}

//------------------------ PRIVATE FUNCTIONS ------------------------//

	/**
	 Completes the purchase of a product, given the billing and cart entry.

	 @param Object $billing - A object of the "billing" table, with the
	   billing info you want to use for this purchase.
	 @param Object $cart_entry - A object of the "cart" table. This is the
	   cart entry you want to process the purchase for.
	*/
	private function process_purchase(BillingMethod $billing, CartEntry $cart_entry)
	{
		$product = new Product();

		//initialized a cart_purchased instance. This is mostly for auditing
		//purposes.
		$cart_purchased = new CartPurchased();
		$cart_purchased->user_id = SessionManager::$user->id;
		$cart_purchased->billing_id = $billing->id;
		$cart_purchased->factory = $cart_entry->factory;

		if (UserErrorHandler::$inst->hasErrors)
			return false;

		$str = $cart_entry->factory;
		$factory = new $str();

		//certain types of products create a 'product" object for the purchase
		//request, but others don't. If a product instance already exists for
		//this object, then we'll just update the product with info from the
		//cart and save it. If no product instance exists, then we'll create
		//one.
		$isLoaded = false;
		if (!empty($cart_entry->productid))
		{
			$isLoaded = true;
			$product->load($cart_entry->productid);
		}
		else
		{
			$product->id = WgTextTools::uniqueid();
			$product->quantity = 1;
			$product->user_id = SessionManager::$user->id;
			$product->status = "active";
			$product->product_name = get_class($factory);
		}


		// Run the purchase command for this specific object.
		$factory->purchase(
			new CartData($cart_entry,
				$product, $cart_purchased, $billing));

		if (UserErrorHandler::$inst->hasErrors)
			return false;

		if ($isLoaded)
			$product->save();
		else
			$product->create();
		$cart_purchased->product_id = $product->id;
		$cart_purchased->create();

		//remove the item from the cart.
		$this->dbh->executeUpdate("
		    DELETE FROM
		        cart
		    WHERE
		        id = '". $cart_entry->id ."'
		");
	}
}
