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
use Oranges\sql\Database;

class SyncContactsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mail:contacts:sync')
			->setHelp("Synchronizes all of the email contacts.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		Contact::refreshContactCache();
	}
}