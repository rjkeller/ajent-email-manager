<?php
namespace AjentServers\ExternalMailServerBundle\Helper;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalMessage;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
interface MessageHandler
{
	public function receiveMessage($user_id, ExternalMessage $msg);
}
