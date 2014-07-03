<?php
namespace Pixonite\BillingBundle\Entity;

use Pixonite\BillingBundle\Helper\BluePayment;
use Pixonite\BillingBundle\Helper\BillingMethod;

use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\logging\Helper\Logger;

use Doctrine\ORM\Mapping as ORM;

/**
 Provides a billing interface to charge a credit card in a user's WordGrab account.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="billing_bluepay")
*/
class BluePayBillingMethod extends BillingMethod
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
    protected $id;

	/** @ORM\Column(type="integer") */
    protected $user_id;
    
	/** @ORM\Column(type="decimal") */
    protected $amount;

	/** @ORM\Column(type="integer") */
    protected $credit_card_number;

	/** @ORM\Column(type="date") */
    protected $expiration_date;
    
	/** @ORM\Column(type="integer") */
    protected $number_on_back;
    
	/** @ORM\Column(type="string") */
    protected $first_name;

	/** @ORM\Column(type="string") */
    protected $last_name;

	/** @ORM\Column(type="string") */
    protected $address;
    
	/** @ORM\Column(type="string") */
    protected $city;
    
	/** @ORM\Column(type="string", length=2) */
    protected $state;
    
	/** @ORM\Column(type="string", length=10) */
    protected $zip;
    
	/** @ORM\Column(type="string", length=2) */
    protected $country = "US";
    
	/** @ORM\Column(type="string", length=15) */
    protected $status = "pending";

	/** @ORM\Column(type="string", length=50) */
    protected $transaction_id = 0;

	protected function getTable()
	{
		return "billing_bluepay";
	}

    public function getTransactionId()
    {
        $this->transaction_id;
    }

    public function deductFunds($amount)
	{
		$this->amount = $amount;
		$this->user_id = SessionManager::$user->id;

//		if ($_POST['billingMethod'] == "cc" && Billing::getDefaultBilling() == null)
//			throw new UnrecoverableSystemException("No credit card number on file", "You currently do not have a default credit card on file.<br><br><a href=\"billing_default.php?checkout=1\">Edit Default Billing</a>");

	    Logger::log("Charging credit card (for user ". User::$username .") $ $dollars dollars", "billing");

		/*
			if ($this->cc['numberOnBack'] == 777 &&
				$this->cc['number'] == 1111222233334444 &&
				$this->cc['expirationDate'] == 1122)
				return 1234;
			else
				throw new UnrecoverableSystemException("Invalid Credit Card Number", "The following error has occured when trying to complete your credit card purchase: Your credit card number has been declined. Please retry the transaction with a new credit card number.");
		*/
		$bp = new BluePayment();
		$bp->sale($dollars);

		$bp->setCustInfo($this->credit_card_number,
	           $this->number_on_back,
	           $this->expiration_date,
	           $this->first_name,
	           $this->last_name,
	           $this->address,
	           $this->city,
	           $this->state,
	           $this->zip,
	           $this->country);
		$bp->process();
	    if ($bp->getStatus() == "E")
	    {
	        $this->status = "declined";
	        $this->save();
			throw new UnrecoverableSystemException("Invalid Credit Card Number", "The following error has occurred when trying to complete your credit card purchase: ". $bp->getMessage() . ". Please retry the transaction with a new credit card number.");
		}

	    $this->transaction_id = $bp->getTransId();
	    $this->status = "complete";
        $this->create();
	}

    public function getBillingMethodName()
    {
        return "Credit Card";
    }
}
