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
use Oranges\UserBundle\Helper\SessionManager;

use Ajent\Vendor\VendorScanBundle\Helper\VendorScan;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

class RjTestCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('rjtest')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $user = new User();
        $user->loadUsername($input->getArgument("username"));

		$external_account = new ExternalAccount();
		$external_account->loadUser($user->id);


		$inbox = $external_account->getInbox();

		echo $inbox->connString ."\n";
        $mbox = imap_open(
			    $inbox->connString,

				$external_account->username,
				$external_account->password);

		$check = imap_mailboxmsginfo($mbox);
		
		echo "Messages before delete: " . $check->Nmsgs . "\n";

		imap_delete($mbox, 1);

		$check = imap_mailboxmsginfo($mbox);
		echo "Messages after  delete: " . $check->Nmsgs . "\n";

		imap_expunge($mbox);

		$check = imap_mailboxmsginfo($mbox);
		echo "Messages after expunge: " . $check->Nmsgs . "\n";

		imap_close($mbox);

		echo "Done\n";
		echo imap_last_error();
	}
}
