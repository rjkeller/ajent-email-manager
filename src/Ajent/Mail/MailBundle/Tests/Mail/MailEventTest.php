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

		$externalAccount = new ExternalAccount();
		$externalAccount->loadUser();

		//create some email messages.
		$email = new EmailMessage();
		$email->is_invisible = $hideMe;
        $email->recipient_user_id = SessionManager::$user->id;
		$email->from_address = "TeamAjentTestVendor <test@teamajenttest.com>";

		$email->to_address = "rjkellerTEST@ajent.com";
	    $email->subject = "Test Email!"
		//making the assumption here that the PHP timezone is set to GMT in
		//app/AppKernel.php.
		$email->date = mktime();
		$email->create();

//		$metadata = array();
//		$id = $grid->storeFile($html_file, $metadata, array("safe" => true));

//		$email->body_file_id = $id->__toString();
//		$email->body_type = $type;

		//check the vendor categories to make sure they're correct.
		$files = MongoDb::modelQuery($db->vendor_categories->find(
			array('user_id' => SessionManager::$user->id)
			),
			"Ajent\Vendor\VendorBundle\Entity\Vendor");

		print_r($files);

//		$this->assertTrue(sizeof($q) == 3);
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
