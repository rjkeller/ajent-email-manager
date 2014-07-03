<?php
namespace Ajent\Mail\MailBundle\Cache;

use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Mail\MailBundle\Event\MailEvent;
use Ajent\Mail\MailBundle\Entity\EmailAccount;

use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\MongoDbBundle\Helper\ModelCache;
use Oranges\sql\Database;

/**
 * This is a special cache. When a Mail model command is hit, we generate a
 * summary of that information in another table.
 * 
 * @author R.J. Keller <rjkeller@pixonite.com>
 */
class CategoryCache implements ModelCache
{
	public function create(array $messages)
	{
		$userIds = array();

		foreach ($messages as $msg)
		{
			$userIds[] = $msg->recipient_user_id;
		}
		$userIds = array_unique($userIds);

		foreach ($userIds as $user_id)
		{
			$this->refreshCache($user_id);
		}
	}

	public function save(array $messages)
	{
		$userIds = array();

		foreach ($messages as $msg)
		{
			$userIds[] = $msg->recipient_user_id;
		}
		$userIds = array_unique($userIds);

		foreach ($userIds as $user_id)
		{
			$this->refreshCache($user_id);
		}
	}

	public function delete(array $messages)
	{
		$userIds = array();

		foreach ($messages as $msg)
		{
			$userIds[] = $msg->recipient_user_id;
		}
		$userIds = array_unique($userIds);

		foreach ($userIds as $user_id)
		{
			$this->refreshCache($user_id);
		}
	}

	public function refreshCache($user_id = null)
	{
		if (isset(BuildOptions::$get['DisableMailCache']))
			return false;

		$refreshAll = $user_id == null;
		$db = MongoDb::getDatabase();

		$whereClause = array();
		if (!$refreshAll)
		{
			$whereClause['user_id'] = $user_id;
		}

		$categories = MongoDb::modelQuery(
			$db->email_categories->find($whereClause),
			"Ajent\Mail\MailBundle\Entity\Category");

		foreach ($categories as $c)
		{
			$c->num_new_messages = $db->email_messages->find(array(
				'folder' => array('$ne' => 'trash'),
				'is_read' => false,
				'category_id' => $c->id,
				'is_invisible' => false
				))
				->count();
			$c->num_messages = $db->email_messages->find(array(
				'folder' => array('$ne' => 'trash'),
				'category_id' => $c->id,
				'is_invisible' => false
				))
				->count();

			$c->save();
		}
	}
}
