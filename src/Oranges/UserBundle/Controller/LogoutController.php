<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\gui\MessageBoxHandler;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;

class LogoutController extends Controller
{
	public function indexAction()
	{
		$sessionManager = $this->get("Oranges.UserBundle.SessionManager");
		$sessionManager->logout();

		header("Location: /");
		die();

		return $this->render("UserBundle:pages:Logout.twig.html",
			array());
	}
}
