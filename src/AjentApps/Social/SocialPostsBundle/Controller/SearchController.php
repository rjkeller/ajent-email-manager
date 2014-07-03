<?php
namespace AjentApps\Social\SocialPostsBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\forms\FormUtility;

use AjentApps\Social\SocialPostsBundle\Form\EditProfileForm;
use AjentApps\Social\SocialPostsBundle\Form\ResetPasswordForm;
use AjentApps\Social\SocialPostsBundle\Form\ProfilePicForm;
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Form\EditCategoryForm;
use Ajent\Mail\MailBundle\Form\DeleteCategoryForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchController extends RequireLoginController
{
	/**
	 * @Route("/search/friends", name="SocialBundleFriends")
	 */
	public function friendsAction()
	{
		$profile = new UserProfile();
		$profile->loadUser();

    	return $this->render("SocialBundle:pages:SearchFriends.twig.html",
	    	array("profile" => $profile));
	}
}
