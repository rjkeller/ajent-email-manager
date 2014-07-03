<?php
namespace AjentApps\MailServerManagerBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\sql\Database;

use AjentApps\MailServerManagerBundle\Entity\ServerActivator;
use AjentApps\MailServerManagerBundle\Entity\Server;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ServerManagerController extends RequireLoginController
{
	/**
	 * @Route("/server-manager", name="MailServerManager2")
	 */
	public function indexAction()
	{
		$db = MongoDb::getDatabase();

		$template_vars = array();
		$template_vars['servers'] = MongoDb::modelQuery(
			$db->servers->find(),
			"AjentApps\MailServerManagerBundle\Entity\Server");


		$template_vars['postfix_logs'] = MongoDb::modelQuery(
			$db->postfix_log->find(array(
				"server_id" => 1
				))
				->sort(array(
					'date' => -1
				)),
			"AjentApps\MailServerBundle\Entity\PostfixLog");

		$template_vars['cron_logs'] = MongoDb::modelQuery(
			$db->cron_logs->find(array(
				"server_id" => 1
				))
				->sort(array(
					'date' => -1
				)),
			"AjentApps\MailServerBundle\Entity\CronLog");

		$activator = new ServerActivator();
		$activator->loadServer(1);
		$template_vars['server_settings'] = $activator;

		return $this->render("MailServerManagerBundle:pages:ServerManager.twig.html",
			$template_vars);
	}

	/**
	 * @Route("/server-manager/{server_name}", name="MailServerManagerViewServer")
	 */
	public function viewServerAction($server_name)
	{
		$db = MongoDb::getDatabase();

		$template_vars = array();
		$server = new Server();
		$server->loadName($server_name);
		$template_vars['server'] = $server;

		return $this->render("MailServerManagerBundle:pages:ViewServer.twig.html",
			$template_vars);
		
	}
}
