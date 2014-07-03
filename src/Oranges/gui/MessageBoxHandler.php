<?php
namespace Oranges\gui;

class MessageBoxHandler
{
	private static $messages = array();

	public static function happy($message, $header = null)
	{
		array_push(self::$messages, array("happy", $message, $header));
	}

	public static function notice($message, $header = null)
	{
		array_push(self::$messages, array("notice", $message, $header));
	}

	public static function error($message, $header = null)
	{
		array_push(self::$messages, array("error", $message, $header));
	}

	public static function warning($message, $header = null)
	{
		array_push(self::$messages, array("warning", $message, $header));
	}

	public static function printMessages()
	{
		foreach (self::$messages as $i)
		{
			switch ($i[0])
			{
			case 'error':
				echo MessageBox::error($i[1], $i[2]);
				break;

			case 'happy':
				echo MessageBox::happy($i[1], $i[2]);
				break;

			case 'notice':
				echo MessageBox::notice($i[1], $i[2]);
				break;

			default:
				echo MessageBox::warning($i[1], $i[2]);
				break;
			}
		}
		self::$messages = array();
	}
}
