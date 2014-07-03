<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Doctrine\ORM\Mapping as ORM;

/**
 A domain that we can receive emails for. Right now we don't support more than
 one of these, but in the future we might be able to accept emails from
 accounts other than @ajent.com.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailDomain extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;
	
	/** @ORM\Column(type="string") */
//	protected $domain;
	
	protected function getTable()
	{
		return "email_domains";
	}

	public function loadDomain($domain)
	{
		parent::loadQuery(array("domain" => $domain));
	}
}
