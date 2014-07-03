<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\SocialPostsBundle\Entity\WallPost;

use Oranges\MongoDbBundle\Helper\FileUploadManager;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class WallPostForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a comment")
     */
	public $message;

	/** @Assert\Type("string") */
	public $url;

	/**
	 @Assert\Type("string")
	 @Assert\NotBlank(message="Please specify a category")
	*/
	public $category;

	public function __construct()
	{
		parent::__construct();
	}

	public function getName()
	{
		return "wall_post_form";
	}

	public function submitForm()
	{
		$wallMessage = new WallPost();
		$wallMessage->user_id = SessionManager::$user->id;
		$wallMessage->message = $this->message;
		if ($this->category != "")
			$wallMessage->category_id = $this->category;

		if (!empty($this->url) && $this->url != "http://")
			$wallMessage->url = $this->url;

		if (is_uploaded_file($_FILES['post_pic']['tmp_name']))
		{
			$metadata = array();

			FileUploadManager::resizeImageUpload("post_pic", 155, 155);

			$file_id = FileUploadManager::storeUpload("post_pic",
				"SocialBundleWallPostPic",
				$metadata);
			$wallMessage->picture_id = $file_id;
		}

		$wallMessage->create();
	}
}
