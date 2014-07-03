<?php

namespace Ajent\Mail\MailBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Ajent\Mail\MailBundle\Entity\Contact;
use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Oranges\sql\Database;
use Oranges\MongoDbBundle\Helper\MongoDb;

class PostfixFindDeadMessagesCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('postfix:findDeadMessages')
			->setHelp("Loads an email that postfix stuck on STDIN.")
            ->addOption('delete', null, InputOption::VALUE_NONE, 'If set, the task will delete all the dead files')
			;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$db = MongoDb::getDatabase();

		$grid = $db->getGridFS();
		$out = $grid->find();

		$allIds = array();

		foreach ($out as $i)
		{
			$allIds[] = $i->file['_id']->__toString();
		}

		$q = MongoDb::modelQuery($db->email_messages->find(),
			"Ajent\Mail\MailBundle\Entity\EmailMessage");

		foreach ($q as $msg)
		{
			foreach ($allIds as $key => $value)
			{
				if ($value == $msg->body_file_id ||
					$value == $msg->mime_file_id)
					unset($allIds[$key]);
			}
		}
		print_r($allIds);

		if ($input->getOption("delete"))
		{
			foreach ($allIds as $id)
			{
				$grid->delete(new \MongoId($id));
			}
		}
	}
}