<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Doctrine\ORM\Mapping as ORM;

/**
 Represents a product that has been purchased in the WordGrab system. Tells you how
 much the product cost, how many were purchased, whether or not auto renew is turned
 on, the product ID (ID of the item in its corresponding product table [like domain
 table]).

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="product")
*/
class Product extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="string") */
	protected $product_name;
	
	/** @ORM\Column(type="integer") */
	protected $reseller_id;
	
	/** @ORM\Column(type="decimal") */
	protected $price;
	
	/** @ORM\Column(type="integer") */
	protected $quantity;
	
	/** @ORM\Column(type="boolean") */
	protected $enable_auto_renew;
	
	/** @ORM\Column(type="integer") */
	protected $product_id;
	
	/** @ORM\Column(type="integer") */
	protected $user_id;
	
	/** @ORM\Column(type="string") */
	protected $status;
	
	/** @ORM\Column(type="datetime") */
	protected $expires;

	protected function getTable()
	{
		return "product";
	}
}
