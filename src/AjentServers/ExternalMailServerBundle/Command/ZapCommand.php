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

class ZapCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('extzap')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

        $extAct = new ExternalAccount();
        $extAct->loadUser($user->id);

		$folders = $extAct->getFolders();
		if (isset($extAct->sync_message))
		    $syncIds = $extAct->sync_message;
		else
		    $syncIds = array();

		foreach ($folders as $f)
		{
			//open with write access
			if (!$f->open(true))
			{
				echo "WARNING: Bad folder. Skipping: ". $f->connString ."\n";
				continue;
			}

			if (!isset($syncIds[$f->connString]))
			{
				$syncIds[$f->connString] = 1;
			}

			$messages = $f->getAllMessages($syncIds[$f->connString]);

			echo "Opening folder ". $f->connString . "\n";
			foreach ($messages as $msg)
			{
			    if ($msg->message_id > $syncIds[$f->connString])
			        $syncIds[$f->connString] = $msg->message_id;
            }
        }

		$extAct->sync_message = $syncIds;
		$extAct->save();
	}
}
