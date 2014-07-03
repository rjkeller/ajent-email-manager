<?php
namespace Ajent\Mail\MailBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Ajent\Mail\MailBundle\Form\ComposeEmailForm;

/**
 * Create an email to send to someone.
 */
class ComposeController extends RequireLoginController
{
	public function indexAction()
	{
		$ComposeEmailForm = new ComposeEmailForm();		

		return $this->render("MailBundle:pages:Compose.twig.html",
			array());
	}
}
