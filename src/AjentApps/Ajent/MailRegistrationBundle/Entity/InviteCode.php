<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class InviteCode extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="string") */
//	protected $invite_code;

	/** @ORM\Column(type="boolean") */
//	protected $is_active;

	public function __construct()
	{
		parent::__construct();

		$this->is_active = true;
		$this->invite_code = substr(uniqid(), 0, 7);
	}

	public function loadInviteCode($invite_code)
	{
		return parent::loadQuery(
			array("invite_code" => $invite_code), true);
	}

	protected function getTable()
	{
		return "beta_invite_codes";
	}
}
