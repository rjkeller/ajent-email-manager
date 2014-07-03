<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Doctrine\ORM\Mapping as ORM;
use Pixonite\CartBundle\Helper\Cart;

/**
 This class stores meta data about each individual product in the WordGrab
 system. For example, pricing information, type of product, and whether it
 is still actively stored are contained in this class.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="product_type")
*/
class ProductType extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $reseller_id;
	
	/** @ORM\Column(type="string") */
	protected $name;
	
	/** @ORM\Column(type="decimal") */
	protected $price;
	
	/** @ORM\Column(type="string") */
	protected $type;
	
	/** @ORM\Column(type="string") */
	protected $status;
	
	/** @ORM\Column(type="boolean") */
	protected $is_displayed;

	protected function getTable()
	{
		return "product_type";
	}

	/**
	 loads the ProductType, given the name and type of object you requested.
	 ex:
		$name = "domain"
		$type = "net"

	 See the "product_type" table in MySQL for a list of available product
	 types.

	 @param string $name - The name of the product you requested.
	 @param string $type - The type of product you requested.
	*/
	public function loadType($name, $type)
	{
	    $rid = Reseller::getInstance()->id;
		return $this->loadQuery("reseller_id = '". $rid ."' AND
			type = '". $type ."' AND
			name = '". $name ."'");
	}

	public function updateCartEntry(Cart $cart)
	{
		$cart->price = $this->price;
		$cart->term = $this->term;
	}
}
