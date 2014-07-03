<?php
namespace Ajent\Mail\PeopleScannerBundle\Helper;

use Oranges\MongoDbBundle\Helper\MongoDb;

/**
 Determines if a string is a person's name. Does various searching
 capabilities to perform this.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class PeopleScanner
{
	/**
	 Returns whether or not the string inputted contains a person's name.
	
	 @param string $string - The string to see if it equals a person's name.
	 @return boolean - Whether or not it's equal to a person's name.
	*/
	public static function isName($string)
	{
		$data = explode(" ", $string);
		//if there is NOT a first and a last name, then it must not be a name.
		if (sizeof($data) != 2)
			return false;

		$data[0] = str_replace("'", "", $data[0]);

		$db = MongoDb::getDatabase();
		$num_names = $db->person_names->find(
				array("name" => $data[0]))
				->count();

		return $num_names > 0;
	}
}