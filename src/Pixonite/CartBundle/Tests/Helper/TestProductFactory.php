<?php
namespace Pixonite\CartBundle\Tests\Helper;

use Pixonite\CartBundle\Entity\CartPrep;
use Pixonite\CartBundle\Entity\CartEntry;

use Pixonite\CartBundle\Helper\ProductFactory;
use Pixonite\CartBundle\Helper\CartData;

class TestProductFactory extends ProductFactory
{
	public function prepareForPurchase(CartPrep $cartPrep, $productInfo)
	{
	    
	}


	public function addToCart(CartEntry $cart, $prepId)
	{
	    
	}

	public function purchase(CartData $cartdata)
	{
	    
	}

	public function removeFromCart(CartEntry $cart)
	{
	    
	}
}
