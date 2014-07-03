<?php
namespace Oranges\LoggingBundle\Helper;

use Oranges\LoggingBundle\Entity\LogEntry;
use Oranges\errorHandling\CheckType;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\misc\Mailer;

/**
 * Logs developer-oriented messages in the database.
 * Designed to be used with the WordGrab Logging GUI.
 *
 * @author R.J. Keller <rjkeller@wordgrab.com>
 * @version 2.0
 */
class Logger
{
	//this private varaible is used to prevent recurssive
	//loggging, which can happen if the LoggingBundle system
	//is integrated with the PHP exception handling
	//system.
	private static $isLogging = false;

	//tells us how to handle categories. Possible options are
	//"echo", and "sql"
	public static $category_handler = array();

	/**
	Use this function to log an exception. Optionally you can also
	provide a message with additional information about the exception.
	
	Things like the exception message and stack trace are automatically
	logged.
	
	@param $ex - A PHP Exception instance of the exception you want
	  to log.
	@param $message - [optional] A message about why the exception
	  occurred or with additional information about the exception.
	@param $type - The category that the exception falls under.
	*/
	public static function handle($ex, $message = null, $type = "general")
	{
		if (self::$isLogging)
			return;
		self::$isLogging = true;

		$entry = new LogEntry();
		$entry->type = $type;
		$entry->is_warning = true;
		if ($message == null)
			$entry->description = $message;
		else
			$entry->description = $ex->getMessage();

		$exception['file'] = $ex->getFile();
		$exception['line'] = $ex->getLine();
		$entry->stack_trace = $ex;

		$entry->exception = array(
			"message" => $ex->getMessage(),
			"code" => $ex->getCode(),
			"file" => $ex->getFile(),
			"line" => $ex->getLine(),
			"trace" => $ex->getTraceAsString()
		);

		$entry->create();

		self::$isLogging = false;

		return $entry->id;
	}

	/**
	 Used to log a message in the system.
	
	 @param $str - The message you want to log.
	 @param $type - The category that the message falls under.
	 @param $isWarning - Whether or not to treat this message as a
	   system warning. When a message is a system warning, it is
	   treated as a problem by the LoggingBundle system, and will email
	   the administrators about the problem as well as having the
	   message show up under the Bomb icon in the GUI (overriding
	   the category icon).
	 */
	public static function log($str, $type = "general", $otherAttrs = null, $isWarning = false, $extra = null)
	{
		if (self::$isLogging)
			return;
		self::$isLogging = true;

		$entry = new LogEntry();
		$entry->type = $type;
		if ($isWarning)
			$entry->is_warning = true;
		if ($otherAttrs != null)
		{
			foreach ($otherAttrs as $key => $value)
			{
				$entry->$key = $value;
			}
		}
		$entry->description = $str;
		$entry->create();

		self::$isLogging = false;

		return $entry->id;
	}

}

function WG_LOGGER_error_handler($errno, $errstr, $errfile, $errline)
{
    switch ($errno) {
    case E_USER_ERROR:
		$ex = new \Exception($errstr);
		Logger::handle($ex);
        exit(1);
        break;

    case E_USER_WARNING:
		Logger::log($errstr . "\n$errfile@$errline", "general", true);
        break;

    case E_USER_NOTICE:
		Logger::log($errstr . "\n$errfile@$errline");
        break;

    default:
		Logger::log($errstr . "\n$errfile@$errline");
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}
