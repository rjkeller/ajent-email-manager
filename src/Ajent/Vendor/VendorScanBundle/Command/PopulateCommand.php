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

use Oranges\MasterContainer;

class PopulateCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('populate')
            ->addArgument('email', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $email = $input->getArgument("email");

        $mailer = MasterContainer::get("mailer");

		$message = \Swift_Message::newInstance();
		$message->setSubject("Welcome to R.J. Inc!");
		$message->setFrom("do-not-reply@teamajent.com");
		$message->setTo(array($email));
		$message->setBody(
		    "subscribe unsubscribe"
		);

		$message->setDate(time());
		$mailer->send($message);
		echo "Done!\n";
	}
}
