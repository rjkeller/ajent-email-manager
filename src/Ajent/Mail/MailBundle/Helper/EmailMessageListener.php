<?php
namespace Ajent\Mail\MailBundle\Helper;

use Ajent\Mail\MailBundle\Entity\EmailAccount;

/**
 This interface is designed to parse each and every email as they are received
 by the system. The idea is loading 1,000,000+ messages, and then parsing them,
 would take too much memory and crash the server. So parsing them one at a time
 as they're received will allow us to support huge email accounts.

 This interface gives us the ability to supply the "email parser", which can
 perform specific instructions over a specific email. The MailReader class will
 run the emailReceived() method for every email they read from the user's email
 account.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
interface EmailMessageListener
{
	/**
	 Every time an email is received through MailReader, this method will be
	 called with the message details.
	*/
	public function emailReceived($maildir_data, EmailAccount $emailAccount);
}