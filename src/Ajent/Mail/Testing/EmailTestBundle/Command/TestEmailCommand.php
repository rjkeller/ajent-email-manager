<?php

namespace Ajent\Mail\Testing\EmailTestBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\MasterContainer;
use Oranges\framework\BuildOptions;

use Ajent\Vendor\VendorBundle\Entity\VendorCategory;
use Ajent\Vendor\VendorBundle\Event\VendorCategoryMailListener;
use Ajent\Vendor\VendorBundle\Event\VendorCacheListener;
use Ajent\Mail\MailBundle\Event\CategoryMailListener;

class TestEmailCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('email:test:send')
			->setHelp("Flushes out Redis stats.");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$vars = array(
			"first_name" => "Roger",
			"username" => "rjkeller");

		$mailer = MasterContainer::get("mailer");
		$twig = MasterContainer::get("templating");

		$message = \Swift_Message::newInstance();
		$message->setSubject("Thank you for Joining Ajent!");
		$message->setFrom(BuildOptions::$get['from_email']);
		$message->setTo(array("rjkeller@pixonite.com"));
		$message->setBody(
			"Ajent requires an HTML mail client to view this email."
		);
		$message->addPart(
			$twig->render('MailRegistrationBundle:emails:Welcome.twig.html', $vars),
			'text/html');
		$message->setDate(time());
		$mailer->send($message);
	}
}