<?php
namespace AjentWidgets\InviteAFriendBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use AjentWidgets\InviteAFriendBundle\Form\InviteAFriendForm;


class IndexController extends RequireLoginController
{
	public function indexAction()
	{
        new InviteAFriendForm();

		$template_vars = array();
		return $this->render('InviteAFriendBundle:shadowbox:Welcome.twig.html',
			$template_vars);
	}
}

