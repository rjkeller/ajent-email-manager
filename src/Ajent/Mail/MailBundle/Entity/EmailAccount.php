<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 An email account in the Ajent system. Generally represents a local email
 account (and not an external one)

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailAccount extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="integer") */
//	protected $domain_id;

	/** @ORM\Column(type="string") */
//	protected $username;

	/** @ORM\Column(type="string") */
//	protected $domain;

	/** @ORM\Column(type="string") */
//	protected $full_email_address;

	/** @ORM\Column(type="string") */
//	protected $old_email_address;

	/** @ORM\Column(type="string", length="20") */
//	protected $password;

	/** @ORM\Column(type="integer") */
//	protected $quota = -1;

	protected function getTable()
	{
		return "email_accounts";
	}

	public function __construct()
	{
		parent::__construct();
		$this->__encrypt['password'] = true;
	}

	public function loadFullEmailAddress($address)
	{
		return parent::loadQuery(
			array("full_email_address" => $address)
			, true);
	}

	public function loadUser($user_id = null)
	{
		if ($user_id == null)
			$user_id = SessionManager::$user->id;

		return parent::loadQuery(
			array("user_id" => $user_id)
			, true);
	}

    public function create()
    {
        $this->full_email_address = $this->username . '@'. $this->domain;

        parent::create();
    }
}
