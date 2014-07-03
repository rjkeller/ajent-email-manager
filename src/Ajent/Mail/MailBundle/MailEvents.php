<?php

namespace Ajent\Mail\MailBundle;

final class MailEvents
{
	/**
	 * The MailBundle.EmailMessage.create event is thrown right before a new
	 * email object is created in the system. This gives you the option of
	 * modifying the email object right before it is created.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onPreEmailMessageCreate = 'MailBundle.EmailMessage.preCreate';

	/**
	 * The MailBundle.EmailMessage.create event is thrown right after a new
	 * email object is created in the system.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onPostEmailMessageCreate = 'MailBundle.EmailMessage.postCreate';


	/**
	 * The MailBundle.EmailMessage.create event is thrown before an existing
	 * email is modified in the system.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onEmailMessageSave = 'MailBundle.EmailMessage.save';

	/**
	 * The MailBundle.EmailMessage.create event is thrown after an existing
	 * email is modified in the system.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onEmailMessagePostSave = 'MailBundle.EmailMessage.postSave';

	/**
	 * The MailBundle.EmailMessage.trash event is thrown each time an existing
	 * email is moved to the user's Trash bin.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onEmailMessageTrash = 'MailBundle.EmailMessage.trash';

	/**
	 * The MailBundle.EmailMessage.delete event is thrown each time an existing
	 * email is permanently deleted from the system.
	 *
	 * The event listener receives an Ajent\Mail\MailBundle\Event\MailEvent
	 * instance.
	 *
	 * @var string
	 */
	const onEmailMessageDelete = 'MailBundle.EmailMessage.delete';

}