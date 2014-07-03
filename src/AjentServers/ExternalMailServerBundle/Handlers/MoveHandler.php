<?php
namespace AjentServers\ExternalMailServerBundle\Handlers;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalMessage;
use Ajent\Mail\MailBundle\Entity\EmailAccount;

use AjentServers\ExternalMailServerBundle\Helper\MessageHandler;

class MoveHandler implements MessageHandler
{
	public function receiveMessage($user_id, ExternalMessage $msg)
	{
		$emailAccount = new EmailAccount();
		$emailAccount->loadUser($user_id);

		$message = $msg->copyToEmailAccount($emailAccount);
		$message->create();
		echo "++++ Copying email to Ajent\n";
		$msg->delete();
	}
}
