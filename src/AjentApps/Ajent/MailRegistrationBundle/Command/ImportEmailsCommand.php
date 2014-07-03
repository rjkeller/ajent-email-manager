<?php

namespace AjentApps\Ajent\MailRegistrationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\MongoDbBundle\Helper\MongoDb;
use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

class ImportEmailsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('importemails')
            ->addArgument('user_id', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = MongoDb::getDatabase();
		$user_id = $input->getArgument("user_id");

		//for the remaining vendors, we'll import the last 3 emails for
		//that vendor.
		$q = MongoDb::modelQuery($db->vendors->find(array(
				'user_id' => $user_id,
				'pendingAddToAjent' => true,
				'is_unsubscribed' => false,
				'is_invisible' => false
			)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor")
			    ->getArray();

		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser($user_id);

		VendorScan::importVendorStarterEmails($externalAccount, $q->getArray());

		VendorScan::cleanUpVendors($user_id);
	}
}


