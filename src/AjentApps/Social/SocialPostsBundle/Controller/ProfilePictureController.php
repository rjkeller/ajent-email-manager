<?php
namespace AjentApps\Social\SocialPostsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Oranges\UserBundle\Helper\SessionManager;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

class ProfilePictureController extends Controller
{
	public function indexAction($user_id)
	{
		$profile = new UserProfile();
		$profile->loadUser($user_id);

		if (!isset($profile->picture_id))
			return new Response("/bundles/social/images/placeholder.png");

		$router = $this->get("router");
		$url = $router->generate(
			'SocialBundleViewPicture',
			array('picture_id' => $profile->picture_id->__toString() ));

		return new Response($url);
	}
}
