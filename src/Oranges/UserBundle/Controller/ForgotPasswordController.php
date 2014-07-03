<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Authentication\Token\UsernamePasswordToken;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\ForgotPasswordRequest;
use Oranges\UserBundle\Entity\User;

use Oranges\gui\MessageBoxHandler;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\errorHandling\ForceError;
use Oranges\misc\WgTextTools;

use Oranges\framework\BuildOptions;
use Oranges\sql\Database;

use ReflectionClass, Doctrine\Common\Annotations\Parser;

class ForgotPasswordController extends Controller
{
	public function indexAction()
	{
		if (SessionManager::$logged_in)
		{
			$response = $this->createResponse();
			$response->headers->set('Location', "/");
			return $response;
		}

        $template_vars = array("errors" => "");
		//if a login command has been issued (can be done from any page as long as the
		//user is not logged in), then log the person in
		if (isset($_POST['forgot']) && $_POST['forgot'] == 1)
		{
		    ForceError::$inst->checkStr($_POST['username']);
		    ForceError::$inst->checkEmail($_POST['email']);
            $userid = Database::scalarQuery("
                SELECT
                    id
                FROM
                    users
                WHERE
                    username = '". $_POST['username'] ."' AND
                    email = '". $_POST['email'] ."'
            ");
            if (!isset($userid))
            {
                $template_vars['errors'] = array(
                    "title" => "Invalid username or email",
                    "message" => "The username or the email address you entered is invalid."
                );
            }
            else
            {
                $request = new ForgotPasswordRequest();
                $request->user_id = $userid;
                $request->create();

                $email_vars = array(
                    'user' => SessionManager::$user,
                    'request' => $request,
                    'company_name' => BuildOptions::$get['company_name_short'],
                    'help_email' => BuildOptions::$get['help_email']
                );

                $mailer = $this->get('mailer');
                $message = \Swift_Message::newInstance()
                    ->setSubject($email_vars['company_name']. ' Reset Password')
                    ->setFrom(BuildOptions::$get['from_email'])
                    ->setTo($_POST['email'])
                    ->setBody(
                        $this->renderView(
                            'UserBundle:emails:ForgotPassword.twig.txt',
                             $email_vars))
                ;

                $mailer->send($message);
                $template_vars['successes'] = array(
                    "title" => "Email Sent!",
                    "message" => "Your password reset request has been emailed to you."
                );

            }
		}

		return $this->render("UserBundle:pages:ForgotPassword.twig.html",
			$template_vars);
	}

	public function resetAction($request_id)
	{
        $template_vars = array("errors" => "");
		//if a login command has been issued (can be done from any page as long as the
		//user is not logged in), then log the person in
		if (isset($_POST['forgot']) && $_POST['forgot'] == 1)
		{
		    ForceError::$inst->checkStr($_POST['username']);
		    ForceError::$inst->checkEmail($_POST['email']);
		    ForceError::$inst->checkStr($_POST['new_password']);
		    ForceError::$inst->checkStr($_POST['new_password_confirm']);

            $userid = Database::scalarQuery("
                SELECT
                    id
                FROM
                    users
                WHERE
                    username = '". $_POST['username'] ."' AND
                    email = '". $_POST['email'] ."'
            ");
            if (!isset($userid) || $_POST['new_password'] != $_POST['new_password_confirm'])
            {
                $template_vars['errors'] = array(
                    "title" => "Invalid information entered",
                    "message" => "The information you entered is invalid."
                );
            }
            else
            {
				$user = new User();
				$user->load($userid);
				$user->password = WgTextTools::hash($_POST['new_password'], $user->username);
				$user->save();

                $template_vars['successes'] = array(
                    "title" => "Success!",
                    "message" => "Your password has been successfully reset."
                );
			}
		}

		return $this->render("UserBundle:pages:ForgotPasswordReset.twig.html",
			$template_vars);
	}

}
