<?php

namespace Oranges\LoggingBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;

class LogCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('log')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = MongoDb::getDatabase();
		
		$logEntries = MongoDb::modelQuery(
			$db->logs->find()
			->sort(array('timestamp' => 1))
			->limit(10),
			"Oranges\LoggingBundle\Entity\LogEntry");

		foreach ($logEntries as $entry)
		{
			echo $entry->description . "\n";
		}
	}
}
