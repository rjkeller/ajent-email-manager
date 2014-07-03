<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Doctrine\ORM\Mapping as ORM;
use Oranges\sql\Database;

/**
 A user's contact list.
 
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Contact extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="string") */
//	protected $name;

	/** @ORM\Column(type="string") */
//	protected $email;

	protected function getTable()
	{
		return "email_contacts";
	}

	public static function refreshContactCache($user_id = null)
	{
		if ($user_id == null)
			$user_id = SessionManager::$user->id;

		$users = Database::modelQuery("
			SELECT
				*
			FROM
				users
		",
		"Oranges\UserBundle\Entity\User");

		foreach ($users as $user)
		{
			$db = MongoDb::getDatabase();
			$db->email_contacts->remove(
				array("user_id" => $user->id)
			);

			$q = $db->command(array(
				"distinct" => "email_messages",
				"key" => "from_email",
				"query" => array(
					"from_name" => array('$ne' => ''),
					"recipient_user_id" => $user_id
					)
				));

			foreach ($q['values'] as $contactInfo)
			{
				$contact = new Contact();
				$contact->user_id = $user->id;
				$contact->name = $contactInfo['from_name'];
				$contact->email = $contactInfo['from_email'];
				$contact->create();
			}
		}
	}
}
