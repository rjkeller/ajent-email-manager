<?php
namespace Pixonite\CartBundle\Helper;

use Pixonite\CartBundle\Entity\CartPrep;
use Pixonite\CartBundle\Entity\CartEntry;

/**
 This represents a item that can be purchased through the cart system. For example,
 "domain registration", "domain renewal", "hosting purchase" are all ProductFactory
 items, because they're items that can be purchased in WordGrab.

 This is a bit different than actual products a customer is using, like a "domain",
 since a domain renewal purchase does not result in the creation of a brand new
 product, despite it having its own ProductFactory.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
abstract class ProductFactory
{
	/**
	 Prepare the item for purchase. Usually if you need to get data from a page
	 that will be used to initialize the product being purchased, it should
	 "flow" into the cart at this stage. Right now, the item is not yet in
	 the user's cart.
	
	 @param $cartPrep - A cart prep object, with various information about the product
	   that might be added to the user's cart (like price information, etc). See the
	   cart_prep table in MySQL for more info.
	 @param $productInfo - Various product information passed into the cart system.
	   This could be anything, and is product dependent.
	 @return An ID # that you want to associate with this order. Usually to store
	   the prep information collected on this page.
	*/
	public abstract function prepareForPurchase(CartPrep $cartPrep, $productInfo);

	/**
	 This event is ran when the product has been added to the user's cart.
	
	 @param Cart $cart - The cart object of the item just purchased.
	 @param $prepid - The ID # returned when the cart ran $this->prepareForPurchase().
	*/
	public abstract function addToCart(CartEntry $cart, $prepId);

	/**
	 Purchase the product. You are recommended to perform a couple of operations in
	 this method, like create an invoice, etc. See DomainFactory for a good example
	 of all the operations you should perform in this method.
	
	 @param CartData $cartdata - A set of various information about this cart, like
	   billing information, etc.
	*/
	public abstract function purchase(CartData $cartdata);

	/**
	 If the user has removed this item from their cart, this method is ran so you can
	 run some cleanup on removing the order from the system. Like removing any info
	 stored during $this->prepareForPurchase or $this->addToCart().
	
	 @param Cart $cart - The cart entry that is being removed by the user. Should
	   contain prepID if applicable.
	*/
	public abstract function removeFromCart(CartEntry $cart);

    public function getImage()
    {
        return "/resources/images/admin_images/domains_md.gif";
    }
}
