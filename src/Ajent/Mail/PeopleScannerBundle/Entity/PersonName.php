<?php
namespace Ajent\Mail\PeopleScannerBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class PersonName extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="string") */
//	protected $name;

	protected function getTable()
	{
		return "person_names";
	}


	public function getExpirationDate()
	{
		return date("F j g:i a", strtotime($this->expiration_date));
	}
}
