<?php
namespace Pixonite\UserAdminBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Entity\User;


class UserEditorController extends RequireLoginController
{
	public function indexAction($user_id)
	{
		$template_vars = array();

		$user = new User();
		$user->load($user_id);
		$template_vars['user'] = $user;

    	return $this->render("UserAdminBundle:pages:UserEditor.twig.html",
	    	$template_vars);
	}
}
