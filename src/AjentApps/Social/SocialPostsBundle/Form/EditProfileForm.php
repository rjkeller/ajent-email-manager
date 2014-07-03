<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\MasterContainer;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\UserBundle\Entity\User;
use Oranges\UserBundle\Entity\Contact;

use Symfony\Component\Validator\Constraints as Assert;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EditProfileForm extends AbstractType
{
	/** @Assert\Choice(choices = {"public", "private"}, message = "Please select a valid profile access.") */
	public $profile_access;

	
    /**
     * @Assert\NotBlank(message = "Please enter a First Name")
     */
	public $first_name;
	
    /**
     * @Assert\NotBlank(message = "Please enter a Last Name")
     */
	public $last_name;


	public function __construct()
	{
		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);

		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);

		if (isset($profile->is_profile_public))
			$this->profile_access = $profile->is_profile_public ? "public" : "private";

		$this->first_name = $contact->first_name;
		$this->last_name = $contact->last_name;
		
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add("profile_access", "choice", array(
			'choices' => array(
				"public" => "All Members",
				"private" => "Friends Only"
			),
			'expanded' => true)
		);

		$builder->add("first_name");
		$builder->add("last_name");
	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'AjentApps\Social\SocialPostsBundle\Form\EditProfileForm',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => 'edit_profile_form'
        );
    }

	public function getName()
	{
		return "edit_profile_form";
	}

	public function submitForm()
	{
		$contact = new Contact();
		$contact->loadUser(SessionManager::$user->id);
		$contact->first_name = $this->first_name;
		$contact->last_name = $this->last_name;
		$contact->save();

		$profile = new UserProfile();
		$profile->loadUser(SessionManager::$user->id);
		$profile->is_profile_public = $this->profile_access == "public";
		$profile->save();
	}

	public function getErrors()
	{
		$errorList = MasterContainer::get("validator")->validate($this);
		$errors = "";
		foreach ($errorList as $error)
		{
			$errors .= $error->getMessage() . "<br>";
		}
		return array("title" => "Title", "message" => $errors);
	}
}
