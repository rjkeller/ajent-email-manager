<?php

namespace Ajent\Mail\ExternalMailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;

class RerenderCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mail:renderAll')
			->setHelp("Re-renders all the emails.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = MongoDb::getDatabase();
		$q = MongoDb::modelQuery(
			$db->email_messages->find(),
			"Ajent\Mail\MailBundle\Entity\EmailMessage");

		foreach ($q as $message)
		{
			$message->rerenderEmail();
		}
	}
}