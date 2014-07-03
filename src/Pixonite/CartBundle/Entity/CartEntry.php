<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 An item in the user's cart.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="cart")
*/
class CartEntry extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;
	
	/** @ORM\Column(type="integer") */
	protected $user_id;
	
	/** @ORM\Column(type="string") */
	protected $factory;

	/** @ORM\Column(type="string") */
	protected $name;

	/** @ORM\Column(type="integer") */
	protected $product_prep_id;

	/** @ORM\Column(type="decimal") */
	protected $price;

    /** @ORM\Column(type="string") */
	protected $term;

	/** @ORM\Column(type="integer") */
	protected $product_type_id;

	/** @ORM\Column(type="integer") */
	protected $product_id;
	
	/** @ORM\Column(type="datetime") */
	protected $timestamp;

	protected function getTable()
	{
		return "cart";
	}

	public function getShadowboxName()
	{
		return "rmv". $this->id;
	}

	public function getShadowboxCommand()
	{
		return "ajaxpage('/purchase/cart/ajax/remove/". $this->id ."', 'cart')";
	}

	public function getImage()
	{
		$factory = new $this->factory();
		return $factory->getImage();
	}

	public function getPrice()
	{
		return number_format($this->price, 2, '.', ',');
	}

	public function getTerm()
	{
		if ($this->term == "yearly")
			return "/yr";
	    else if ($this->term == "monthly")
			return "/mo";
	    else
			return "";
	}
}
