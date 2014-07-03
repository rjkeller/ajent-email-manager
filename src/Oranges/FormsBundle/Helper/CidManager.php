<?php
namespace Oranges\FormsBundle\Helper;

use Oranges\sql\Database;
use Oranges\FormsBundle\Entity\FormCode;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\RedisBundle\Helper\Redis;

class CidManager
{
	private static $is_codes_loaded = false;
	private static $old_codes = array();
	private static $new_codes = array();

	public static function isCidValid($form_name)
	{
		if (!isset($_POST['cid']))
			return false;

		//if the current user is not logged in, we go into Anonymous form
		//submit mode.
		if (!SessionManager::$logged_in)
		{
			return $_POST['cid'] == $form_name;
		}

		$key = "FormCodes". SessionManager::$user->id;

		$redis = Redis::getInstance();
		if (!$redis->exists($key) ||
			!$redis->hexists($key, $form_name))
			return false;

		$real_code = $redis->hmget($key, $form_name);
		$real_code = $real_code[0];

		return $real_code == $_POST['cid'];
	}


	public static function getFormCidCode($form_name)
	{
		if (isset(self::$new_codes[$form_name]))
			return self::$new_codes[$form_name];

		$code = md5(uniqid(rand(), true));
		self::$new_codes[$form_name] = $code;

		$key = "FormCodes". SessionManager::$user->id;

		$redis = Redis::getInstance();

		//if the data type is wrong, or it's somehow corrupted, then kill the
		//key and re-create it.
		if ($redis->type($key) != "hash")
			$redis->del($key);

		$redis->hset($key, $form_name, $code);

		return $code;
	}

    public static function loadCodes()
    {
	/*
		//if the current user is not logged in, then we can't load any codes.
		if (!SessionManager::$logged_in)
		{
			return;
		}

        if (!self::$is_codes_loaded)
		{
			$redis = Redis::getInstance();

			//loop through each form code and load it in.
			$data = null;
			$key = "FormCodes". SessionManager::$user->id;

			while (($data = $redis->lpop($key)) != null)
			{
				$codes = explode(",", $data);
				self::$old_codes[$codes[0]] = $codes[1];
			}

			self::$is_codes_loaded = true;
			print_r(self::$old_codes);
			if (isset($_POST['cid']))
				echo "======> ". $_POST['cid'];

		}
		*/
    }
}