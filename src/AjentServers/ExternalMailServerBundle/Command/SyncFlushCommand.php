<?php

namespace AjentServers\ExternalMailServerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\UserBundle\Entity\User;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

use AjentServers\ExternalMailServerBundle\Handlers\MoveHandler;
use AjentServers\ExternalMailServerBundle\Handlers\UnsubscribeHandler;

class SyncFlushCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('syncflush')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

		$extAct = new ExternalAccount();
		$extAct->loadUser($user->id);

		echo "Found:\n";
		print_r($extAct->sync_message);

		echo "\n\nDone\n";
		$extAct->sync_message = array();
		$extAct->save();
	}
}
