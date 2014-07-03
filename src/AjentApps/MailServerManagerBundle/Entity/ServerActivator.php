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
class ServerActivator extends DatabaseModel
{
	protected function getTable()
	{
		return "server_activator";
	}

	public function getFields()
	{
		return array(
			"server_id",
			"is_cron_enabled"
		);
	}

	public function loadServer($server_id)
	{
		parent::loadQuery(array("server_id" => $server_id));
	}
}
