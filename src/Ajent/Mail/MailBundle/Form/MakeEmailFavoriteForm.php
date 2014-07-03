<?php
namespace Ajent\Mail\MailBundle\Form;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;
use Oranges\forms\WgForm;
use Oranges\UserBundle\Helper\SessionManager;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormBuilder;

use Ajent\Mail\MailBundle\Entity\Vendor;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use AjentApps\Social\SocialPostsBundle\Entity\FavoriteSite;
use AjentApps\Social\SocialPostsBundle\Entity\WallPost;

class MakeEmailFavoriteForm extends WgForm
{
	/** @Assert\Type("string")
	    @Assert\NotBlank(message = "ID must not be blank")
	*/
	public $id;

	/** @Assert\Type("string") */
	public $message;

	public function getName()
	{
		return "EmailMakeFavorite";
	}

	public function submitForm()
	{
		$message = new EmailMessage();
		$message->load($this->id);

		$vendor = new Vendor();
		$vendor->load($message->vendor_id);

		$wallPost = new WallPost();
		$wallPost->user_id = SessionManager::$user->id;
		$wallPost->url = "http://". $vendor->email_suffix;

		$publicMessage = $this->message;
		$publicMessage = str_replace("\n", "<br>", $publicMessage);

		$wallPost->message = SessionManager::$user->username ." has posted a favorite vendor:<br><br>". $publicMessage;
		$wallPost->is_favorite = true;
		$wallPost->create();

		MessageBoxHandler::happy(
			"This vendor has been successfully marked as your favorite.",
			"Success!");		
	}
}