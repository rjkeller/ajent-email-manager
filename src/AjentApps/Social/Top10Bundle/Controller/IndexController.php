<?php
namespace AjentApps\Social\Top10Bundle\Controller;

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

use AjentApps\Social\Top10Bundle\Form\DeleteItemForm;
use AjentApps\Social\Top10Bundle\Form\SetItemForm;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class IndexController extends RequireLoginController
{
	private static $existingVendors = array();

	/**
	 * @Route("/landing", name="SocialBundleLanding")
	 */
	public function indexAction()
	{
		new ProfilePicForm();

		new DeleteItemForm();
		new SetItemForm();


		$db = MongoDb::getDatabase();
		self::$existingVendors = MongoDb::modelQuery(
			$db->vendors->find(array(
				"is_invisible" => false,
				"user_id" => SessionManager::$user->id
				))
				->sort(array(
					'num_new_messages' => -1,
					'num_messages' => -1
				)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor")
				->getArray();


		$profile = new UserProfile();
		$profile->loadUser();

		$template_vars = array("profile" => $profile);

		$template_vars['num_followers'] = $db->social_followers->find(array(
			"friend_user_id" => SessionManager::$user->id
			))->count();


		$template_vars['categories'] = MongoDb::modelQuery($db->email_categories->find(array(
				"user_id" => SessionManager::$user->id)),
			"Ajent\Mail\MailBundle\Entity\Category");

    	return $this->render("Top10Bundle:pages:index.twig.html",
	    	$template_vars);
	}

	public function printCategoryListAction(Category $category)
	{
		$db = MongoDb::getDatabase();

		$template_vars = array();
		$template_vars['category'] = $category;
		$template_vars['vendors'] = self::$existingVendors;
		$template_vars['listItems'] = MongoDb::modelQuery(
			$db->list_items->find(array(
				"category_id" => $category->id))
				->sort(array(
					'slot_num' => 1
				)),
			"AjentApps\Social\Top10Bundle\Entity\ListItem");

    	return $this->render("Top10Bundle:pages:LandingVendors.twig.html",
	    	$template_vars);
	}
}
