<?php
namespace Oranges\UserBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Authentication\Token\UsernamePasswordToken;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MasterContainer;

class RequireLoginController extends Controller
{
	public function __construct()
	{
		//if a logout command has been issued (can be done from any page), then
		//log the user out and redirect.
		$sm = MasterContainer::get("Oranges.UserBundle.SessionManager");
		if (isset($_GET['logout']) && $_GET['logout'] == 1)
		{
			$sm->logout();
			header("Location: /");
			die();
		}

		if (!SessionManager::$logged_in)
		{
			if (isset($_SERVER['REQUEST_URI']))
				header("Location: /login?redir=". $_SERVER['REQUEST_URI']);
			else
				header("Location: /login");
			die();
		}
		//if this user is logged in, but their account is disabled, then show
		//them the disabled page.
		if (SessionManager::$permissions->is_active == false)
		{
			header("Location: /login/inactive");
			die();
		}
	}
}
