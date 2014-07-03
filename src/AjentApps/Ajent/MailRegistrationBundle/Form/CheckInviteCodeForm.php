<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Form;

use Oranges\forms\WgForm;

use Symfony\Component\Validator\Constraints as Assert;
use AjentApps\Ajent\MailRegistrationBundle\Entity\InviteCode;

/**
 This form processes a new user registration.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class CheckInviteCodeForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Sorry, the invite code you entered is not valid.")
     * @Assert\MinLength(limit = 3, message = "Please enter a valid invite code")
     * @Assert\Regex(pattern = "/^[\w\d]+/", message = "Please enter a valid invite code")
     */
	public $invite_code;


    /**
     * @Assert\True(message = "Sorry, the invite code you entered is not valid.")
     */
	public function isValidInviteCode()
	{
		//if the invite code is empty, return true here, because the @Assert
		//statement above will trigger an error, and we don't want to show
		//"Invalid Invite Code" error twice.
		if (empty($this->invite_code))
			return true;

		$code = new InviteCode();
		return $code->loadInviteCode($this->invite_code);
	}


	public function getName()
	{
		return "CheckInviteCode";
	}
}
