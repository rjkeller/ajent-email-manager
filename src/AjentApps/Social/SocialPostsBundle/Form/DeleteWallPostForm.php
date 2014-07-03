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
use AjentApps\Social\SocialPostsBundle\Entity\WallPost;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class DeleteWallPostForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $id;

	public function getName()
	{
		return "DeleteWallPost";
	}

	public function submitForm()
	{
		$wallPost = new WallPost();
		$wallPost->load($this->id);
		if (SessionManager::$user->id == $wallPost->user_id)
			$wallPost->delete();
	}
}
