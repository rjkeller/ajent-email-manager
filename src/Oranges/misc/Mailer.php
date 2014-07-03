<?php
namespace Oranges\misc;

use Oranges\sql\SqlTable;
use Oranges\sql\Database;

use Oranges\errorHandling\InternalErrorException;
use \Build_Options;

/**
 This class helps automate the process of sending email by providing an interface
 similar to the PHP mail() function. This class also provides the ability to queue
 email messages to be sent later.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class Mailer
{
	public static $useQueue = false;

	public static $bcc = null;

	public static function sendMail($to, $subject, $message, $attachments = null)
	{
		if (self::$useQueue)
		{
			$tbl = new SqlTable("emails");
			$tbl->to = base64_encode($to);
			$tbl->subject = base64_encode($subject);
			$tbl->message = base64_encode($message);
			$tbl->tableInsert();
			return true;
		}

		if (!Build_Options::$ENABLE_EMAIL)
			return true;

		require_once(dirname(__FILE__). "/../../../vendor/mail_lib/swift_required.php");

		$transport = Swift_SmtpTransport::newInstance()
			->setHost(Build_Options::$SMTP_SERVER)
			->setPort(25)
//			->setEncryption('tls')
			->setUsername(Build_Options::$SMTP_USER)
			->setPassword(Build_Options::$SMTP_PASS);
		$mailer = Swift_Mailer::newInstance($transport);

		$bcc = Build_Options::$ADMIN_EMAIL;
		if (self::$bcc != null)
			$bcc = self::$bcc;

		//Create the message
		$message = Swift_Message::newInstance()
		  ->setSubject($subject)
		  ->setFrom(array(Build_Options::$FROM_EMAIL => Build_Options::$FROM_NAME))
		  ->setTo(array($to))
		  ->setBcc(array($bcc))
		  ->setBody($message);

		if ($attachments != null)
		{
			foreach ($attachments as $i)
				$message->attach(Swift_Attachment::fromPath($i));
		}
		return $mailer->send($message);
	}

	public static function sendAdminMail($subject, $message, $attachments = null)
	{
		if (self::$useQueue)
		{
			$tbl = new SqlTable("emails");
			$tbl->to = base64_encode($to);
			$tbl->subject = base64_encode($subject);
			$tbl->message = base64_encode($message);
			$tbl->tableInsert();
			return true;
		}

		if (!Build_Options::$ENABLE_EMAIL)
			return true;

		require_once(dirname(__FILE__). "/../../../vendor/mail_lib/swift_required.php");

		$transport = Swift_SmtpTransport::newInstance()
			->setHost(Build_Options::$SMTP_SERVER)
			->setPort(25)
//			->setEncryption('tls')
			->setUsername(Build_Options::$SMTP_USER)
			->setPassword(Build_Options::$SMTP_PASS);
		$mailer = Swift_Mailer::newInstance($transport);

		$bcc = Build_Options::$ADMIN_EMAIL;
		if (self::$bcc != null)
			$bcc = self::$bcc;

		//Create the message
		$message = Swift_Message::newInstance()
		  ->setSubject($subject)
		  ->setFrom(array(Build_Options::$FROM_EMAIL => Build_Options::$FROM_NAME))
		  ->setTo(array($bcc))
		  ->setBody($message);

		if ($attachments != null)
		{
			foreach ($attachments as $i)
				$message->attach(Swift_Attachment::fromPath($i));
		}
		return $mailer->send($message);
	}

	public static function mailTemplate($to, $contents)
	{
		if (empty($contents))
			throw new InternalErrorException("Email is blank. Smarty error?");

		//extracting out the "Subject" line from the templates.
		$email = strpos($contents, "\n", 1);
		$subject = substr($contents, 9, $email - 9);
		$contents = substr($contents, $email+1, strlen($contents));

		return self::sendMail($to, $subject, $contents);
	}

	public static function mailFile($to, $subject, $file, $var)
	{
		if (!Build_Options::$ENABLE_EMAIL)
			return true;
		$fh = fopen($file, 'r');
		$message = fread($fh, filesize($file));
		fclose($fh);

		$out = explode("%", $message);
		$message = "";
		$len = sizeof($out);
		for ($i = 0; $i < $len; $i++)
		{
			if ($i % 2 == 0)
			{
				$message .= $out[$i];
			}
			else
			{
				$message .= $var[$out[$i]];
			}
		}

		self::sendMail($to, $subject, $message);
	}

	public static function runQueue()
	{
		self::$useQueue = false;
		$q = Database::query("SELECT * FROM emails WHERE isProcessed = FALSE");
		while ($email = $q->fetch_object())
		{
			self::sendMail(base64_decode($email->to),
				base64_decode($email->subject),
				base64_decode($email->message));
			echo base64_decode($email->to) . "<br>". base64_decode($email->subject) . "<br>". base64_decode($email->message) . "<br>";
			Database::query("UPDATE emails SET isProcessed = TRUE WHERE id = '$email->id'");
		}
		$q->close();
		self::$useQueue = true;
	}
}
