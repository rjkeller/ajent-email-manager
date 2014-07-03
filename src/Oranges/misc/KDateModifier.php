<?php
namespace Oranges\misc;

/**
 This class provides quick, on the fly date manipulations to date strings.

 All date strings passed in must be in the format of Y-m-d H:i:s to be
 guaranteed to be parsed correctly. Everything else is whildcard (it
 might work, or might not).

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class KDateModifier
{
	public static function add30Days($date)
	{
		$date = new KDate($date);
		$date->addDay(30);
		$date = new KDate();
		$date->gotoBeginningMonth();
		return $date->__toString();
	}

	public static function addMonth($date)
	{
		$date = new KDate($date);
		$date->addMonth();
		return $date->__toString();
	}

	public static function addYear($date)
	{
		$date = new KDate($date);
		$date->addYear();
		return $date->__toString();
	}

	public static function addDays($numDays, $date = null)
	{
		$date = new KDate($date);
		$date->addDay($numDays);
		return $date->__toString();
	}

	public static function getBeginningOfMonth()
	{
		$date = new KDate();
		$date->gotoBeginningMonth();
		return $date->__toString();
	}

	public static function getThisMonth()
	{
		return KDate_Modifier::getBeginningOfMonth();
	}

	public static function getNextMonth()
	{
		$date = new KDate();
		$date->addMonth();
		return $date->__toString();
	}

	public static function standard($date)
	{
		return date("m/d/y", strtotime($date));
	}

	private static $strings = array(
		'y' => array('1 year ago', '%d years'),
		'm' => array('1 month ago', '%d months'),
		'd' => array('1 day ago', '%d days'),
		'h' => array('1 hour', '%d hours'),
		'i' => array('1 minute ago', '%d minutes'),
		's' => array('now', '%d secons'),
	);

	/** Function by dev dot ivangc at gmail dot com
	http://php.net/manual/en/function.time.php */
	private static function getDiffText($intervalKey, $diff)
	{
		$pluralKey = 1;
		$value = $diff->$intervalKey;
		if($value > 0){
			if($value < 2){
				$pluralKey = 0;
			}
			return sprintf(self::$strings[$intervalKey][$pluralKey], $value);
		}
		return null;
	}

	/** Function by dev dot ivangc at gmail dot com
	http://php.net/manual/en/function.time.php */
	public static function getTimeAgo(\DateTime $date, $timeText = " ago")
	{
		$now = new \DateTime("now",
					new \DateTimeZone("GMT"));

		$diff = $date->diff($now);
		$diffText = "";
		foreach (self::$strings as $key => $value){
			if ( ($text = self::getDiffText($key, $diff)) ){
				if ($text != "now")
					return $text . $timeText;
				return $text;
			}
		}
		return "";
	}

}
