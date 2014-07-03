<?php
namespace Ajent\Mail\MailBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\forms\WgForm;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\AlertBundle\Entity\Alert;
use Ajent\Mail\MailBundle\Entity\EmailMessage;

class EmailExpirationForm extends WgForm
{
	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "ID must not be blank")
	*/
	public $id;

	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "Alert Date must not be blank")
	 */
	public $date;

	/** @Assert\Type("string") */
	public $incr;

	/** @Assert\Type("string") */
	public $message;

	public function getName()
	{
		return "EmailExpiration";
	}

	public function submitForm()
	{
		$message = new EmailMessage();
		$message->load($this->id);

		$date = new \DateTime($this->date,
					new \DateTimeZone("GMT"));
		switch ($this->incr)
		{
		case '15min':
			$date->modify("+15 minutes");
			break;
		
		case '1day':
			$date->modify("+24 hours");
			break;
		
		case '1week':
			$date->modify("+1 week");
			break;
		
		case '1month':
			$date->modify("+1 month");
			break;
			
		}
		$message->date = $date->getTimestamp();
		$message->save();

		$alert = new Alert();
		$alert->message_id = $message->_id;
		$alert->user_id = SessionManager::$user->id;
		$alert->type = "Expiration";

		$expirationDate = new \DateTime($this->date,
					new \DateTimeZone("GMT"));
		$expirationDate->setTimestamp(strtotime($this->date));

		$alert->expiration_date = $expirationDate->getTimestamp();
		$alert->message = $this->message;
		$alert->vendor_id = $message->vendor_id;
		$alert->create();

		MessageBoxHandler::happy(
			"You have successfully created a message alert.",
			"Success!");
	}
}