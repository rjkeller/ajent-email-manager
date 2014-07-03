<?php
namespace AjentApps\Ajent\AjentHomePageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oranges\UserBundle\Helper\SessionManager;

class IndexController extends Controller
{
	public function indexAction()
	{
		//if the user is logged in, switch apps to the Mail app.
		if (SessionManager::$logged_in)
		{
			return $this->forward('SocialMailBundle:Landing:index');
		}

		return $this->render("AjentHomePageBundle:pages:Index.twig.html",
			array());
	}
}
