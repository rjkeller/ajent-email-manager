<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\UserBundle\Helper\SessionManager;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

use AjentApps\Ajent\MailRegistrationBundle\Form\RegisterForm;
use AjentApps\Ajent\MailRegistrationBundle\Form\CheckInviteCodeForm;
use AjentApps\Ajent\MailRegistrationBundle\Form\RequestBetaInviteForm;

class RegisterController extends Controller
{
	public function indexAction()
	{
		$template_vars = array();

		$oldCid = isset($_POST['cid']) ? $_POST['cid'] : "";
		if (isset($_POST['invite_code']))
			$_POST['cid'] = "CheckInviteCode";

		if (!isset(BuildOptions::$get['disableBetaInviteSystem']))
		{
			$betaInviteForm = new CheckInviteCodeForm();
			$isInviteCodeValid = !$betaInviteForm->hasErrors && $betaInviteForm->isSubmitted;

			$template_vars['isInviteCodeValid'] = $isInviteCodeValid;

			if ($isInviteCodeValid)
				$template_vars['invite_code'] = $_POST['invite_code'];
			else
				$template_vars['invite_code'] = "";
		}
		else
		//if the invite code system is disabled
		{
			$isInviteCodeValid = true;
		}

		$_POST['cid'] = $oldCid;
		if ($isInviteCodeValid)
		{
			$formdata = new RegisterForm();
			$form = $this->createFormBuilder($formdata)
				->add("username")
				->add("password", "password")
				->add("password_confirm", "password")
				->add("first_name")
				->add("last_name")
				->add("old_email_address")
				->add("old_email_username")
				->add("old_email_password", "password")
				->add('accept_license_agreement', 'checkbox', array(
				    'required' => true))
				->add("old_email_mail_server")
				->getForm();
			$template_vars['form'] = $form->createView();
		}
		else
		{
		    new RequestBetaInviteForm();
		}

		$template_vars['company_name'] = BuildOptions::$get['company_name_short'];




		return $this->render("MailRegistrationBundle:pages:Register.twig.html",
			$template_vars);
	}
}
