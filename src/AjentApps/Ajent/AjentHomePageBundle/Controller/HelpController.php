<?php
namespace AjentApps\Ajent\AjentHomePageBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;

class HelpController extends RequireLoginController
{
	public function indexAction()
	{
		return $this->render("AjentBundle:pages:Help.twig.html",
			array());
	}
}
