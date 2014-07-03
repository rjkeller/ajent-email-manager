<?php
namespace Pixonite\BillingBundle\Entity;

use Pixonite\BillingBundle\Helper\BluePayment;
use Pixonite\BillingBundle\Helper\BillingMethod;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\User;

use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\logging\Helper\Logger;
use Oranges\DatabaseModel;

use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="user_funds")
*/
class UserFunds extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;
	
	/** @ORM\Column(type="integer") */
	protected $user_id = 0;

	/** @ORM\Column(type="decimal") */
	protected $amount = 0.0;

	/** @ORM\Column(type="boolean") */
	protected $hasLowBalanceAlert = false;

	protected function getTable()
	{
		return "user_funds";
	}

	/**
	 *Adds the specified funds into the user's account.
	 *
	 *	@param $amount - The amount of money to add to the user's account.
	 */
	public function addFunds($amount)
	{
		$this->amount += $amount;
		$this->save();
	}

	/**
	 *Removes the specified amount from the user's account.
	 *
	 *	@param $amount - The amount of money to remove from the user's account.
	 */
	public function removeFunds($amount)
	{
		$this->amount = $this->amount - $amount;
		$this->save();
	}

	/**
	 * Passes in an amount, and returns whether or not the user has enough
	 * funds to purchase that item.
	 *
	 * @param $cost - The cost the user wants to withdraw.
	 */
	public function canPurchase($cost)
	{
		return $cost <= $this->amount;
	}

	/** Purchases an item with user funds, and returns an array with
	  how much money of the item baught was not paid for yet.
	  */
	public function halfPurchase($cost)
	{
		$notPaidFor = $cost - $this->getBalance();
		$this->removeFunds($this->getBalance());
		return $notPaidFor;
	}


	public function getBalance()
	{
		//if the account balance is blank, try and return zero as a float. This avoids
		//some number_format issues we were having.
		if (empty($this->amount))
			return 0.0;
		return $this->amount;
	}

	public function stringBalance()
	{
		return "$". number_format($this->amount , 2, '.', ',');
	}

	public function loadUser(User $user)
	{
		if (!$this->loadQuery("user_id = '". $user->id ."'", true))
		{
			$this->user_id = $user->id;
			$this->create();
		}
		return true;
	}

	/*************** STATIC FUNCTIONS *******************/
	/**
	 Returns the UserFunds object for this user.
	 */
	public static function getInstance()
	{
		$obj = new UserFunds();
		$obj->loadUser(SessionManager::$user);
		return $obj;
	}
}
