<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

/**
 An email attachment stored in GridFs.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Attachment extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $email_message_id;

	/** @ORM\Column(type="string") */
//	protected $file_id;


	protected function getTable()
	{
		return "email_attachments";
	}
}
