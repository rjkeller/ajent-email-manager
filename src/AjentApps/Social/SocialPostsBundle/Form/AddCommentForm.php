<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\forms\WgForm;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\Contact;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\SocialPostsBundle\Entity\WallPost;
use AjentApps\Social\SocialPostsBundle\Entity\Comment;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class AddCommentForm extends WgForm
{
    /**
     * @Assert\NotBlank(message = "Please enter a wall post")
     */
	public $id;

    /**
     * @Assert\NotBlank(message = "Please enter a comment")
     */
	public $message;

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add("message");
	}

	public function getName()
	{
		return "AddComment";
	}

	public function submitForm()
	{
		$wallPost = new WallPost();
		$wallPost->load($this->id);

		$contact = new Contact();
		$contact->loadUser();

		$comment = array(
			"first_name" => $contact->first_name,
			"author_user_id" => SessionManager::$user->id,
			"message" => $this->message
		);

		//because php blows and is buggy, isset() doesn't work on overloaded
		//magic array properties. So we need to supplement this.
		if (!isset($wallPost->comments))
		{
			$wallPost->comments = array();
		}

		//this sucks, but i can't figure out an alternative
		$allComments = $wallPost->comments;
		$allComments[] = $comment;
		$wallPost->comments = $allComments;

		$wallPost->save();
	}
}
