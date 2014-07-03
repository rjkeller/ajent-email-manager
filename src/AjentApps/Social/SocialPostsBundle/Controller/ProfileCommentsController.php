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
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ProfileCommentsController extends RequireLoginController
{
	/**
	 * @Route("/profile/{profile_id}/wall-posts/{wall_post_id}", 
	 *		name="SocialBundleProfileComments")
	 */
	public function indexAction($profile_id, $wall_post_id)
	{
		$template_vars = array();

		$db = MongoDb::getDatabase();
		$template_vars['comments'] = MongoDb::modelQuery(
			$db->social_wall_comments->find(array(
				"wall_message_id" => $wall_post_id))
				->sort(array("date" => 1)),
			"AjentApps\Social\SocialPostsBundle\Entity\Comment"); 

    	return $this->render("SocialBundle:pages:Comments.twig.html",
	    	$template_vars);
	}
}
