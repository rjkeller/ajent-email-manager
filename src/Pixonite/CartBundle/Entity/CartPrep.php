<?php
namespace Pixonite\CartBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Doctrine\ORM\Mapping as ORM;

/**
 Represents a item that is being prepared to be added to a user's cart, but is not
 yet in their cart. Most purchase pages involve you going through a wizard before
 the item is added to your cart. So the Cart_Prep instance will store the information
 you need, and then when you're ready to add the item to your cart, you got all the
 information already loaded into the Cart_Prep instance.

 Now since the information needed to be stored in each wizard is different for each
 product, the Cart_Prep has a "prepid" that corresponds to another SQL table with
 wizard information for each specific product.

 NOTE:
 These instances are not garbage collected right now, but probably should be (since
 if the user cancels the wizard in the middle of the process, this object will float
 in space never to be seen again).

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="cart_prep")
*/
class CartPrep extends DatabaseModel
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
	
	/** @ORM\Column(type="integer") */
	protected $product_prep_id;
	
	/** @ORM\Column(type="string") */
	protected $redirect;
	
	/** @ORM\Column(type="datetime") */
	protected $timestamp;

	protected function getTable()
	{
		return "cart_prep";
	}
}
