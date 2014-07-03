<?php
namespace Oranges\errorHandling;

use Oranges\FrontendBundle\Helper\MessageBoxHandler;

/**
 This is a validator where, if validation fails, it is logged in
 MessageBoxHandler, but execution continues. This is useful when validating
 several form fields, so you can tell the user at one time which fields were
 bad, and which were good.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class UserErrorHandler extends CheckType
{
	public static $inst;

	public $hasErrors = false;

	public function error(ErrorMetaData $metaData = null)
	{
		$this->hasErrors = true;
		ForceError::$inst->assert(!empty($metaData->title), new ErrorMetaData("", "", "UserErrorHandler must have errors defined for all data types."));
		if (empty($metaData->message))
			MessageBoxHandler::error($metaData->title);
		else
			MessageBoxHandler::error($metaData->message, $metaData->title);
	}

	public function doForceError()
	{
		ForceUserError::$inst->error(new ErrorMetaData("", $this->toString()));
	}
}
UserErrorHandler::$inst = new UserErrorHandler();
