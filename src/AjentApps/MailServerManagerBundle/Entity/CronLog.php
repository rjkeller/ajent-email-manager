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
class CronLog extends DatabaseModel
{
	protected function getTable()
	{
		return "cron_log";
	}

	public function getFields()
	{
		return array(
			"server_id",
			"log_entry",
			"date"
		);
	}

	public function __construct()
	{
		parent::__construct();

		$this->date = time();
	}
}
