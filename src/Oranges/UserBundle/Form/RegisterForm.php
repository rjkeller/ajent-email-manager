<?php
namespace Oranges\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

use Oranges\user\Helper\UserExtras;
use Oranges\forms\StdTypes;
use Oranges\sql\Database;
use Oranges\MasterContainer;

use Symfony\Component\Validator\Constraints as Assert;

/**
 This form processes a new user registration.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class RegisterForm extends AbstractType
{
    /**
     * @Assert\NotBlank(message = "Please enter a username")
     * @Assert\MinLength(limit = 3, message = "Please enter a longer username")
     * @Assert\Regex(pattern = "/\w\d+/", message = "Please enter a username with only letters and numbers")
     */
	public $username;
	
    /**
     * @Assert\NotBlank(message = "Please enter a password for your account")
     * @Assert\MinLength(limit = 5, message = "Please enter a password at least 5 characters long")
     */
	public $password;
	
    /**
     * @Assert\NotBlank(message = "Please enter an email address for your account")
     * @Assert\Email(message = "The email address you entered is not valid")
     */
	public $email;
	
	/** @Assert\Type("string") */
	public $how_did_you_hear_about_us;

	/** @Assert\Type("string") */
	public $company_name;
	
    /**
     * @Assert\NotBlank(message = "Please enter a First Name")
     */
	public $first_name;
	
    /**
     * @Assert\NotBlank(message = "Please enter a Last Name")
     */
	public $last_name;
	
    /**
     * @Assert\NotBlank(message = "Please enter a valid address")
     * @Assert\MinLength(3)
     */
	public $address1;
	
	/** @Assert\Type("string") */
	public $address2;
	
    /**
     * @Assert\NotBlank(message = "Please enter a city")
     * @Assert\MinLength(limit = 3, message = "Please enter a valid city")
     */
	public $city;
	
    /**
     * @Assert\NotBlank(message = "Please enter a state")
     * @Assert\MinLength(limit = 2, message = "Please enter a valid state")
     * @Assert\MaxLength(limit = 2, message = "Please enter a valid state")
     */
	public $state;
	
    /**
     * @Assert\NotBlank(message = "Please enter a zip code")
     * @Assert\MinLength(limit = 4, message = "Please enter a valid zip code")
     * @Assert\MaxLength(limit = 8, message = "Please enter a valid zip code")
     */
	public $zip;
	
    /**
     * @Assert\NotBlank(message = "Please enter a country")
     * @Assert\MinLength(limit = 2, message = "Please enter a valid country")
     * @Assert\MaxLength(limit = 2, message = "Please enter a valid country")
     */
	public $country;
	
    /**
     * @Assert\NotBlank(message = "Please enter a credit card number")
     * @Assert\MinLength(limit = 16, message = "Please enter a valid credit card number")
     * @Assert\MaxLength(limit = 16, message = "Please enter a valid credit card number")
     * @Assert\Regex(pattern = "/\d+/", message = "Please enter a valid credit card number")
     */
	public $credit_card_number;
	
    /**
     * @Assert\Regex(pattern = "/\d+/", message = "Please enter a valid credit card expiration date")
     */
	public $credit_card_expiration_month;
	
    /**
     * @Assert\Regex(pattern = "/\d+/", message = "Please enter a valid credit card expiration date")
     */
	public $credit_card_expiration_year;
	
    /**
     * @Assert\Regex(pattern = "/\d+/", message = "Please enter a valid credit card CVV number")
     */
	public $credit_card_cvv;

    /**
     * @Assert\True(message = "This username is already taken")
     */
	public function isUsernameUnique()
	{
		$q = Database::scalarQuery("
			SELECT
				COUNT(*)
			FROM
				users
			WHERE
				username = '". $this->username ."'
		");
		return $q <= 0;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add("username");
		$builder->add("password", "password");
		$builder->add("email");
		$builder->add("how_did_you_hear_about_us");
		$builder->add("company_name");
		$builder->add("first_name");
		$builder->add("last_name");
		$builder->add("address1");
		$builder->add("address2");
		$builder->add("city");
		$builder->add("state");
		$builder->add("zip");
		$builder->add("country");
		$builder->add("credit_card_number");
		$builder->add("credit_card_expiration_month");
		$builder->add("credit_card_expiration_year");
		$builder->add("credit_card_cvv");
	}

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Oranges\UserBundle\Form\RegisterForm',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'intention'  => 'registration_form'
        );
    }

	public function submitForm()
	{
		$user = new User();
		$user->username = $this->username;
		$user->password = WgTextTools::hash($this->password, $user->username);
		$user->email = $this->email;
		$user->role = "Customer";

		$contact = new Contact;
		$contact->user_id = $user->id;
		$contact->first_name = $this->first_name;
		$contact->last_name = $this->last_name;
		$contact->email = $this->email;
		$contact->create();

		$user->register($contact);

		$sessionManager = MasterContainer::get("Oranges.UserBundle.SessionManager");
		$sessionManager->login($user->username, $this->password);
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
