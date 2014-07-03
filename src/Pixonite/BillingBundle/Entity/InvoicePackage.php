<?php
namespace Pixonite\BillingBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;

use Doctrine\ORM\Mapping as ORM;

/**
 Represents a product that was purchased that is attached to an invoice. So for
 example, "1 year domain registration" would be a product attached to an invoice.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="invoice_package")
*/
class InvoicePackage extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="string", length="8") */
	protected $invoice_id;

	/** @ORM\Column(type="integer") */
	protected $product_id;

	/** @ORM\Column(type="string") */
	protected $description;

	/** @ORM\Column(type="integer") */
	protected $quantity;

	/** @ORM\Column(type="decimal") */
	protected $price;

	protected function getTable()
	{
		return "invoice_package";
	}
}
