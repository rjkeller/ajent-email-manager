<?php
namespace AjentApps\AppStoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Ajent\Mail\MailBundle\Entity\Category;

class IndexController extends RequireLoginController
{
	/**
	 * @Route("/app-store", name="AppStoreBundleAppStore")
	 */
	public function indexAction()
	{
		$template_vars = array();

		$template_vars['apps'] = Database::modelQuery("
			SELECT
				*
			FROM
				app_store_apps
			WHERE
				is_enabled = TRUE
			",
			"AjentApps\AppStoreBundle\Entity\App");

		return $this->render('AppStoreBundle:pages:index.twig.html',
			$template_vars);
	}
}
