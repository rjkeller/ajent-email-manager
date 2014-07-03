<?php
namespace Oranges\misc;

use Oranges\framework\BuildOptions;

class WgTextTools
{
	public static function hash($data, $salt = null)
	{
		$algorithm = "sha512";
		if (isset(BuildOptions::$get['hash_algorithm']))
			$algorithm = BuildOptions::$get['hash_algorithm'];

		if ($algorithm == "sha1" && $salt != null)
			$data = substr($salt, 0, 9) . $data;
		else if ($salt != null)
			$data = $salt . "wg". $data ."wg". $salt;

		$hash = hash($algorithm, $data);
		return $hash;
	}

	public static function uniqueid($num = -1)
	{
		if ($num == -1)
			return md5(uniqid(rand(), true));
		else
			return substr(WgTextTools::uniqueid(), 0, $num);
	}

	public static function strToDateTime($date, $format)
	{
    	if(!($date = strptime($date, $format))) return;
    	$dateTime = array('sec' => 0, 'min' => 0, 'hour' => 0, 'day' => 0, 'mon' => 0, 'year' => 0, 'timestamp' => 0);
    	foreach($date as $key => $val) {
        	switch($key) {
            	case 'd':
	            case 'j': $dateTime['day'] = intval($val); break;
	            case 'D': $dateTime['day'] = intval(date('j', $val)); break;
	           
	            case 'm':
	            case 'n': $dateTime['mon'] = intval($val); break;
	            case 'M': $dateTime['mon'] = intval(date('n', $val)); break;
	           
	            case 'Y': $dateTime['year'] = intval($val); break;
	            case 'y': $dateTime['year'] = intval($val)+2000; break;
	           
	            case 'G':
	            case 'g':
	            case 'H':
	            case 'h': $dateTime['hour'] = intval($val); break;
	           
	            case 'i': $dateTime['min'] = intval($val); break;
	           
	            case 's': $dateTime['sec'] = intval($val); break;
	        }
	    }
	    $dateTime['timestamp'] = mktime($dateTime['hour'], $dateTime['min'], $dateTime['sec'], $dateTime['mon'], $dateTime['day'], $dateTime['year']);
	    return $dateTime;
	}

	public static function truncate($str, $num = 55)
	{
		if (strlen($str) > $num)
			return substr($str, 0, $num) . "...";
		else
			return $str;
	}
}
