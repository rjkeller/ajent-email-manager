<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Doctrine\ORM\Mapping as ORM;

/**
 This is mostly a log table that corresponds a purchased product to an invoice,
 product_type, factory, and other info. These are generated once a user has
 bought a product.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="cart_purchased")
*/
class CartPurchased extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;
	
	/** @ORM\Column(type="integer") */
	protected $product_id;
	
	/** @ORM\Column(type="integer") */
	protected $user_id;
	
	/** @ORM\Column(type="integer") */
	protected $invoice_id;
	
	/** @ORM\Column(type="integer") */
	protected $billing_id;
	
	/** @ORM\Column(type="integer") */
	protected $product_type_id;
	
	/** @ORM\Column(type="string") */
	protected $factory;

	protected function getTable()
	{
		return "cart_purchased";
	}
}
