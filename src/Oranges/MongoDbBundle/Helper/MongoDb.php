<?php
namespace Oranges\MongoDbBundle\Helper;

use Oranges\framework\BuildOptions;

class MongoDb
{
	private static $inst = null;

	public static function getDatabase()
	{
		if (self::$inst == null)
		{
			$m = null;
			if (isset(BuildOptions::$get['MongoDbBundle']) &&
				isset(BuildOptions::$get['MongoDbBundle']['host']))
				$m = new \Mongo(BuildOptions::$get['MongoDbBundle']['host']);
			else
				$m = new \Mongo();

			if (isset(BuildOptions::$get['MongoDbBundle']) &&
				isset(BuildOptions::$get['MongoDbBundle']['db']))
			{
				self::$inst = $m->selectDB(BuildOptions::$get['MongoDbBundle']['db']);
			}
			else
			{
				self::$inst = $m->ajent;
			}
		}
		return self::$inst;
	}

	public static function modelQuery($query, $model)
	{
		return new ModelIterator($query, $model);
	}
}
