<?php
namespace Pixonite\BillingBundle\Entity;

use Oranges\UserBundle\Helper\User;
use Oranges\sql\WgDbh;
use Oranges\sql\SqlTable;
use Oranges\errorHandling\ForceError;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\DatabaseModel;

use Doctrine\ORM\Mapping as ORM;

/**
 Contains billing data associated with each user. Like the user's billing
 preference, and how many credits they have in their account.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="billing_settings")
*/
class BillingSettings extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="integer") */
	protected $user_id = "";

	/** @ORM\Column(type="integer") */
	protected $contact_id = "";

	/** @ORM\Column(type="integer") */
	protected $default_billing_id = "";

	protected function getTable()
	{
		return "billing_settings";
	}



	private static $billing = null;

	/**
	 Returns the default billing for the user currently logged in.
	*/
	public static function getDefaultBilling()
	{
		if (self::$billing == null)
		{
			$billing = new BillingSettings();
			$out = $billing->loadUser(SessionManager::$user->id);

            //if this user does not have a default billing
            if (!$out)
                return null;

			self::$billing = $billing;
		}
		return self::$billing;
	}

	public function loadUser($id)
	{
		return $this->loadQuery("user_id = '". $id ."'", true);
	}

    public function toString()
    {
        return "Coming Soon!";
    }
}
