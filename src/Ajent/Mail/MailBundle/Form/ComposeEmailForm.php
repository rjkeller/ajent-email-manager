<?php
namespace Ajent\Mail\MailBundle\Form;

use Symfony\Component\Validator\Constraints as Assert;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MasterContainer;
use Oranges\forms\WgForm;

use Ajent\Mail\MailBundle\Entity\EmailAccount;

class ComposeEmailForm extends WgForm
{
	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "You must enter an email address to send this email to")
	*/
	public $to;

	/** @Assert\Type("string") */
	public $cc;

	/** @Assert\Type("string") */
	public $bcc;

	/** @Assert\Type("string") */
	public $subject;

	/** @Assert\Type("string") */
	public $message;

	public function getName()
	{
		return "ComposeEmail";
	}

	public function submitForm()
	{
		$contact = new Contact();
		$contact->loadUser();

		$emailAccount = new EmailAccount();
		$emailAccount->loadUser();

		$container = MasterContainer::getContainer();

		$mailer = $container->get('mailer');
		$message = \Swift_Message::newInstance();
		$message->setSubject($this->subject);
//		array('john@doe.com' => 'John Doe')
		$message->setFrom(array(
			$emailAccount->full_email_address =>
				$contact->first_name . ' '. $contact->last_name
			)
		);
		$message->setTo($this->to);
		if ($this->cc != "")
			$message->setCc($this->cc);
		if ($this->bcc != "")
			$message->setBcc($this->bcc);
		$message->setBody(strip_tags($_POST['message']));
		$message->addPart($_POST['message'], 'text/html');

		$mailer->send($message);

		MessageBoxHandler::happy(
			"Your email has been successfully sent.",
			"Success!");
		
	}
}