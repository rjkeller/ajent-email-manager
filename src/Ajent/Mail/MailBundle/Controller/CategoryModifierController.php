<?php
namespace Ajent\Mail\MailBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Ajent\Mail\MailBundle\Query\EmailSearch;
use Ajent\Mail\MailBundle\Query\EmailSpec;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Entity\Vendor;

class CategoryModifierController extends RequireLoginController
{
	public function changeCategoryAction($message_id, $category_id)
	{
		$category = new Category();
		$category->load($category_id);

		$message = new EmailMessage();
		$message->load($message_id);
		$message->category_id = $category->id;
		$message->save();

		header("Location: /category/". $category->name . "?message_id=". $message->id);
		die();
	}

	public function addCategoryAction($message_id, $new_category)
	{
		$category = new Category();
		$category->user_id = SessionManager::$user->id;
		$category->name = $new_category;
		$category->create();

		$message = new EmailMessage();
		$message->load($message_id);
		$message->category_id = $category->id;
		$message->save();

		return $this->forward('MailBundle:Category:messageCategoryMenu', array(
			'message_id' => $message_id
		));
	}
}
