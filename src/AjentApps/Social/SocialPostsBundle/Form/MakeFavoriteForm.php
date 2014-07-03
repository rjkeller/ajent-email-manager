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

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class MakeFavoriteForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $id;

	public function getName()
	{
		return "MakeFavorite";
	}

	public function submitForm()
	{
		$wallPost = new WallPost();
		$wallPost->load($this->id);
		$wallPost->is_favorite = true;
		$wallPost->save();
	}
}
