<?php
namespace Oranges\UserBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\forms\StdData;
use Oranges\LoggingBundle\Helper\Logger;
use Oranges\UserBundle\Helper\SessionManager;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 This is a class that stores in the database user information, such as their name, 
 address, phone number, etc. This is used for WordGrab profiles, invoices, and
 domain registrant information.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="contacts")
*/
class Contact extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
    protected $id;
    
	/** @ORM\Column(type="integer") */
    protected $user_id;

	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $ip_address;

	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $first_name;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $middle_name;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $last_name;
    
	/** @ORM\Column(type="string") */
    protected $company;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	 @Assert\Email()
	*/
    protected $email;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $address1;
    
	/** @ORM\Column(type="string") */
    protected $address2;
    
	/** @ORM\Column(type="string") */
    protected $address3;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $city;

	/**
	 @ORM\Column(type="string")
	 @Assert\MinLength(2)
	 @Assert\MaxLength(2)
	*/
    protected $state;

	/**
	 @ORM\Column(type="string")
	 @Assert\MaxLength(10)
	*/
    protected $zip;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $country;
    
	/** @ORM\Column(type="integer") */
    protected $phone;
    
	/** @ORM\Column(type="integer") */
    protected $phone_country_code;
    
	/** @ORM\Column(type="integer") */
    protected $phone_extension;
    
	/** @ORM\Column(type="integer") */
    protected $fax;
    
	/** @ORM\Column(type="integer") */
    protected $fax_country_code;
    
	/** @ORM\Column(type="integer") */
    protected $fax_extension;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $security_question;
    
	/**
	 @ORM\Column(type="string")
	 @Assert\NotBlank()
	*/
    protected $security_answer;

	protected function getTable()
	{
		return "contacts";
	}

	public function loadUser($user_id = -1)
	{
		if ($user_id == -1)
			$user_id = SessionManager::$user->id;

		parent::loadQuery("user_id = '". $user_id ."'");
	}

	public function toString()
	{
		$cstr = "";
		$cstr .= $this->first_name ." ". $this->last_name ."<br>\n";
		$cstr .= $this->pm($this->company);
		$cstr .= $this->pm($this->address1);
		$cstr .= $this->pm($this->address2);
		$cstr .= $this->pm($this->address3);
		if (!empty($this->city))
			$cstr .= $this->city. ", ". $this->state ." ". $this->zip ."<br>\n";
		if ($this->country != "")
			$cstr .= $this->pm(StdData::ccCodeToCountryName($this->country));
		if ($this->phone != "" && $this->phone != 0)
			$cstr .=  "Phone: +". $this->phone_country_code .".". $this->phone ."<br>\n";
		if ($this->fax != "" && $this->fax != 0)
			$cstr .=  "Fax: +". $this->fax_country_code .".". $this->fax ."<br>\n";
		return $cstr;
	}

	public function save()
	{
		parent::save();
	}

	private function pm($i) { if (!empty($i)) return "$i<br>\n"; }
}

?>
