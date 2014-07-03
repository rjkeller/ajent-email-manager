<?php
namespace AjentExtensions\PasswordManagerBundle\Entity;

use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;
use Oranges\DatabaseModel;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="password_manager_vendor_password")
*/
class VendorPassword extends DatabaseModel
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
	protected $vendor_id;

	/** @ORM\Column(type="string") */
	protected $password;


	protected function getTable()
	{
		return "password_manager_vendor_password";
	}


	public function __construct()
	{
		parent::__construct();

		$this->__key = md5("AjentExtensions\PasswordManagerBundle\Entity\VendorPassword");
		$this->__encrypt['password'] = true;
	}

	public function loadVendor($vendor_id)
	{
		$user_id = SessionManager::$user->id;

		return parent::loadQuery(
			"user_id = '". $user_id ."' AND
			vendor_id = '". $vendor_id ."'",
			true);
	}
}
