<?php
namespace Ajent\Mail\MailBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Ajent\Mail\MailBundle\Query\EmailSearch;
use Ajent\Mail\MailBundle\Query\EmailSpec;

class ShowMessagesController extends RequireLoginController
{
	private function indexAction($folderName, $spec)
	{
        $search = new EmailSearch($spec);
        $template_vars['messages'] = $search->getSqlQuery();
        $template_vars['searchResults'] = $search;
		$template_vars['leftNavTab'] = $folderName;

		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$template_vars['name'] = $contact->first_name;
		$template_vars['email_address'] = SessionManager::$user->username . "@". BuildOptions::$get['MailBundle']['DefaultEmailDomain'];
		$template_vars['folder'] = $folder;
		$template_vars['uc_folder'] = $folderName;

    	return $this->render("MailBundle:components:ShowMessages.twig.html",
	    	$template_vars);		
	}
}
