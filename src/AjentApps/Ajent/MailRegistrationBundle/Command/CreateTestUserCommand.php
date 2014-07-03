<?php

namespace AjentApps\Ajent\MailRegistrationBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use AjentApps\Ajent\MailRegistrationBundle\Form\RegisterForm;

class CreateTestUserCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('createTestUser')
            ->addArgument('username', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $frm = new RegisterForm();
        $frm->username = $input->getArgument("username");
        $frm->password = "456123";
        $frm->password_confirm = "456123";
        $frm->first_name = "Roger";
        $frm->last_name = "Keller";
        $frm->old_email_username = $frm->username ."@teamajent.com";
        $frm->old_email_password = "456123";
        $frm->old_email_mail_server = "mail3.ajent.net";
        $frm->old_email_account_type = "IMAP";
        $frm->accept_license_agreement = true;
        $frm->submitFormNoRedir();
        echo "Done\n";
	}
}
