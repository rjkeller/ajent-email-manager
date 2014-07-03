<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\gui\MessageBoxHandler;
use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\forms\WgForm;
use Oranges\forms\StdTypes;
use Oranges\misc\WgTextTools;

use Oranges\sql\Database;
use Oranges\sql\SqlIterator;
use Oranges\framework\BuildOptions;

use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Contact;
use Oranges\UserBundle\Helper\SessionManager;
use Ajent\AjentBundle\Form\RegisterForm;


class RegisterController extends Controller
{
	public function indexAction()
	{
		$template_vars = array();

		$formdata = new RegisterForm();
		$form = $this->createForm($formdata, $formdata);

		$template_vars['company_name'] = BuildOptions::$get['company_name_short'];

	    $request = $this->get('request');
	    if ($request->getMethod() == 'POST')
		{
	        $form->bindRequest($request);

	        if ($form->isValid()) {
	            // perform some action, such as save the object to the database
				$formdata->submitForm();

				header("Location: /?wasNewRegistration=true");
				die();
	        }

			$template_vars['errors'] = $formdata->getErrors();
	    }


		$template_vars['form'] = $form->createView();
		return $this->render("UserBundle:pages:Register.twig.html",
			$template_vars);
	}
}
