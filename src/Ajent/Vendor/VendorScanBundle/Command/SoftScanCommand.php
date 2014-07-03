<?php

namespace Ajent\Vendor\VendorScanBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\UserBundle\Entity\User;
use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

class SoftScanCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('softscan')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser($user->id);

		$scanner = new VendorScan();
		$hasMore = $scanner->scanForVendors($externalAccount);

        echo "----------------------------\n";
		foreach ($scanner->vendors as $v)
		{
		    print_r($v->getArray());
		}
        echo "----------------------------\n";
        if (!$hasMore)
            echo "No more vendors to load either!\n";
        else
            echo "There are more vendors. only showing first 12 above\n";
	}
}
