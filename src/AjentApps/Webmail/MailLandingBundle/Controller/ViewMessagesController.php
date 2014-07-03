<?php
namespace AjentApps\Webmail\MailLandingBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Oranges\misc\WgTextTools;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\IdForm;

use Ajent\Mail\MailBundle\Query\EmailSearch;
use Ajent\Mail\MailBundle\Query\EmailSpec;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Form\MakeEmailPublicForm;
use Ajent\Mail\MailBundle\Form\EmailAlertForm;
use Ajent\Mail\MailBundle\Form\EmailExpirationForm;
use Ajent\Mail\MailBundle\Form\MakeEmailFavoriteForm;
use Ajent\Mail\MailBundle\Form\ForwardEmailForm;
use Ajent\Mail\MailBundle\Form\CategoryChangeForm;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

use Oranges\MongoDbBundle\Helper\MongoDb;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ViewMessagesController extends RequireLoginController
{
	/**
	 * @Route("/mail/folder/{folder}", name="MailBundleViewFolder")
	 */
	public function viewFolderAction($folder)
	{
		return $this->showMessages(
		    "Inbox",
			"",
		    new EmailSpec($folder));
	}

	/**
	 * @Route("/mail/category/{category}", name="MailBundleViewCategory")
	 */
	public function viewCategoryAction($category)
	{
		$c = new Category();
		$c->loadCategoryName($category);

		return $this->showMessages(
			$c->name,
			$c->getCategoryClass(),
		    new EmailSpec('inbox', $c->id)
		);
	}

	/**
	 * @Route("/mail/vendor/{vendor_id}", name="VendorBundleViewCategoryVendor")
	 */
	public function viewVendorAction($vendor_id)
	{
		$v = new Vendor();
		$v->load($vendor_id);

		$category = new Category();
		$category->load($v->category_id);

		return $this->showMessages(
			$category->name,
			$category->getCategoryClass(),
			new EmailSpec("inbox", null, $v->id)
		);
	}

	/**
	 * @Route("/mail/category/{category_name}/{vendor_id}", name="VendorBundleViewCategoryVendor")
	 */
	public function viewCategoryVendorAction($vendor_id, $category_name)
	{
		$v = new Vendor();
		$v->load($vendor_id);

		$category = new Category();
		$category->loadCategoryName($category_name);

		return $this->showMessages(
			$v->name,
			$category->getCategoryClass(),
			new EmailSpec("inbox", $category->id, $v->id)
		);
		
	}

	protected function showMessages($title, $colorCode, EmailSpec $spec)
	{
        $spec->template = "SocialMailBundle:query:Messages.twig.html";

		$template_vars = array();

		$db = MongoDb::getDatabase();

		if (isset($_GET['message_id']))
		{
			$template_vars['loadMessageId'] = $_GET['message_id'];
		}

        new CategoryChangeForm();

		$emailPublicForm = new MakeEmailPublicForm();
		if ($emailPublicForm->isSubmitted)
		{
			$template_vars['loadMessageId'] = $emailPublicForm->id;
		}

		$emailExpirationForm = new EmailExpirationForm();
		if ($emailExpirationForm->isSubmitted)
		{
			$template_vars['loadMessageId'] = $emailExpirationForm->id;
		}

		$forwardMailForm = new ForwardEmailForm();
		if ($forwardMailForm->isSubmitted)
		{
			$template_vars['loadMessageId'] = $forwardMailForm->id;
		}

		$makeEmailFavoriteForm = new MakeEmailFavoriteForm();
		if ($makeEmailFavoriteForm->isSubmitted)
		{
			$template_vars['loadMessageId'] = $makeEmailFavoriteForm->id;
		}

		$DeleteAllFolderForm = new IdForm("DeleteAllFolder");
		if ($DeleteAllFolderForm->isSubmitted)
		{
			$email = new EmailMessage();
			$email->load($DeleteAllFolderForm->id);

			$q = MongoDb::modelQuery($db->email_messages
					->find(array(
						"category_id" => $email->category_id,
						"recipient_user_id" => SessionManager::$user->id))
				, "Ajent\Mail\MailBundle\Entity\EmailMessage");

			foreach ($q as $message)
			{
				$message->load($message->id);
				$message->delete();
			}

			header("Location: /");
			die();
		}

		$DeleteAllVendorForm = new IdForm("DeleteAllVendor");
		if ($DeleteAllVendorForm->isSubmitted)
		{
			$email = new \Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage();
			$email->load($DeleteAllVendorForm->id);

			$q = MongoDb::modelQuery($db->email_messages
					->find(array(
						"vendor_id" => $email->vendor_id,
						"recipient_user_id" => SessionManager::$user->id))
				, "Ajent\Mail\MailBundle\Entity\EmailMessage");

			foreach ($q as $message)
			{
				$message->load($message->id);
				$message->delete();
			}

			header("Location: /");
			die();
		}



		$template_vars['spec'] = $spec;
		$template_vars['title'] = WgTextTools::truncate($title, 20);
		$template_vars['colorCode'] = $colorCode;

		$template_vars['is_safari'] = preg_match('/Safari/i', $_SERVER['HTTP_USER_AGENT']);

    	return $this->render("SocialMailBundle:pages:ViewMessages.twig.html",
	    	$template_vars);
	}
}
