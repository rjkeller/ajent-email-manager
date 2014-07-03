<?php

namespace AjentApps\Ajent\MailRegistrationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;

class UlsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('uls')
			;
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
        echo "UID\tUsername\tEmail\n";
        echo "--------------------------------------------\n";
        foreach ($q as $user)
            echo $user->id . "\t". $user->username ."\t". $user->email ."\n";
	}
}
