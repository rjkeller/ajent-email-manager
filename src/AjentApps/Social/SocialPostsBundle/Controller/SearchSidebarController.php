<?php
namespace AjentApps\Social\SocialPostsBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\SearchBundle\Helper\SearchResults;
use Oranges\forms\FormUtility;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

use AjentApps\Social\SocialPostsBundle\Query\FriendsSpec;
use AjentApps\Social\SocialPostsBundle\Query\FavoritesSpec;
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;
use AjentApps\Social\SocialPostsBundle\Entity\Friend;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchSidebarController extends RequireLoginController
{
	/**
	 * @Route("/social-search", name="SocialBundleSearch")
	 */
	public function indexAction($defaultSearchType, UserProfile $profile)
	{
		$template_vars = array();
		$searchType = "";

		$isRunningQuery = isset($_GET['q']);

		if ($isRunningQuery)
			$searchType = $_GET['type'];
		else
			$searchType = $defaultSearchType;

		$_GET['type'] = $defaultSearchType;

		$spec = null;
		switch ($searchType)
		{
		case 'friends':
			$spec = new FriendsSpec();
			$spec->onlyMyFriends($profile);
			break;

		case 'favorites':
			$spec = new FavoritesSpec();
			break;
		}
        $template_vars['spec'] = $spec;

/*
		if (isset($_POST['profile_id']))
		{
			$profile = new UserProfile();
			$profile->load($_POST['profile_id']);

			$friend = new Friend();
			$friend->user_id = SessionManager::$user->id;
			$friend->friend_user_id = $profile->user_id;
			$friend->create();

			MessageBoxHandler::happy("You are now following this user!.",
				"Success!");
		}
*/
		$template_vars['isRunningQuery'] = $isRunningQuery;

    	return $this->render("SocialBundle:pages:SearchSidebar.twig.html",
	    	$template_vars);
	}
}
