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

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

class CheckEmailCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('mail:checkEmail')
			->setHelp("Tests the email importing mechanism.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$q = Database::modelQuery("
			SELECT
				*
			FROM
				users
		", "Oranges\UserBundle\Entity\User");

		foreach ($q as $user)
		{
			$externalAccount = new ExternalAccount();
			if (!$externalAccount->loadUser($user->id))
			{
				continue;
			}
			\OrangesLogger("Syncing external account ". $externalAccount->username ."@". $externalAccount->server, "externalSync");

			$externalAccount->sync();
		}
	}
}