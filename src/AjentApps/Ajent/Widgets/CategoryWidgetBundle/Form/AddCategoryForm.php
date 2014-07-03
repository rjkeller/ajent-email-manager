<?php
namespace AjentApps\Ajent\Widgets\CategoryWidgetBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use AjentApps\Social\SocialPostsBundle\Entity\FavoriteSite;

class AddCategoryForm extends WgForm
{
	/** @Assert\Type("string") */
	public $name;

    /**
     * @Assert\True(message = "Sorry, you cannot create a category with this name.")
     */
	public function isNameNotEqualToReservedWords()
	{
		return strtolower($this->name) != "inbox" &&
			strtolower($this->name) != "trash";
	}

	public function getName()
	{
		return "AddCategory";
	}

	public function submitForm()
	{
		$category = new Category();
		$category->user_id = SessionManager::$user->id;
		$category->name = $this->name;
		$category->create();
	}
}
