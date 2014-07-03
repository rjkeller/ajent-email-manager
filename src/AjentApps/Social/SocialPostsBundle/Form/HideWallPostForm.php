<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\SocialPostsBundle\Entity\HideWallPost;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class HideWallPostForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $id;

	public function getName()
	{
		return "HideWallPost";
	}

	public function submitForm()
	{
		$wallPost = new HideWallPost();
		$wallPost->user_id = SessionManager::$user->id;
		$wallPost->wall_post_id = $this->id;
		$wallPost->is_hidden = true;
		$wallPost->create();
	}
}
