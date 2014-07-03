<?php
namespace Oranges\errorHandling;

use Oranges\user\Helper\User;

/**
 This is a validator where, if validation fails, execution stops and a user-friendly
 "Bomb" error-message is displayed.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class ForceError extends CheckType
{
	public static $inst;

	public function error(ErrorMetaData $metaData = null)
	{
		if ($metaData == null)
			$metaData = new ErrorMetaData("Invalid Input", "You have input invalid data. Please try again. If you believe this is in error, please contact support.");
		throw new UnrecoverableSystemException($metaData->title, $metaData->message, $metaData->systemError);
	}

	public function checkAccess($perm)
	{
		if (!$perm)
			$this->error(new ErrorMetaData("Access Denied", "The resource you have attempted to access is not available to you. If you believe this is in error, please contact support.", "User ". User::$username . " attempted to access $_SERVER[SCRIPT_FILENAME]"));
		return true;
	}
}
ForceError::$inst = new ForceError();


?>
