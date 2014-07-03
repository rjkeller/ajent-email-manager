<?php
namespace AjentApps\Social\SocialPostsBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\forms\FormUtility;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

use AjentApps\Social\SocialPostsBundle\Form\WallPostForm;
use AjentApps\Social\SocialPostsBundle\Form\AddCommentForm;
use AjentApps\Social\SocialPostsBundle\Form\MakeFavoriteForm;
use AjentApps\Social\SocialPostsBundle\Form\DeleteWallPostForm;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;
use AjentApps\Social\SocialPostsBundle\Entity\WallPost;
use AjentApps\Social\SocialPostsBundle\Query\MyPostsSpec;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MyPostsController extends RequireLoginController
{
	/**
	 * @Route("/profile", name="SocialBundleMyPosts")
	 */
	public function indexAction()
	{
		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);
		return $this->viewProfileAction($profile->id);
	}

	/**
	 * @Route("/profile/{profile_id}", name="SocialBundleViewProfile")
	 */
	public function viewProfileAction($profile_id)
	{
		$template_vars = array();

		$profile = new UserProfile();
		$profile->load($profile_id);
		$template_vars['profile'] = $profile;

		$contact = new Contact();
		$contact->loadUser($profile->user_id);
		$template_vars['contact'] = $contact;

		//create some new form objects, that will automatically run the form
		//submits when these objects are created. But since we don't need the
		//form objects later, we won't store them.
		new AddCommentForm();
		if ($profile->user_id == SessionManager::$user->id)
		{
			new MakeFavoriteForm();
			new DeleteWallPostForm();
		}
		else
		{
			new HideWallPostForm();
			new UnfollowWallPostForm();
		}

		$formdata = new WallPostForm($profile->id);
		$form = $this->createFormBuilder($formdata)
						->add("url")
						->add("message")
						->getForm();
		$template_vars['form'] = $form->createView();
		$template_vars['spec'] = new MyPostsSpec($profile);

		$_GET['orderby'] = "";
    	return $this->render("SocialBundle:pages:MyPosts.twig.html",
	    	$template_vars);
	}

	/**
	 * @Route("/profile/{profile_id}/profile_pic", name="SocialBundleViewProfilePic")
	 */
	public function viewProfilePicAction($profile_id)
	{
		$profile = new UserProfile();
		$profile->load($profile_id);

		return $this->forward('MongoDbBundle:ShowFile:showImage',
			array(
				'database' => 'SocialBundleProfilePic',
				'id' => $profile->picture_id
			)
		);
	}

	/**
	 * @Route("/wall_post_pic/{post_id}", name="SocialBundleViewWallPostPic")
	 */
	public function viewWallPostPicAction($post_id)
	{
		$wallPost = new WallPost();
		$wallPost->load($post_id);

		return $this->forward('MongoDbBundle:ShowFile:showImage',
			array(
				'database' => 'SocialBundleWallPostPic',
				'id' => $wallPost->picture_id
			)
		);
	}

	/**
	 * @Route("/print_picture/{picture_id}", name="SocialBundleViewPicture")
	 */
	public function viewImageAction($picture_id)
	{
		return $this->forward('MongoDbBundle:ShowFile:showImage',
			array(
				'database' => 'SocialBundleWallPostPic',
				'id' => $picture_id
			)
		);
	}
}
