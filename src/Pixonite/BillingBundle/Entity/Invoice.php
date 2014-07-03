<?php
namespace Pixonite\BillingBundle\Entity;

use Oranges\UserBundle\Entity\Contact;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\misc\KDate;
use Oranges\UserBundle\Helper\User;
use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;

use WordGrab\messages\Model\Message;

use Pixonite\CartBundle\Entity\CartEntry;
use Pixonite\CartBundle\Entity\Product;

use Doctrine\ORM\Mapping as ORM;

/**
 Represents an invoice that is issued to a user.

 When creating a new invoice, you must associate an InvoicePackage instance before
 saving the invoice.

 Note that any save or insert operations will also be applied to the invoice packages
 associated with this class.

 @see InvoicePackage
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="invoice")
*/
class Invoice extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="string", length="8")
	*/
	protected $id;
	
	/** @ORM\Column(type="integer") */
	protected $buyer_user_id;
	
	/** @ORM\Column(type="integer") */
	protected $buyer_contact_id;
	
	/** @ORM\Column(type="integer") */
	protected $seller_reseller_id;
	
	/** @ORM\Column(type="integer") */
	protected $billing_id;

	/** @ORM\Column(type="datetime") */
	protected $date_issued;
	
	/** @ORM\Column(type="datetime") */
	protected $date_due;
	
	/** @ORM\Column(type="decimal") */
	protected $amount;
	
	/** @ORM\Column(type="decimal") */
	protected $total_due;
	
	/** @ORM\Column(type="string") */
	protected $transaction_id;
	
	/** @ORM\Column(type="string") */
	protected $status;
	
	/** @ORM\Column(type="string") */
	protected $cycle;
	
	/** @ORM\Column(type="datetime") */
	protected $next_renewal;
	
	/** @ORM\Column(type="decimal") */
	protected $subtotal;
	
	/** @ORM\Column(type="decimal") */
	protected $credits;
	
	/** @ORM\Column(type="decimal") */
	protected $tax;
	
	/** @ORM\Column(type="decimal") */
	protected $total;

	/** What type of billing image should be associated with this invoice.
	  @ORM\Column(type="string") */
	public $type;

	/** @ORM\Column(type="string") */
	public $short_description;

	protected function getTable()
	{
		return "invoice";
	}

	//packages cache
	private $packages = null;

	//product cache, used in getProduct()
	private $product = null;

    /**
     initializes all fields in an invoice except for type
     */
	public function loadCartInformation(CartEntry $cartEntry,
	    Product $product,
	    $billing_method,
	    $desc)
	{
		$price = $cartEntry->price;

		$contact = new Contact();
		$contact->load(SessionManager::$user->contact_id);

        //create a clone of this contact, so that if the user changes their
        //profile, then it won't change the invoice.
        $contact->id = "";
		$contact->create();

		$this->buyer_user_id = SessionManager::$user->id;
		$this->buyer_contact_id = $contact->id;
		$this->billing_id = $billing_method->id;
		$this->date_issued = date("Y-m-d H:i:s");
		$this->date_due = date("Y-m-d H:i:s");
		$this->amount = $price;
		$this->total_due = 0;
	    $this->transaction_id = $billing_method->getTransactionId();
		$this->status = "paid";
		$this->cycle = $cartEntry->term;
		$this->next_renewal = $product->expires;
		$this->subtotal = $price;
		$this->credits = 0;
		$this->tax = 0;
		$this->total = $this->subtotal + $this->tax - $this->credits;

		$this->short_description = $desc;

		$pkg = new InvoicePackage();
		$pkg->invoice_id = $this->id;
		$pkg->product_id = $product->id;
		$pkg->description = $desc;
		$pkg->quantity = 1;
		$pkg->price = $price;

		$this->addPackage($pkg);
	}


	public function load($id)
	{
		ForceError::$inst->checkId($id);

		$out = $this->loadQuery(
		    "id = '". $id ."' AND
		    buyer_user_id ". SessionManager::$permissions->getUserId(),
		    true);

		if (!$out)
			throw new UnrecoverableSystemException("Access Denied", "The invoice you requested either does not exist or you do not have access to it. Please select a different invoice.", "Loading with SQL query info failed: id = '". $id ."' AND
		    buyer_user_id ". SessionManager::$permissions->getUserId());
	}

	/**
	 Returns a Product object related to the product purchased in this invoice.
	*/
	public function getProduct()
	{
		if ($this->product != null)
			return $this->product;

		$q = "
			SELECT
				COUNT(*)
			FROM
				product
			WHERE
				id = '". $this->product_id ."'
			LIMIT
				1
		";
		if (Database::scalarQuery($q) > 0)
		{
		    $product = new Product();
		    $product->load($this->product_id);

		    $this->product = $product;
		}

		return $this->product;
	}

	/**
	 Returns whether or not this invoice supports having the "autorenew" widget at the bottom
	 of the invoice.
	*/
	public function doesInvoiceSupportAutorenew()
	{
	    //will re-implement this later.
	    return false;
	}

	/**
	 Returns true if the product referenced in this invoice supports autorenew.
	*/
	public function isAutorenewEnabled()
	{
		return $this->getProduct()->enableAutorenew;
	}

	/**
	 Adds an invoice package product to this invoice. Note that amount due and other values
	 in this invoice will not be updated until a save operation is made.
	*/
	public function addPackage(InvoicePackage $package)
	{
		$this->packages[] = $package;
	}

	/**
	 Returns an array of packages attached to this invoice.
	*/
	public function getPackages()
	{
		if ($this->packages == null)
		{
			$this->packages = Database::modelQuery("
				SELECT
					*
				FROM
					invoice_package
				WHERE
					invoice_id = '". $this->id ."'
			",
			"Pixonite\BillingBundle\Entity\InvoicePackage");
		}
		return $this->packages;
	}

	public function create()
	{
	    $this->id = WgTextTools::uniqueid(8);
	    if (empty($this->packages))
	        throw new \Exception("Cannot create an invoice with no packages attached.");

	    $cache = new InvoiceCache();
		$cache->date = $this->date_issued;
		$cache->user_id = SessionManager::$user->id;
		$cache->type = $this->type;
		$cache->invoice_id = $this->id;
		$cache->description = $this->packages[0]->description;
		$cache->amount = $this->amount;
		$funds = UserFunds::getInstance();
		$cache->balance = $funds->getBalance();
		$cache->create();

		foreach ($this->packages as $pkg)
		{
			$pkg->invoice_id = $this->id;
			$pkg->create();
		}

		parent::create();
	}

	public function save()
	{
		foreach ($this->packages as $pkg)
		{
			$pkg->save();
		}
		parent::save();
	}

	/**
	 Returns the status of this invoice as a user-friendly string.
	 ex. Due Now, Issued, Refunded, etc.
	*/
	public function getStatus()
	{
		if ($this->status == "due")
			return "Due Now";
		else if ($this->status == "issued")
			return "Issued";
		else if ($this->status == "refunded")
			return "Refunded";
		
	}

	public function getAmount()
	{
		return number_format($this->amount, 2, '.', ',');
	}

	public function getTotalDue()
	{
		return number_format($this->total_due, 2, '.', ',');
	}

	public function getDateIssued()
	{
		$d = new KDate($this->date_issued);
		return $d->__toString();
	}

	public function getDateDue()
	{
		$d = new KDate($this->date_due);
		return $d->__toString();
	}

	public function getBillingContact()
	{
		$cid = new Contact();
		//legacy code didn't set the buyer_contact_id, so for those instances
		//just show the user contact. It's not optimal, but at least it works.
		if (empty($this->buyer_contact_id))
			$cid->load(SessionManager::$user->contact_id);
		else
			$cid->load($this->buyer_contact_id);
		return $cid->toString();
	}

	public function getBillingCycle()
	{
		switch ($this->cycle)
		{
		case "monthly":
			return "Monthly";

		case "yearly":
			return "Yearly";
		
		default:
			return "n/a";
		}
	}

	/**
	 Returns the billing cycle in a shorter format (e.g., /mo, /yr).
	*/
	public function getShortBillingCycle()
	{
		switch ($this->cycle)
		{
		case "none": return "";
		case "monthly": return "/mo.";
		case "yearly": return "/yr.";
		}
	}

	public function getNextRenewal()
	{
		if ($this->next_renewal == "0000-00-00 00:00:00")
			return "--";
		return date("m/d/y", strtotime($this->next_renewal));
	}

	public function getSubtotal()
	{
		return number_format($this->subtotal, 2, '.', ',');
	}

	public function getCredits()
	{
		if (!$this->credits)
			return "--";
		else
			return "$". number_format($this->credits, 2, '.', ',');
	}

	public function getTax()
	{
		if (!$this->tax)
			return "--";
		else
			return "$". number_format($this->tax, 2, '.', ',');
	}

	public function getTotal()
	{
		return number_format($this->total, 2, '.', ',');
	}
}
