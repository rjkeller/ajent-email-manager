<?php
namespace AjentApps\MailServerManagerBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;
use Oranges\RedisBundle\Helper\Redis;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Server extends DatabaseModel
{
	protected function getTable()
	{
		return "servers";
	}

	public function getFields()
	{
		return array(
			"name",
			"stats",
			"ip",
			"location",
			"munin_url"
		);
	}

	public function loadName($name)
	{
		return parent::loadQuery(array("name" => $name));
	}
}
