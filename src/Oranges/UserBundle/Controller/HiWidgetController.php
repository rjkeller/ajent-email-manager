<?php
namespace Oranges\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;

class HiWidgetController extends Controller
{
	public function indexAction()
	{
		$vars = array();
		$vars['is_logged_in'] = SessionManager::$logged_in;
		$vars['company_name'] = BuildOptions::$get['company_name_short'];
		if (SessionManager::$logged_in)
			$vars['user'] = SessionManager::$user;

		return $this->render("UserBundle:pages:HiWidget.twig.html", $vars);
	}
}
