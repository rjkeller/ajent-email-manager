<?php
namespace Pixonite\CartBundle\Helper;

use Pixonite\CartBundle\Entity\CartEntry;
use Pixonite\CartBundle\Entity\Product;
use Pixonite\CartBundle\Entity\CartPurchased;

/**
 Holds some misc data that the cart class wants to pass to the various ProductFactory's
 when a purchase request is made by the system.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class CartData
{
	public $cart;
	public $product;
	public $cart_purchased;
	public $billing_method;

	public function __construct(CartEntry &$c, Product &$p, CartPurchased &$cp, &$b)
	{
		$this->cart = &$c;
		$this->product = &$p;
		$this->cart_purchased = &$cp;
		$this->billing_method = &$b;
	}
}
