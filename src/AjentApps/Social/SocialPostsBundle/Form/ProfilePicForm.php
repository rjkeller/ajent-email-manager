<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

use Oranges\MongoDbBundle\Helper\FileUploadManager;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ProfilePicForm extends WgForm
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getName()
	{
		return "ProfilePhotoUpload";
	}

	public function submitForm()
	{
		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);

		if (isset($_FILES['post_pic']) &&
			is_uploaded_file($_FILES['post_pic']['tmp_name']))
		{
			$metadata = array();

			FileUploadManager::resizeImageUpload("post_pic", 100, 100);

			$file_id = FileUploadManager::storeUpload("post_pic",
				"SocialBundleWallPostPic",
				$metadata);
			$profile->picture_id = $file_id;
			$profile->save();
			print_r($profile);
		}

	}
}
