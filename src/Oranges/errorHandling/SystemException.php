<?php
namespace Oranges\errorHandling;


class SystemException extends Exception
{
	public $message;
	public $systemError;

	public function __construct($message, $systemError)
	{
		$this->message = $message;
		$this->systemError = $systemError;
	}
}
