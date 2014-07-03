<?php
namespace AjentApps\Ajent\MailRegistrationBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class BetaInvite extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $name;

	/** @ORM\Column(type="string") */
//	protected $email;

	/** @ORM\Column(type="datetime") */
//	protected $date;

	public function __construct()
	{
		parent::__construct();
		
		$this->date = date("Y-m-d H:i:s");
	}

	protected function getTable()
	{
		return "beta_invites";
	}
}
