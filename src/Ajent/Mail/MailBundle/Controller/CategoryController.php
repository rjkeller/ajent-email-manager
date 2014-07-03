<?php
namespace Ajent\Mail\MailBundle\Controller;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Helper\BundleManager;

use Ajent\Mail\MailBundle\Form\AddCategoryForm;
use Ajent\Mail\MailBundle\Form\EditCategoryForm;
use Ajent\Mail\MailBundle\Form\DeleteCategoryForm;

class CategoryController extends RequireLoginController
{
	public function indexAction()
	{
        new EditCategoryForm();
        new DeleteCategoryForm();
        new AddCategoryForm();

		$db = MongoDb::getDatabase();
		$categories = MongoDb::modelQuery($db->email_categories->find(array(
				"user_id" => SessionManager::$user->id))
				->sort(array("num_new_messages" => -1,
					"num_messages" => -1))
				->limit(7),
			"Ajent\Mail\MailBundle\Entity\Category");
		

		$template_vars['categories'] = $categories;
		$template_vars['company_name'] = BuildOptions::$get['company_name_short'];
		$template_vars['print_more_button'] = $categories->count() >= 6;

		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$template_vars['name'] = $contact->first_name;
		$template_vars['email_address'] = SessionManager::$user->username . "@". BuildOptions::$get['MailBundle']['DefaultEmailDomain'];

		return $this->render('MailBundle:pages:Categories.twig.html',
			$template_vars);
	}


	public function messageCategoryMenuAction($message_id)
	{
		$message = new EmailMessage();
		$message->load($message_id);

		$db = MongoDb::getDatabase();
		$categories = MongoDb::modelQuery($db->email_categories->find(array(
				"user_id" => SessionManager::$user->id)),
			"Ajent\Mail\MailBundle\Entity\Category");

		$template_vars['categories'] = $categories;
		$template_vars['message'] = $message;

		return $this->render('MailBundle:pages:ViewMessageCategories.twig.html',
			$template_vars);
	}
}
