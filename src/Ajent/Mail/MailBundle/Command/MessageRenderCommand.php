<?php

namespace Ajent\Mail\MailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Ajent\Mail\MailBundle\Entity\Contact;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Oranges\sql\Database;

class MessageRenderCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('postfix:rerender')
			->setHelp("Loads an email that postfix stuck on STDIN.")
            ->addArgument('email_id', InputArgument::REQUIRED, 'The email account to load the message into')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$msg = new EmailMessage();
		$msg->load($input->getArgument("email_id"));
		$msg->rerenderEmail();
	}
}
