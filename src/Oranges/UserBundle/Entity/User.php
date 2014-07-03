<?php
namespace Oranges\UserBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\MasterContainer;
use Oranges\framework\BuildOptions;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Doctrine\ORM\Mapping as ORM;

/**
 User Model for modifying user information

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="users")
*/
class User
	extends DatabaseModel
	implements AdvancedUserInterface
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="string") */
	protected $username = "";

	/** @ORM\Column(type="string") */
	protected $password = "";

	/** @ORM\Column(type="datetime") */
	protected $creation_date = "";

	/** @ORM\Column(type="string") */
	protected $session_id = "";

	/** @ORM\Column(type="string") */
	protected $session_ip = "";

	/** @ORM\Column(type="integer") */
	protected $contact_id = "";

	/** @ORM\Column(type="string") */
	protected $email = "";

	/** @ORM\Column(type="string") */
	protected $backup_email = "";

	/** @ORM\Column(type="string") */
	protected $role = "";

	/** @ORM\Column(type="boolean") */
	protected $is_deleted = false;


	public function __construct()
	{
		$this->creation_date = date("Y-m-d H:i:s");

		parent::__construct();
	}

	public function __toString()
	{
		return $this->username;
	}

	protected function getTable()
	{
		return "users";
	}

	public function loadUsername($username)
	{
		return $this->loadQuery("is_deleted = FALSE AND username = '". $username ."'");
	}

	public function loadEmail($email)
	{
		return $this->loadQuery("is_deleted = FALSE AND email = '". $email ."'");
	}

	public function loadSessionData($session_ip, $session_id, $id)
	{
		return $this->loadQuery("session_ip = '". $session_ip ."' AND session_id = '". $session_id ."' AND id = '". $id ."'", true);
	}

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->role;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return !$this->is_deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
    	return !$this->is_deleted;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * {@inheritDoc}
     */
    public function equals(UserInterface $user)
    {
        if (!$user instanceof User) {
            return false;
        }

        if ($this->password !== $user->getPassword()) {
            return false;
        }

        if ($this->getSalt() !== $user->getSalt()) {
            return false;
        }

        if ($this->username !== $user->getUsername()) {
            return false;
        }

        if ($this->isAccountNonExpired() !== $user->isAccountNonExpired()) {
            return false;
        }

        if ($this->isAccountNonLocked() !== $user->isAccountNonLocked()) {
            return false;
        }

        if ($this->isCredentialsNonExpired() !== $user->isCredentialsNonExpired()) {
            return false;
        }

        if ($this->isEnabled() !== $user->isEnabled()) {
            return false;
        }

        return true;
    }

	/**
	 Does the same as $this->create(), but also sends the user an email. So if
	 you want to create users without sending an email, just use the standard
	 create.
	
	 @param $contact - The contact with information on this user.
	*/
	public function register(Contact $contact, $password)
	{
		$this->contact_id = $contact->id;
		$this->create();

		$contact->user_id = $this->id;
		$contact->save();

		$container = MasterContainer::getContainer();

		$sessionManager = $container->get("Oranges.UserBundle.SessionManager");
		$sessionManager->login($this->username, $password);

		$companyName = BuildOptions::$get['company_name_short'];
		$emailVars = array(
			"username" => $this->username,
			"name" => $contact->first_name . " ". $contact->last_name,
			"company_name" => $companyName
		);

		if (!empty($this->email))
		{
			$mailer = $container->get('mailer');
			$message = \Swift_Message::newInstance()
				->setSubject('Welcome to '. $companyName .'!')
				->setFrom(BuildOptions::$get['from_email'])
				->setTo($this->email)
				->setBody(
					$container->get('templating')->render(
						'UserBundle:emails:WelcomeEmail.twig.txt',
						 $emailVars))
			;
		}
	}
}
