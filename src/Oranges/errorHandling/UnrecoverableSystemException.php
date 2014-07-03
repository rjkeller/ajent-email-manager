<?php
namespace Oranges\errorHandling;

use Oranges\frontend\WgSmarty;
use Oranges\LoggingBundle\Helper\Logger;
use Oranges\framework\BuildOptions;

/**
 This is an exception that will show a Bomb error when thrown.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class UnrecoverableSystemException extends \Exception
{
	public $message;
	public $systemError;
	public $title;

	private static $isRunning = false;

	public function __construct($title = null, $message = null, $systemError = "")
	{
		//if we're in cron mode, just return so we throw the exception instead
		//of bombing out.
		if (BuildOptions::$get['cron_mode'])
			return;
		global $INIT_DOCTYPE,$INIT_TOP,$INIT_META,$MODULE_START;

		//if no title is supplied with the exception, then we show
		//a generic title and message to the user.
		if (empty($title))
		{
			$id = Logger::handle($this);
			$title = "Oops! ". BuildOptions::$get['company_name_short'] ." seems to have an issue";
			$message = "An internal issue has occurred within ". BuildOptions::$get['company_name_short'] .". This issue has been logged and will be reviewed by our development team. If you need immediate assistance with this problem, please file a support ticket referencing the information below.<br><br>Error #$id - Internal Error";
		}
		$output = "<h1>". $title ."</h1><p>". $message ."</p>";
		if (BuildOptions::$get['enable_debug'])
			$output .= "<h1>Debug Information</h1><p>". $systemError ."</p>";

		parent::__construct($output);
	}
}

?>
