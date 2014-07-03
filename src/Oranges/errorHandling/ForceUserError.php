<?php
namespace Oranges\errorHandling;

/**
 This is a validator where, if validation fails, execution stops and a user-friendly
 "red box" error-message is displayed.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class ForceUserError extends CheckType
{
	public static $inst;

	public function error(ErrorMetaData $metaData = null)
	{
		throw new UserErrorException($metaData);
	}
}

ForceUserError::$inst = new ForceUserError();
