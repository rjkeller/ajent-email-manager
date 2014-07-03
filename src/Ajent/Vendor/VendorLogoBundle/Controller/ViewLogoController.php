<?php
namespace Ajent\Vendor\VendorLogoBundle\Controller;

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

class LogoBundle extends RequireLoginController
{
	/**
	 * @Route("/logo/{mongo_image_id}", name="LogoBundleViewLogo")
	 */
	public function viewImageAction($mongo_image_id)
	{
		return $this->forward('MongoDbBundle:ShowFile:showImage',
			array(
				'database' => 'LogoBundleLogos',
				'id' => $mongo_image_id
			)
		);
	}
}
