<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Doctrine\ORM\Mapping as ORM;

use Oranges\MongoDbBundle\Helper\DatabaseModel;

/**
 Allows us to set up an email forwarder (although is this ever used?)

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailForwarder extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="string") */
//	protected $source_email;

	/** @ORM\Column(type="string") */
//	protected $destination_email;

	protected function getTable()
	{
		return "email_forwarders";
	}
}
