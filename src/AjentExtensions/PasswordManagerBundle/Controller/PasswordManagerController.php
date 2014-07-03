<?php
namespace AjentExtensions\PasswordManagerBundle\Controller;

use Ajent\AddonBundle\Helper\AjentAddon;

use AjentExtensions\PasswordManagerBundle\Form\SetVendorPasswordForm;
use AjentExtensions\PasswordManagerBundle\Entity\VendorPassword;

class PasswordManagerController extends AjentAddon
{
	public function ConstructAddon()
	{
		new SetVendorPasswordForm();
	}

	public function VendorIconsAction($vendor)
	{
		$vendorPassword = new VendorPassword();

		if ($vendorPassword->loadVendor($vendor->id))
		{
			return $this->render("PasswordManagerBundle:pages:PasswordManager.twig.html",
				array('vendor' => $vendor,
					'vendorPassword' => $vendorPassword));
		}

		return $this->render("PasswordManagerBundle:pages:PasswordManager.twig.html",
			array('vendor' => $vendor));
		
	}
}