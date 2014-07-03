<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Authentication\Token\UsernamePasswordToken;

use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Contact;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\forms\WgForm;
use Oranges\forms\StdTypes;
use Oranges\misc\WgTextTools;

use Oranges\framework\BuildOptions;

use ReflectionClass, Doctrine\Common\Annotations\Parser;
use Ajent\AjentBundle\Form\RegisterForm;

class emailLoginController extends Controller
{
	public function indexAction()
	{
		if (SessionManager::$logged_in)
		{
			header("Location: /");
			die();
		}

		$sessionManager = $this->get("Oranges.UserBundle.SessionManager");

		//if a login command has been issued (can be done from any page as long as the
		//user is not logged in), then log the person in
		if (isset($_POST['sublogin']) && $_POST['sublogin'] == 1)
		{
			$email = $_POST['user'];

			UserErrorHandler::$inst->checkEmail($email, false,
				new ErrorMetaData("Invalid username or password")
				);
			if (!UserErrorHandler::$inst->hasErrors)
			{
				$user = new User();
				$user->loadEmail($email);
				$_POST['user'] = $user->username;
				$sessionManager->login($user->username, $_POST['pass']);
			}

			if (!UserErrorHandler::$inst->hasErrors)
			{
				\OrangesLogger("User successfully logged in: ". $sessionManager::$user->username, "login");
				if (isset($_GET['redir']))
					header("Location: ". $_GET['redir']);
				else
					header("Location: /");
				die();
			}
		}

		$template_vars = array();
		$template_vars['isMsie6'] = strrpos($_SERVER['HTTP_USER_AGENT'], "MSIE 6");

		$template_vars['company_name'] = BuildOptions::$get['company_name_short'];


		return $this->render("UserBundle:pages:login.twig.html", $template_vars);
	}
}
