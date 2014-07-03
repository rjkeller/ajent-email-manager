<?php
namespace Ajent\Mail\MailBundle\Tests\Mail;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;

use AjentApps\Ajent\MailRegistrationBundle\Form\RegisterForm;
use Oranges\MasterContainer;
use Oranges\sql\Database;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\UserBundle\Helper\SessionManager;

class DemoControllerTest extends WebTestCase
{
    public function testIndex()
    {


		return true;





        $client = static::createClient();
		MasterContainer::$isTesting = true;
		MasterContainer::$container = $client->getContainer();
/*
		$this->sendVendorEmail();
		$this->fail();
*/

		$registerForm = new RegisterForm();
		$registerForm->username = "rjkellerTEST";
		$registerForm->password = "testpasswd39";
		$registerForm->password_confirm = "testpasswd39";
		$registerForm->first_name = "Roger";
		$registerForm->last_name = "Keller";
		$registerForm->old_email_username = "rjkeller";
		$registerForm->old_email_password = "";
		$registerForm->old_email_account_type = "Gmail";
		$registerForm->accept_license_agreement = true;

		if ($registerForm->hasErrors())
			$this->fail(print_r(MessageBoxHandler::getTemplateVars(), true));

		$registerForm->submitFormNoRedir();
/*
		$sessionManager = MasterContainer::get("Oranges.UserBundle.SessionManager");
		$sessionManager->sessionOnlyLogin("rjkellerTEST");
		print_r(SessionManager::$user);
*/
		$_GET['scanVendors'] = 1;
		$importMailController = new \AjentApps\Ajent\MailRegistrationBundle\Controller\EmailScanController();
		$importMailController->viewVendorsAction();
		$importMailController->viewVendorsAction();
		$_GET['scanVendors'] = 0;
		die("done!");

		$q = Database::modelQuery("
			SELECT
				*
			FROM
				email_vendors
			WHERE
				name = 'RJ, Inc.' AND
				user_id = '". SessionManager::$user->id ."'
		",
		"Ajent\Mail\MailBundle\Entity\Vendor");
		$this->assertTrue(sizeof($q) > 0);

		foreach ($q as $i)
		{
			$i->load($i->id);
			$i->pendingAddToAjent = true;
			$i->is_unsubscribed = false;
			$i->is_invisible = false;
			$i->save();

		}
		$_GET['loadEmails'] = 1;
		$importMailController->scanSuccessAction();

		$q = Database::modelQuery("
			SELECT
				*
			FROM
				email_messages
		",
		"Ajent\Mail\MailBundle\Entity\EmailMessage");
		$this->assertTrue(sizeof($q) > 0);

		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser();
		$externalAccount->sync();

		$q = Database::modelQuery("
			SELECT
				*
			FROM
				email_messages
		",
		"Ajent\Mail\MailBundle\Entity\EmailMessage");
		$this->assertTrue(sizeof($q) == 3);
    }

	private function sendVendorEmail()
	{
		mail("rjkeller@gmail.com", "This is the R.J. vendor!",
			"Welcome to RJ Inc! subscribe all rights reserved click here !",
			"From: \"RJ, Inc.\" <do-not-reply@rjkeller.net>\r\n");
/*
		$mailer = MasterContainer::get('mailer');
		$message = \Swift_Message::newInstance()
			->setSubject('This is the R.J. vendor!')
			->setFrom(array("do-not-reply@rjkeller.net" => "RJ, Inc."))
			->setTo("rjkeller@pixonite.com")
			->setBody(
				"Welcome to RJ Inc! subscribe all rights reserved click here !")
		;
		$mailer->send($message);
*/
	}
}
