<?php
namespace Ajent\Mail\MailBundle\Controller;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Pixonite\TagCloudBundle\Helper\TagCloudPrinter;

/**
 * Shows a cool tag cloud for email messages.
 */
class TagCloudController extends RequireLoginController
{
	public function indexAction()
	{
		$printer = new TagCloudPrinter($this->get('router'));
		$printer->min_font_size = 10;
		$printer->max_font_size = 72;

		$lowestNumEntries = BuildOptions::$get['TagCloudBundle']['lowestNumEntries'];
		$highestNumEntries = BuildOptions::$get['TagCloudBundle']['highestNumEntries'];

		$printer->readTagsFromTable("tags WHERE user_id = '". SessionManager::$user->id ."' AND num <= ". $highestNumEntries ." AND num > ". $lowestNumEntries);

		$template_vars = array("tags" => $printer,
			"company_name" => BuildOptions::$get['company_name_short']
		);
		$template_vars['leftNavTab'] = "TagCloud";


		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$template_vars['name'] = $contact->first_name;
		$template_vars['email_address'] = SessionManager::$user->username . "@". BuildOptions::$get['MailBundle']['DefaultEmailDomain'];

		return $this->render('MailBundle:pages:TagCloud.twig.html',
			$template_vars);
	}
}
