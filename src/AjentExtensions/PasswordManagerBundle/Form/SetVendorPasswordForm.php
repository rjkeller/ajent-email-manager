<?php
namespace AjentExtensions\PasswordManagerBundle\Form;

use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;

use AjentExtensions\PasswordManagerBundle\Entity\VendorPassword;

class SetVendorPasswordForm extends WgForm
{
	/** @Assert\Type("numeric") */
	public $id;

	/** @Assert\Type("string") */
	public $password;

	public function getName()
	{
		return "SetVendorPassword";
	}

	public function submitForm()
	{
		$saveMe = false;
		$password = new VendorPassword();

		if ($password->loadVendor($this->id))
			$saveMe = true;

		$password->user_id = SessionManager::$user->id;
		$password->vendor_id = $this->id;
		$password->password = $this->password;

		if ($saveMe)
			$password->save();
		else
			$password->create();
	}
}