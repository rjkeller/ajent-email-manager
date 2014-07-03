<?php
namespace Ajent\Mail\MailBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Entity\Vendor;

use AjentApps\Social\SocialPostsBundle\Entity\WallPost;
use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;
use AjentApps\Social\SocialPostsBundle\Entity\FavoriteSite;

class MakeEmailPublicForm extends WgForm
{
	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "ID must not be blank")
	*/
	public $id;

	/** @Assert\Type("string") */
	public $message;

	public function getName()
	{
		return "MakeEmailPublic";
	}

	public function submitForm()
	{
		$message = new EmailMessage();
		$message->load($this->id);
		$message->is_public = true;
		$message->save();

		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);

		$wallPost = new WallPost();
		$wallPost->user_id = SessionManager::$user->id;
		$wallPost->type = "email";
		$wallPost->url = MasterContainer::get("router")->generate(
			'MailBundleGetMessage', array('message_id' => $message->_id));

		$publicMessage = $this->message;
		$publicMessage = str_replace("\n", "<br>", $publicMessage);

		$wallPost->message = SessionManager::$user->username ." has posted a public email:<br><br>". $publicMessage;
		$wallPost->create();

		MessageBoxHandler::happy(
			"This message has been successfully made public.",
			"Success!");
		
	}
}