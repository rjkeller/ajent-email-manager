<?php
namespace Ajent\Vendor\VendorBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\InternalException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

class DeleteVendorForm extends WgForm
{
	/** @Assert\Type("numeric") */
	public $id;

	public function getName()
	{
		return "DeleteVendor";
	}

	public function submitForm()
	{
		$v = new Vendor();
		$v->load($this->id);
		if ($v->user_id != SessionManager::$user->id)
		{
			throw new InternalException("Security violation detected: Cannot edit vendor because Access is Denied");
		}
		$v->is_rejected = true;
		$v->save();

		MessageBoxHandler::happy(
			"You have successfully unsubscribed from this vendor.",
			"Success!");		
	}
}