<?php

namespace Ajent\Vendor\VendorBundle\Command;

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
use Ajent\Vendor\VendorBundle\Entity\Vendor;

class UnsubscribeCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('unsubscribe')
            ->addArgument('vendor_id', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	    $vendor = new Vendor();
	    $vendor->load($input->getArgument("vendor_id"));
        $vendor->is_unsubscribed = true;
		$vendor->save();

		echo "Done!\n";
	}
}
