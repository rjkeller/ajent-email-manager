<?php
namespace Oranges\errorHandling;

/**
 Holds meta data that is used to communicate an error to the user if such
 a sitaution were to occur.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class ErrorMetaData
{
	public $message;
	public $title;
	public $systemError;

	public function __construct($title, $message = "", $systemError = "")
	{
		$this->message = $message;
		$this->title = $title;
		$this->systemError = $systemError;
	}
}
