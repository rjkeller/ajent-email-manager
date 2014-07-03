<?php
namespace Ajent\Mail\MailBundle\Helper;

use Oranges\framework\BuildOptions;
use Ajent\Mail\MailBundle\Entity\EmailAccount;

class MaildirMessageListener implements EmailMessageListener
{
	public function emailReceived($maildir_data, EmailAccount $emailAccount)
	{
		$maildir_dir = BuildOptions::$get['MailBundle']['MailDirPath'] .
			$emailAccount->domain . "/". $emailAccount->username;
		if (!is_dir($maildir_dir))
		{
			mkdir($maildir_dir);
			mkdir($maildir_dir . "/Maildir");
			mkdir($maildir_dir . "/Maildir/new");
			mkdir($maildir_dir . "/Maildir/cur");
			mkdir($maildir_dir . "/Maildir/tmp");
		}
		
		$maildir_dir .= "/Maildir/new/";
		$maildir_email_file = $maildir_dir . md5(uniqid("", true));

		if (!is_dir($maildir_dir))
			mkdir($maildir_dir);

		file_put_contents($maildir_email_file, $maildir_data);
	}
}
