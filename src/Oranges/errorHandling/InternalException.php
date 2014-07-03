<?php
namespace Oranges\errorHandling;

use Oranges\LoggingBundle\Helper\Logger;
use Oranges\framework\BuildOptions;
/**
 An internal WordGrab error. This one doesn't show message/title information to the end user,
 but instead gives the user a generic "Internal Error" message with the Bomb error format.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class InternalException extends \Exception
{
	public $title = "";
	public $message = "";
	public $systemError;

	private static $isRunning = false;

	public function __construct($systemError = "")
	{
		parent::__construct($systemError);

		$id = Logger::handle($this);
	}
}
