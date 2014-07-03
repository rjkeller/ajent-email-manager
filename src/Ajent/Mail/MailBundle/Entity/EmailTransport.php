<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Doctrine\ORM\Mapping as ORM;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailTransport extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="string") */
//	protected $domain;

	/** @ORM\Column(type="string") */
//	protected $transport;

	protected function getTable()
	{
		return "email_transports";
	}
}
