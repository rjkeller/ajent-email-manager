<?php
namespace Oranges\RedisBundle\Helper;

/**
 Provides a class that manages connections to Redis, which stores a lot of our
 analytics.

 @author R.J. Keller <rjkeller@teamajent.com>
*/
class Redis
{
	private static $predis_instance = null;

	public static function getInstance()
	{
		if (self::$predis_instance == null)
		{
			self::$predis_instance = new \Predis\Client();
		}
		return self::$predis_instance;
	}
}
