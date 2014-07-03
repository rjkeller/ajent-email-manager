<?php
namespace Pixonite\BillingBundle\Entity;

use Oranges\UserBundle\Helper\User;
use Oranges\sql\WgDbh;
use Oranges\sql\SqlTable;
use Oranges\errorHandling\ForceError;

use Pixonite\BillingBundle\Helper\BillingMethod;

use Oranges\DatabaseModel;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="billing_transaction")
*/
class BillingTransaction extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="integer") */
	protected $user_id = 0;

	/** @ORM\Column(type="string") */
	protected $billing_type = "";

	/** @ORM\Column(type="integer") */
    protected $billing_type_id = 0;

	protected function getTable()
	{
		return "billing_transaction";
	}

    public function loadBillingMethod(BillingMethod $method)
    {
        $this->billing_type = get_class($method);
        $this->user_id = SessionManager::$user->id;
    }

    /**
     Performs the actual billing transaction by deducting the requested
     amount from the user's account using the billing method passed in.
     */
    public function performTransaction($amount)
    {
        $str = $this->billing_type;

        $billingMethod = new $str();
        $billingMethod->load($billing_type_id);
        //this method call should throw an exception if it fails. so we
        //don't need to worry about error handling
        $billingMethod->deductFunds($amount);

        $this->create();
    }
}
