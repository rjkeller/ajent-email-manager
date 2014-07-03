<?php
namespace AjentApps\Social\SocialPostsBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\forms\FormUtility;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

use AjentApps\Social\SocialPostsBundle\Form\WallPostForm;
use AjentApps\Social\SocialPostsBundle\Form\AddCommentForm;
use AjentApps\Social\SocialPostsBundle\Form\MakeFavoriteForm;
use AjentApps\Social\SocialPostsBundle\Form\HideWallPostForm;
use AjentApps\Social\SocialPostsBundle\Form\UnfollowWallPostForm;

use AjentApps\Social\SocialPostsBundle\Query\RecentPostsSpec;
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class RecentPostsController extends RequireLoginController
{
	/**
	 * @Route("/recent-posts", name="SocialBundleRecentPosts")
	 */
	public function indexAction()
	{
		$template_vars = array();

		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);
		$template_vars['profile'] = $profile;

		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);
		$template_vars['contact'] = $contact;

		//create some new form objects, that will automatically run the form
		//submits when these objects are created. But since we don't need the
		//form objects later, we won't store them.
		new AddCommentForm();
		new MakeFavoriteForm();
		new HideWallPostForm();
		new UnfollowWallPostForm();

		$formdata = new WallPostForm($profile->id);
		$form = $this->createFormBuilder($formdata)
						->add("url")
						->add("message", "textarea")
						->getForm();
		$template_vars['form'] = $form->createView();

		
		$template_vars['spec'] = new RecentPostsSpec($profile);

		$db = MongoDb::getDatabase();
		$template_vars['categories'] = MongoDb::modelQuery(
			$db->email_categories->find(array(
				'user_id' => SessionManager::$user->id
			)),
			"Ajent\Mail\MailBundle\Entity\Category"
		);


    	return $this->render("SocialBundle:pages:RecentPosts.twig.html",
	    	$template_vars);
	}
}
