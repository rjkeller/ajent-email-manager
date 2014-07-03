<?php
namespace Oranges\UserBundle\Entity;

use Oranges\DatabaseModel;
use Oranges\misc\WgTextTools;

use Doctrine\ORM\Mapping as ORM;

/**
 Stores how many login attempts a certain IP address has made.

 @author R.J. Keller <rjkeller@wordgrab.com>
 @ORM\Entity
 @ORM\Table(name="user_attempts")
*/
class UserAttempts extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id = "NULL";

	/** @ORM\Column(type="string") */
	protected $ip = "";

	/** @ORM\Column(type="datetime") */
	protected $timestamp = false;

	protected function getTable()
	{
		return "user_attempts";
	}
}
