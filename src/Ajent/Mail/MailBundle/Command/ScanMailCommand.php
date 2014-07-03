<?php

namespace Ajent\Mail\MailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Helper\ImportGmailContacts;
use Ajent\Mail\MailBundle\Helper\ImportVendorsListener;
use Oranges\sql\Database;

class ScanMailCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mail:test:scan')
			->setHelp("Synchronizes all of the email messages in Maildir with MySQL.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/**
		 * REDACTED
		 */
	}
}