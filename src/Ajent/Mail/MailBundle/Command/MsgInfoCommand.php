<?php

namespace Ajent\Mail\MailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\MailBundle\Entity\EmailMessage;

class MsgInfoCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('msginfo')
            ->addArgument('message_id', InputArgument::REQUIRED, '')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$msg = new EmailMessage();
		$msg->load($input->getArgument("message_id"));

		print_r($msg->getArray());
	}
}