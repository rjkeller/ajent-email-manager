<?php
namespace Oranges;

class MasterContainer
{
	public static $container;
	public static $isTesting = false;

	public static function getContainer()
	{
		return self::$container;
	}

	public static function get($str)
	{
		if (self::$isTesting && $str == "request")
			return null;

		return self::$container->get($str);
	}

	public static function getParameter($str)
	{
		if (self::$isTesting && $str == "request")
			return null;

		return self::$container->getParameter($str);
	}
}