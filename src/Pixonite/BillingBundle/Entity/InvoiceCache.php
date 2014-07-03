<?php
namespace Pixonite\BillingBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="invoice_cache")
*/
class InvoiceCache extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
    protected $user_id;

	/** @ORM\Column(type="string", length="20") */
    protected $type;

	/** @ORM\Column(type="string", length="8") */
	protected $invoice_id;

	/** @ORM\Column(type="string") */
	protected $description;

	/** @ORM\Column(type="datetime") */
	protected $date;

	/** @ORM\Column(type="decimal") */
	protected $amount;

	/** @ORM\Column(type="decimal") */
	protected $balance;

	protected function getTable()
	{
		return "invoice_cache";
	}

    public function getImage()
    {
        switch ($this->type)
		{
		case "monitizationPayment":
				return "/bundles/billing/images/BillingPow_esm.gif";
		case "domainAutoRenew":
		case "domainRenew":
		case "hostingRenew":
		case "emailRenew":
				return "/bundles/billing/images/BillingMandRecur_esm.gif";
		case "escrowOrder":
				return "/bundles/billing/images/EscrowOut_esm.gif";
		case "sslCertOrder":
				return "/bundles/billing/images/BillingSSL_esm.gif";
		case "autoRefill":
				return "/bundles/billing/images/BillingRefill_esm.gif";
		case "credit":
				return "/bundles/billing/images/BillingSupport_esm.gif";
		case "manualRefill":
				return "/bundles/billing/images/BillingManRefill_esm.gif";
		case "domainRegistration":
		default:
				return "/bundles/billing/images/billing_esm.gif";
		}
    }

    public function getInvoiceUrl()
    {
        if ($this->invoice_id == -1)
			return "#";
		else
			return "billing/invoice/". $this->invoice_id;
    }

    public function getName()
    {
        return WgTextTools::truncate($this->name, 80);
    }

    public function getAmount()
    {
        return number_format($this->amount, 2, '.', ',');
    }

    public function getBalance()
    {
        return number_format($this->balance , 2, '.', ',');
    }

    public function getDate()
    {
        return date("m/d/y", strtotime($this->date));
    }
}
