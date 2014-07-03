<?php
namespace Oranges\classes;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use WordGrab\framework\user\User;
use \Build_Options;

class RequireLoginController extends Controller
{
	public function __construct()
	{
		//if a logout command has been issued (can be done from any page), then
		//log the user out and redirect.
		if (isset($_GET['logout']) && $_GET['logout'] == 1)
		{
			User::logout();
			header("Location: /");
			die();
		}

		if (!User::$logged_in)
		{
			//otherwise show the login page.
			header("Location: /login");
			die();
		}
		//if this user is logged in, but their account is disabled, then show
		//them the disabled page.
		if (User::$permissions->isActive == false)
		{
			header("Location: /login/inactive");
			die();
		}

		$INIT_H__ = true;
	}
}
