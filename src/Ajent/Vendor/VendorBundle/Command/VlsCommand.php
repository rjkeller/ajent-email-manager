<?php

namespace Ajent\Vendor\VendorBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\UserBundle\Entity\User;
use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

class VlsCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('vls')
            ->addArgument('username', InputArgument::REQUIRED, '')
			->setHelp("Dumps out a list of vendors");
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

		$db = MongoDb::getDatabase();

		$query = MongoDb::modelQuery($db->vendors->find(array(
				"user_id" => $user->id)),
				"Ajent\Vendor\VendorBundle\Entity\Vendor");

		echo "Name\tEmail\tis_rejected\thas_alert\tis_ignored\tunsubscribed\tID\n";
		echo "------------------------\n";
        foreach ($query as $v)
        {
			echo "$v->name\t$v->email_suffix\t$v->is_rejected\t$v->has_alert\t$v->is_ignored\t$v->is_unsubscribed\t$v->id\n";
        }
	}
}
