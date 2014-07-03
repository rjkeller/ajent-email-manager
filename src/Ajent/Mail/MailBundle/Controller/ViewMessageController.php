<?php
namespace Ajent\Mail\MailBundle\Controller;

use Oranges\UserBundle\Helper\RequireLoginController;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;
use Oranges\MongoDbBundle\Helper\MongoDb;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;
use Oranges\FrontendBundle\Helper\MessageBoxHandler;

use Ajent\Mail\MailBundle\Query\EmailSearch;
use Ajent\Mail\MailBundle\Query\EmailSpec;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Entity\Vendor;
use Ajent\Mail\MailBundle\Entity\Bundle;

/**
 * GUI to view an email message.
 */
class ViewMessageController extends RequireLoginController
{
	public function indexAction($message_id)
	{
		return $this->showMessageAction($message_id, "ViewMessage.twig.html");
	}

	public function ajaxAction($message_id)
	{
		return $this->showMessageAction($message_id, "ViewMessageAjax.twig.html");		
	}

	private function showMessageAction($message_id, $front_end)
	{
		$template_vars = array();
		$db = MongoDb::getDatabase();

		$msg = new EmailMessage();
		$msg->load($message_id);

		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$template_vars['name'] = $contact->first_name;
		$template_vars['email_address'] = SessionManager::$user->username . "@". BuildOptions::$get['MailBundle']['DefaultEmailDomain'];

		$template_vars['message'] = $msg;

		if ($msg->type == "bundle")
		{
			$bundle = new Bundle();
			$bundle->loadMessageId($msg->id);

			$bundle_messages = array();
			$q = $db->email_bundle_messages->find(array(
					"bundle_id" => $bundle->id),
					array("message_id" => 1));

			foreach ($q as $i)
			{
				$bundle_messages[] = $i['message_id'];
			}

			$q = MongoDb::modelQuery($db->email_messages
					->find(array(
						"id" => array('$in' => $bundle_messages)))
				, "Ajent\Mail\MailBundle\Entity\EmailMessage");

			$body = "";
			foreach ($q as $message)
			{
				$body .= $message->getMessageBody();
			}
			$template_vars['messageBody'] = $body;
		}
		$template_vars['leftNavTab'] = "";

		//if this email is unread, then mark it as read.
		if (!$msg->is_read)
		{
			$msg->is_read = true;
			$msg->save();
		}

		if ($msg->type == "welcome")
		{
			return $this->forward('MailBundle:Welcome:index');
		}

		$categories = MongoDb::modelQuery($db->email_categories->find(array(
				"user_id" => SessionManager::$user->id)),
			"Ajent\Mail\MailBundle\Entity\Category");

		$template_vars['categories'] = $categories;

		return $this->render("MailBundle:pages:". $front_end,
			$template_vars);
		
	}
}
