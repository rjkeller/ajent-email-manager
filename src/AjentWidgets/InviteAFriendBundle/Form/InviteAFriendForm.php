<?php
namespace AjentWidgets\InviteAFriendBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

class InviteAFriendForm extends WgForm
{
	/** @Assert\Type("string") */
	public $email;

	public function getName()
	{
		return "InviteAFriend";
	}

	public function submitForm()
	{
		MessageBoxHandler::happy(
			"Thank you for inviting your friend!",
			"Success!");
	}
}
