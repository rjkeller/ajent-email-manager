<?php
namespace Oranges\LoggingBundle\Controller;

use Oranges\user\Helper\User;
use Oranges\LoggingBundle\Query\AuditLogSpec;
use Oranges\frontend\WgSmarty;
use Oranges\SearchBundle\Helper\SearchEngine;

use Oranges\UserBundle\Helper\RequireLoginController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AdminController extends RequireLoginController
{
	/**
	 * @Route("/logging", name="LoggingLanding")
	 */
	public function indexAction()
	{
		$template_vars = array();

		$engine = new SearchEngine();
		$engine->loadGetParameters();

		$spec = new AuditLogSpec();
		if (isset($_GET['json']))
			$spec->loadJson($_GET['json']);

		$engine->init($spec);
		$template_vars['searchResults'] = $engine;
		$template_vars['results'] = $engine->getSqlQuery();

		return $this->render("LoggingBundle:pages:Admin.twig.html",
			$template_vars);
	}
}
