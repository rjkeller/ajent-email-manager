<?php

namespace Ajent\Mail\ExternalMailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\UserBundle\Entity\User;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

use Oranges\MongoDbBundle\Helper\MongoDb;

class ExternalImportAllMailCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mail:external:importAllMail')
            ->addArgument('username')
			->setHelp("Imports all email messages from an external email account set up for that user.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$q = Database::modelQuery("
			SELECT
				*
			FROM
				users
			",
			"Oranges\UserBundle\Entity\User");
		foreach ($q as $user)
		{
			$db = MongoDb::getDatabase();
			$db->email_messages->remove();

			$externalAccount = new ExternalAccount();
			if (!$externalAccount->loadUser($user->id))
				continue;
			$externalAccount->importEmails();
		}
	}
}
