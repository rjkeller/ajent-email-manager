<?php
namespace Ajent\AlertBundle\Controller;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\AlertBundle\Entity\Alert;

/**
 * This is a widget that shows an email alert when the user should be notified.
 * For example, if an offer that is tagged expires, then the alert will likely
 * be placed in the queue to be shown to the user here.
 */
class AlertsController extends RequireLoginController
{
	public function indexAction()
	{
		$db = MongoDb::getDatabase();
		$alerts = MongoDb::modelQuery($db->email_alerts->find(array(
				"expiration_date" => array('$gt' => time()),
				"is_invisible" => false,
				"user_id" => SessionManager::$user->id))
				->sort(array("expiration_date" => -1)),
			"Ajent\AlertBundle\Entity\Alert");

		$template_vars['alerts'] = $alerts;

		//check if any Alerts have been triggered.
		$alertMe = MongoDb::modelQuery($db->email_alerts->find(array(
				"expiration_date" => array('$lt' => time()),
				"is_invisible" => false,
				"is_triggered" => array('$ne' => true),
				"user_id" => SessionManager::$user->id))
				->sort(array("expiration_date" => 1)),
			"Ajent\AlertBundle\Entity\Alert");

		//if someone is returned, then fetch the first alert and display it.
		$template_vars['showAlertNotification'] = $alertMe->count() > 0;
		if ($template_vars['showAlertNotification'])
		{
			$notificationAlert = null;
			foreach ($alertMe as $key => $a)
			{
				$notificationAlert = $a;
			}
			$template_vars['notificationAlert'] = $notificationAlert;

			$notificationAlert->is_triggered = true;
			$notificationAlert->save();
		}

		return $this->render('AlertBundle:widgets:Alerts.twig.html',
			$template_vars);
	}

	public function RemindMeLaterAction($alert_id)
	{
		$alert = new Alert();
		$alert->load($alert_id);
		$alert->remindMeLater();

		header("Location: /");
		die();

	}
}
