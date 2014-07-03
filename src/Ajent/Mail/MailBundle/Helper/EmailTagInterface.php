<?php
namespace Ajent\Mail\MailBundle\Helper;

use Pixonite\TagCloudBundle\Helper\TagProductInterface;
use Oranges\sql\Database;

/**
 Returns various information about the item you wish to tag. You need to
 implement one of this class and set it in the Build Options to use the
 Tag Bundle.

  @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailTagInterface implements TagProductInterface
{
	/**
	 Returns all of the posts in the SQL database. Should return an
	 SqlIterator.
	*/
	public function getIterator($user_id)
	{
		$db = MongoDb::getDatabase();
		return MongoDb::modelQuery($db->email_messages->find(array(
				"recipient_user_id" => SessionManager::$user->id)),
			"Ajent\Mail\MailBundle\Entity\EmailMessage");
	}

	/**
	 Returns the text of the specified blog post that you would like to tag.
	*/
	public function getText($emailMessage)
	{
		return strip_tags(
			$emailMessage->getMessageBody()
		);
	}

	/**
	 Returns the product ID of the specified blog post.
	*/
	public function getProductId($emailMessage)
	{
		return $emailMessage->_id;
	}
}
