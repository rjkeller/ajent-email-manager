<?php
namespace Ajent\Mail\MailBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;

use Oranges\sql\Database;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

class WelcomeController extends RequireLoginController
{
	public function indexAction()
	{
		$contact = new Contact();
		$contact->loadUser();

		$template_vars = array();
		$template_vars['first_name'] = $contact->first_name;
		$template_vars['username'] = SessionManager::$user->username;

		return $this->render("MailRegistrationBundle:emails:Welcome.twig.html",
			$template_vars);
	}
}
