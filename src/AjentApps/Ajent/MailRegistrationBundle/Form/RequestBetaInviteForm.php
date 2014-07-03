<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Form;

use Oranges\sql\Database;
use Oranges\MasterContainer;

use Oranges\forms\WgForm;
use Oranges\framework\BuildOptions;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Ajent\MailRegistrationBundle\Entity\BetaInvite;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class RequestBetaInviteForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter your name")
     * @Assert\MinLength(limit = 2, message = "Please enter a valid name")
     * @Assert\Regex(pattern = "/^[\w\d-.]+/", message = "Please enter a name with only letters and numbers")
     */
	public $name;

    /**
     * @Assert\NotBlank(message = "Please enter your email address")
     * @Assert\MinLength(limit = 2, message = "Please enter a valid email address")
     */
	public $email;

	public function submitForm()
	{
		$betaInvite = new BetaInvite();
		$betaInvite->name = $this->name;
		$betaInvite->email = $this->email;
		$betaInvite->create();

		$mailer = MasterContainer::get("mailer");

		$message = \Swift_Message::newInstance();
		$message->setSubject("Ajent.com: New Beta Invite Request");
		$message->setFrom(BuildOptions::$get['from_email']);
		$message->setTo(array("rjkeller@pixonite.com"));
		$message->setBody(
			"The following person requested an Ajent beta invite:\n\n". $betaInvite->name . "\nEmail: ". $betaInvite->email. "\n\nDate: ". $betaInvite->date
		);
		$message->setDate(time());
		$mailer->send($message);

		MessageBoxHandler::happy("Your beta invite request has been successfully submitted", "Success!");
	}

	public function getName()
	{
		return "RequestBetaInviteForm";
	}
}
