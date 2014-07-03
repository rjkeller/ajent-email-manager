<?php
namespace Oranges\errorHandling;

/**
 Throw this when the current user cannot access this specific feature or command.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class AccessDeniedException extends UnrecoverableSystemException
{
	public $title = "";
	public $message = "";
	public $systemError;

	private static $isRunning = false;

	public function __construct($systemError = "")
	{
		$title = "Access Denied";
		$message = "You do not have access to the requested page. If you believe this is in error, please contact support.";
		parent::__construct($title, $message, $systemError);
	}
}
