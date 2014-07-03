<?php
namespace Oranges\framework;

use Symfony\Component\Yaml\Parser;

/**
 Contains build options used by the Oranges classes.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class BuildOptions
{
	public static $get;

	/**
	 USE BY KERNEL ONLY!
	
	 Loads build options into the Oranges system.
	*/
	public static function loadBuildOptions($file)
	{
		$yaml = new Parser();
		self::$get = $yaml->parse(file_get_contents($file));
	}
}