<?php
namespace Ajent\Mail\MailBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Alert;
use Ajent\Mail\MailBundle\Entity\EmailMessage;

class ForwardEmailForm extends WgForm
{
	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "ID must not be blank")
	*/
	public $id;

	/** @Assert\Type("string") */
	public $to_email;

	/** @Assert\Type("string") */
	public $message;

    /**
     * @Assert\True(message = "Your old email address you entered is invalid.")
     */
	public function isEmailValid()
	{
		return preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]+$/", $this->to_email);
	}

	public function getName()
	{
		return "ForwardEmail";
	}

	public function submitForm()
	{
		$msg = new EmailMessage();
		$msg->load($this->id);

		$contact = new Contact();
		$contact->loadUser();

		$mailer = MasterContainer::get("mailer");

		$message = \Swift_Message::newInstance();
		$message->setSubject("Fwd: ". $msg->subject);
		$message->setFrom(SessionManager::$user->username . "@ajent.com");
		$message->setTo(array($this->to_email));
		$message->setBody(
			$this->message . "\n\n" .
			$msg->getRawMessageBody()
		);

		if ($msg->body_type == "text/html")
		{
			$message->addPart($this->message . "<br><br>" .
				$msg->getRawMessageBody(), 'text/html');
		}
		$message->setDate(time());
		$mailer->send($message);


		MessageBoxHandler::happy(
			"You have successfully forwarded a message!",
			"Success!");
	}
}