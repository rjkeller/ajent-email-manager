<?php
namespace Pixonite\UserAdminBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;

use Pixonite\BlogBundle\Entity\Category;
use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\forms\WgForm;
use Oranges\forms\StdTypes;
use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\CheckType;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\sql\Database;
use Oranges\sql\SqlModelIterator;
use Oranges\framework\BuildOptions;

class UserManagerController extends RequireLoginController
{
	public function indexAction()
	{
		$template_vars = array();

		$template_vars['users'] = Database::modelQuery("
			SELECT
				*
			FROM
				users
		", "Oranges\UserBundle\Entity\User");


/*
		$frm = new WgForm("register");
		$frm->addField("reg_user", "Username", new StdTypes("username"));
		$frm->addField("reg_pass", "Password", new StdTypes("password"));
		$frm->addField("reg_passc", "Confirm Password", new StdTypes("password"));

		$frm->addField("reg_fname", "First Name", new StdTypes("str"), null, array("optional" => true));
		$frm->addField("reg_lname", "Last Name", new StdTypes("str"), null, array("optional" => true));
		$frm->addField("reg_email", "E-mail", new StdTypes("email"));

		if (isset($_POST['cid2']) && $_POST['cid2'] == "1")
			$frm->assert($_POST['reg_pass'] == $_POST['reg_passc'], "Your password and confirmed password do not match");

		if ($frm->validate())
		{
			$user = new User();
			$user->username = $_POST['reg_user'];
			$user->password = WgTextTools::hash($_POST['reg_pass'], $user->username);
			$user->email = $_POST['reg_email'];
			$user->role = "Administrator";
			$user->create();

			$contact = new Contact;
			$contact->user_id = $user->id;
			$contact->first_name = $_POST['reg_fname'];
			$contact->last_name = $_POST['reg_lname'];
			$contact->email = $_POST['reg_email'];
			$contact->create();

			$user->contact_id = $contact->id;
			$user->save();

			$companyName = BuildOptions::$get['company_name_short'];

			$emailVars = array(
				"username" => $user->username,
				"name" => $contact->first_name . " ". $contact->last_name,
				"company_name" => $companyName
			);

			$mailer = $this->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject('Welcome to '. $companyName .'!')
				->setFrom(BuildOptions::$get['from_email'])
				->setTo($user->email)
				->setBody(
					$this->renderView(
						'UserBundle:emails:WelcomeEmail.twig.txt',
						 $emailVars))
			;

			$mailer->send($message);
		}
		else
		{
			if (UserErrorHandler::$inst->hasErrors)
			{
				$template_vars['errors'] = array(
					"title" => "Invalid input",
					"message" => UserErrorHandler::$inst->toString()
				);
			}
		}
*/
    	return $this->render("UserAdminBundle:pages:UserManager.twig.html",
	    	$template_vars);
	}
}
