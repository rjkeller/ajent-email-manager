<?php
namespace Pixonite\BillingBundle\Entity;

use Pixonite\BillingBundle\Helper\BillingMethod;
use Oranges\UserBundle\Helper\SessionManager;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="billing_admin")
*/
class AdminBillingMethod extends BillingMethod
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
		return "billing_admin";
	}

    public function deductFunds($amount)
	{
		$this->amount = $amount;
		$this->user_id = SessionManager::$user->id;
	    $this->status = "complete";
        $this->create();
	}

    public function getBillingMethodName()
    {
        return "Special Admin Account";
    }
}
