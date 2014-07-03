<?php
namespace Pixonite\BillingBundle\Entity;

use Pixonite\BillingBundle\Helper\BluePayment;
use Pixonite\BillingBundle\Helper\BillingMethod;

use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\logging\Helper\Logger;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="billing_user_funds")
*/
class UserFundsBillingMethod extends BillingMethod
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
    protected $id;

	/** @ORM\Column(type="integer") */
    protected $user_id;

	/** @ORM\Column(type="integer") */
    protected $amount;
    
	/** @ORM\Column(type="string", length=15) */
    protected $status = "pending";

	protected function getTable()
	{
		return "billing_user_funds";
	}

    public function deductFunds($amount)
	{
		$this->amount = $amount;
		$this->user_id = SessionManager::$user->id;

		$funds = UserFunds::getInstance();
		if ($funds->canPurchase($dollars))
			$funds->removeFunds($dollars);
		else
		{
		    $this->status = "failed";
		    $this->create();
			throw new UserErrorException("Insufficient Funds", "We're sorry, but your account lacks sufficient funds to perform the transaction. Please add more money to your account before continuing.");
		}
	    $this->status = "complete";
        $this->create();
	}

    public function getBillingMethodName()
    {
        return "Pay With my Account Balance";
    }
}
