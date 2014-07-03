<?php
namespace Ajent\Mail\MailBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Ajent\Mail\MailBundle\Entity\EmailMessage;

/**
 * An event that is triggered when an email modification has occurred.
 */
class MailEvent extends Event
{
	protected $message;

	protected $isChanged;

	public function __construct(EmailMessage $message, $isChanged = null)
	{
		$this->message = $message;
		$this->isChanged = $isChanged;
	}

	public function getMessage()
	{
		return $this->message;
	}

	/**
	 Returns an array of columns and whether or not their value has been
	 changed. This is ONLY set if this is a save() event.
	
	 If not a save() event, returns null.
	*/
	public function isChanged()
	{
		return $this->isChanged;
	}
}