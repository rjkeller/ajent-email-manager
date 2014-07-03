<?php
namespace AjentServers\ExternalMailServerBundle\Handlers;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalMessage;

use AjentServers\ExternalMailServerBundle\Helper\MessageHandler;

class UnsubscribeHandler implements MessageHandler
{
	public function receiveMessage($user_id, ExternalMessage $msg)
	{
		$msg->delete();
	}
}