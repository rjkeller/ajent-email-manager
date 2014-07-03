<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\MasterContainer;

use Oranges\forms\WgForm;
use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Contact;
use Oranges\misc\WgTextTools;
use Oranges\framework\BuildOptions;

use Symfony\Component\Validator\Constraints as Assert;

use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\EmailDomain;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;
use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\AlertBundle\Entity\Alert;

/**
 This form processes a new user registration.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class RegisterForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a username")
     * @Assert\MinLength(limit = 3, message = "Please enter a longer username")
     * @Assert\Regex(pattern = "/^[\w\d]+/", message = "Please enter a username with only letters and numbers")
     */
	public $username;
	
    /**
     * @Assert\NotBlank(message = "Please enter a password for your account")
     * @Assert\MinLength(limit = 5, message = "Please enter a password at least 5 characters long")
     */
	public $password;

	/** @Assert\Type("string") */
	public $password_confirm;
	
    /**
     * @Assert\NotBlank(message = "Please enter a First Name")
     */
	public $first_name;

    /**
     * @Assert\NotBlank(message = "Please enter a Last Name")
     */
	public $last_name;

	/**
	 * @Assert\Email(
	 *	   message = "The email '{{ value }}' is not a valid email.",
	 *	   checkMX = false
	 * )
	 */
	public $old_email_address;

	/** @Assert\Type("string") */
	public $old_email_username;
	
	/** @Assert\Type("string") */
	public $old_email_password;

	/** @Assert\Type("string") */
	public $old_email_mail_server;	

	/**
	 @Assert\Type(type = "bool", message = "Please accept the Ajent Terms of Service")
	 @Assert\NotBlank(message = "Please accept the Ajent Terms of Service")
	*/
    public $accept_license_agreement;

    /**
     * @Assert\True(message = "This username is already taken")
     */
	public function isUsernameUnique()
	{
		$this->username = strtolower($this->username);
		$q = Database::scalarQuery("
			SELECT
				COUNT(*)
			FROM
				users
			WHERE
				username = '". $this->username ."'
		");
		return $q <= 0;
	}

    /**
     * @Assert\True(message = "The 2 passwords you entered do not match.")
     */
	public function isPasswordConfirmIdentical()
	{
		return $this->password_confirm == $this->password;
	}

    /**
     * @Assert\True(message = "The username/password of your existing email account is invalid.")
     */
    public function isExternalAccountValid()
    {
//		if ($this->old_email_account_type == "None")
//		    return true;

        $acct = $this->generateExternalAccount();
        return $acct->canConnect();
    }

	public function getName()
	{
		return "registration_form";
	}

	public function submitForm()
	{
		header($this->submitFormNoRedir());
		die();
	}

	public function submitFormNoRedir()
	{
		$user = new User();
		$user->username = $this->username;
		$user->password = WgTextTools::hash($this->password, $user->username);
		$user->email = $user->username . '@'. BuildOptions::$get['MailBundle']['DefaultEmailDomain'];
		$user->role = "Customer";

		$contact = new Contact;
		$contact->first_name = $this->first_name;

		if (isset($_SERVER['REMOTE_ADDR']))
			$contact->ip_address = $_SERVER['REMOTE_ADDR'];

		$contact->create();

		$user->register($contact, $this->password);

		$domain = new EmailDomain();
		$domain->loadDomain(BuildOptions::$get['MailBundle']['DefaultEmailDomain']);

		$emailAccount = new EmailAccount();
		$emailAccount->user_id = $user->id;
		$emailAccount->domain_id = $domain->id;
		$emailAccount->username = $user->username;
		$emailAccount->domain = $domain->domain;
		$emailAccount->password = $this->username;
		$emailAccount->create();

		$profile = new UserProfile();
		$profile->user_id = $user->id;
		$profile->create();

		$c = new Category();
		$c->createCategory(BuildOptions::$get['MailBundle']['InboxName']);
		$c->createCategory("Blogs");
		$c->createCategory("Fashion");
		$c->createCategory("Sports");
		$c->createCategory("Shopping");
		$c->createCategory("Food");
		$c->createCategory("Home");
		$c->createCategory("Travel");
		$c->createCategory("Daily Deals");
		$c->createCategory("Receipts");
		$c->createCategory("Social Networking");
		$c->createCategory("Health");

		//create the welcome email in their account.
		$c->loadGeneralCategory();

		$vendor = new Vendor();
		$vendor->email_suffix = "ajent.com";
		$vendor->name = "Ajent.com";
		$vendor->category_id = $c->id;
		$vendor->user_id = $user->id;
		$vendor->create();

		$welcomeEmail = new EmailMessage();
		$welcomeEmail->type = "welcome";
		$welcomeEmail->type_id = "welcome";
		$welcomeEmail->recipient_user_id = $user->id;
		$welcomeEmail->from_address = "do-not-reply@ajent.com";
		$welcomeEmail->from_name = BuildOptions::$get['from_name'];
		$welcomeEmail->from_email = BuildOptions::$get['from_email'];
		$welcomeEmail->to_address = $user->username . "@ajent.com";
		$welcomeEmail->subject = "Welcome to Ajent!";
		$welcomeEmail->folder = "inbox";
		$welcomeEmail->category_id = $c->id;
		$welcomeEmail->vendor_id = $vendor->id;
		$welcomeEmail->body_type = "text/html";
		$welcomeEmail->create();

		$alert = new Alert();
		$alert->user_id = $user->id;
		$alert->message_id = $welcomeEmail->id;
		$alert->type = "Expiration";
		$alert->message = "Welcome to Ajent!";

		$date = new \DateTime("now",
					new \DateTimeZone("GMT"));
		$date->modify("+2 weeks");

		$alert->expiration_date = $date->getTimestamp();
		$alert->create();

		$sessionManager = MasterContainer::get("Oranges.UserBundle.SessionManager");
		$sessionManager->login($user->username, $this->password);

		$vars = array(
			"first_name" => "Roger",
			"username" => "rjkeller");

		//---------- sending Welcome email-------------//
	/*
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
*/


		//XXX: should we re-add this?
//		if ($this->old_email_account_type != "None")
//		{
			$externalAccount = $this->generateExternalAccount();
    		$externalAccount->user_id = $user->id;
    		$externalAccount->email_account_id = $emailAccount->id;
			$externalAccount->create();
			return "Location: /sign-up/import_mail";
//		}

		return "Location: /?welcome=1";
	}

	private function generateExternalAccount()
	{
		$email = explode("@", $this->old_email_address);

		$externalAccount = new ExternalAccount();
		if ($this->endsWith($this->old_email_address, "gmail.com"))
		{
			$externalAccount->type = "Gmail";
			$externalAccount->username = $email[0];
		}
		else if ($this->endsWith($this->old_email_address, "yahoo.com"))
		{
			$externalAccount->type = "Yahoo";
			$externalAccount->username = $email[0];
		}
		else if ($this->endsWith($this->old_email_address, "aol.com"))
		{
			$externalAccount->type = "AOL";
			$externalAccount->username = $email[0];
		}
		else
		{
			$externalAccount->type = "IMAP";
			$externalAccount->server = $this->old_email_mail_server;
			$externalAccount->username = $this->old_email_username;
		}

		$externalAccount->password = $this->old_email_password;
//		$externalAccount->allow_personal_emails = $this->allow_personal_emails;
		return $externalAccount;
	}

	private function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		$start	= $length * -1; //negative
		return (substr($haystack, $start) === $needle);
	}
}
