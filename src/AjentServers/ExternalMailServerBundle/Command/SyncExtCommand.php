<?php

namespace AjentServers\ExternalMailServerBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;

use Oranges\sql\Database;
use Oranges\UserBundle\Entity\User;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

use AjentServers\ExternalMailServerBundle\Helper\Sync;
use AjentApps\MailServerManagerBundle\Entity\CronLog;

use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\misc\CronHelper;

class SyncExtCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		parent::configure();

		$this
			->setName('syncAll');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if (!CronHelper::lock())
		{
			$cronLog = new CronLog();
			$cronLog->server_id = 2;
			$cronLog->log_entry = "--- OVERLAPPING CRON DETECTED...";
			$cronLog->create();
			return true;
		} 


		$db = MongoDb::getDatabase();
		$q = MongoDb::modelQuery($db->email_external_accounts->find(),
			"Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount");

		$cronLog = new CronLog();
		$cronLog->server_id = 2;
		$cronLog->log_entry = "+++ Starting SyncAll Cron...";
		$cronLog->create();

		foreach ($q as $extAct)
		{
    		Sync::syncUser($extAct);
		}

		$cronLog = new CronLog();
		$cronLog->server_id = 2;
		$cronLog->log_entry = "--- Stopping SyncAll Cron...";
		$cronLog->create();

		CronHelper::unlock();
	}
}
