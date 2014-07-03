<?php
namespace AjentApps\Social\SocialPostsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\sql\Database;
use Oranges\MasterContainer;

use Oranges\UserBundle\Entity\User;
use Oranges\misc\WgTextTools;

use Symfony\Component\Validator\Constraints as Assert;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ResetPasswordForm extends AbstractType
{
    /**
     * @Assert\NotBlank(message = "Please enter a password for your account")
     * @Assert\MinLength(limit = 5, message = "Please enter a password at least 5 characters long")
     */
	public $password;

	/** @Assert\Type("string") */
	public $password_confirm;

    /**
     * @Assert\True(message = "The 2 passwords you entered do not match.")
     */
	public function isPasswordConfirmIdentical()
	{
		return $this->password_confirm == $this->password;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add("password", "password");
		$builder->add("password_confirm", "password");
	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'AjentApps\Social\SocialPostsBundle\Form\ResetPasswordForm',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => 'reset_password_form'
        );
    }

	public function getName()
	{
		return "reset_password_form";
	}

	public function submitForm()
	{
		$user = new User();
		$user->load(SessionManager::$user->id);
		$user->password = WgTextTools::hash($this->password, $user->username);
		$user->save();
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
