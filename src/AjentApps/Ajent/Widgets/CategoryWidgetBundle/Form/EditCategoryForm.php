<?php
namespace AjentApps\Ajent\Widgets\CategoryWidgetBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\errorHandling\InternalException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Category;

class EditCategoryForm extends WgForm
{
	/** @Assert\Type("string") */
	public $id;

	/** @Assert\Type("string") */
	public $name;

	public function getName()
	{
		return "EditCategory";
	}

	public function submitForm()
	{
		$category = new Category();
		$category->load($this->id);
		if ($category->user_id != SessionManager::$user->id)
		{
			throw new InternalException("Security violation detected: Cannot edit category because Access is Denied");
		}
		$category->name = $this->name;
		$category->save();

		MessageBoxHandler::happy(
			"You have successfully changed the name of this category.",
			"Success!");		
	}
}