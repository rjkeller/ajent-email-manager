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

use AjentServers\ExternalMailServerBundle\Helper\Sync;

class MaxIDCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('ext_maxid')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

        $extAct = new ExternalAccount();
        $extAct->loadUser($user->id);

        if (!isset($extAct->sync_message))
            echo "N/A\n";
        else
		    print_r($extAct->sync_message);
	}
}
