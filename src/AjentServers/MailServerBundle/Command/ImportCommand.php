<?php

namespace AjentServers\MailServerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use AjentApps\MailServerManagerBundle\Entity\PostfixLog;

use Ajent\Mail\MailBundle\Entity\Contact;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\EmailMessage;

use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\LoggingBundle\Helper\Logger;

class ImportCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('postfix:import')
			->setHelp("Loads an email that postfix stuck on STDIN.")
            ->addArgument('to_email', InputArgument::REQUIRED, 'The email account to load the message into')
            ->addArgument('from_email', InputArgument::REQUIRED, 'The email account to load the message into')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

		$emailAccount = new EmailAccount();
		$isValidAccount = $emailAccount->loadFullEmailAddress(
			$input->getArgument("to_email"));

		if (!$isValidAccount)
		{
			$postfixLog = new PostfixLog();
			$cronLog->server_id = 2;
			$cronLog->log_entry = "HIT EMAIL: ". $input->getArgument("to_email") . " => INVALID";
			$cronLog->create();

			Logger::log("Email address is not valid", "mail");
			//maybe throw a bounce email?
			return;
		}

		$postfixLog = new PostfixLog();
		$cronLog->server_id = 2;
		$cronLog->log_entry = "HIT EMAIL: ". $input->getArgument("to_email") . " => Saving....";
		$cronLog->create();

		$emailMessage = new EmailMessage();

		$temp_file_name =  "mime_". uniqid(rand(), true);
		$html_file = tempnam(sys_get_temp_dir(), $temp_file_name);
		$fh = fopen($html_file, "w");

		Logger::log("Saving email to temp file...", "mail");
		//write all of STDIN to a temp file, that way we can load it into
		//GridFS and into the renderFromMaildir() function
		$stdin = fopen("php://stdin", "r");
		while (!feof($stdin))
		{
			fwrite($fh, fread($stdin, 1024));
		}
		Logger::log("done!", "mail");

		//close the file for writing, then reopen it for reading in the
		//renderFromMaildirFile() function.
		fclose($fh);

		Logger::log("Saving an email copy in MongoDb", "mail");

		//upload the MIME temp file to GridFS in case we need to re-do the
		//rendering if there's a bug or something.
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();
		$metadata = array("date" => new \MongoDate());
		$grid_id =
			$grid->storeFile($html_file, $metadata, array("safe" => true));
		$mime_file_id = $grid_id->__toString();

		Logger::log("Done! File ID: ". $mime_file_id, "mail");


		Logger::log("Beginning email rendering...", "mail");
		$fh = fopen($html_file, "r");

		$emailMessage->renderFromMaildirFile($emailAccount, $html_file);

		Logger::log("Completed email rendering.", "mail");
		Logger::log("Deleting temp files.", "mail");

		//delete the temp file.
		unlink($html_file);

		$emailMessage->create();
		Logger::log("Completed! Message:\n". print_r($emailMessage, true), "mail");

	}

	private function sendErrorEmail($emailAddress, $error)
	{
		$emailVars = array(
			"email_address" => $emailAddress,
			"error" => $error,
			"funcs" => $this
		);

		$mailer = $container->get('mailer');
		$message = \Swift_Message::newInstance()
			->setSubject('Mail delivery failed: returning to sender')
			->setFrom(BuildOptions::$get['from_name'] . " <". BuildOptions::$get['from_email'])
			->setTo($argv[2])
			->setBody(
				$container->get('templating')->render(
					'MailServerBundle:emails:error.twig.txt',
					 $emailVars))
			;
	}

	public function StreamEmail()
	{
		//write all of STDIN to a temp file, that way we can load it into
		//GridFS and into the renderFromMaildir() function
		$stdin = fopen("php://stdin", "r");
		while (!feof($stdin))
		{
			echo fread($stdin, 1024);
		}

		//close the file for writing, then reopen it for reading in the
		//renderFromMaildirFile() function.
		fclose($fh);
	}
}
