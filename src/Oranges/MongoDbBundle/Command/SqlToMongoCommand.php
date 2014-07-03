<?php

namespace Oranges\MongoDbBundle\Command;

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
use Oranges\MongoDbBundle\Helper\MongoDb;

class SqlToMongoCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mongo:import')
			->setHelp("Copies a MySQL table to MongoDB")
            ->addArgument('table', InputArgument::REQUIRED, 'The email account to load the message into')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$table = $input->getArgument("table");
		$q = Database::query("
			SELECT
				*
			FROM
				". $table);

		$db = MongoDb::getDatabase();
		$c = $db->selectCollection($table);
		foreach ($q as $i)
		{
			$i['_id'] = $i['id'];
			unset($i['id']);
			print_r($i);
			$c->insert($i);
		}
	}
}