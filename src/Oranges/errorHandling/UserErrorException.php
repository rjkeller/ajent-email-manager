<?
namespace Oranges\errorHandling;

use \Exception;
use \Build_Options;

use Oranges\frontend\WgSmarty;
use Oranges\LoggingBundle\Helper\Logger;

/**
 This is an exception that will show a Redbox error when thrown.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class UserErrorException extends Exception
{
	public $message;
	public $systemError;
	public $title;

	private static $isRunning = false;

	public function __construct(ErrorMetaData $metaData)
	{
		$title = $metaData->title;
		$message = $metaData->message;
		$systemError = $metaData->systemError;

		//if we're in cron mode, just return so we throw the exception instead
		//of bombing out.
		if (Build_Options::$CRON_MODE)
			return;
		global $INIT_DOCTYPE,$INIT_TOP,$INIT_META,$MODULE_START;

		if (self::$isRunning)
		{
			throw new Exception($systemError);
		}
		self::$isRunning = true;
		$smarty = WgSmarty::getInstance();

		//if smarty has not yet been initialized, then we have no front-end. So we
		//have no choice but to bomb out now at the PHP level.
		if ($smarty == null)
			throw new Exception($systemError);

		$this->systemError = $systemError;
		$this->message = $message;
		$this->title = $title;


		$id = Logger::handle($this);


		$smarty->assign('INIT_DOCTYPE', $INIT_DOCTYPE);
		$smarty->assign('INIT_TOP', $INIT_TOP);
		$smarty->assign('INIT_META', $INIT_META);
		$smarty->assign('MODULE_START', $MODULE_START);

		$smarty->assign("title", $title);
		$smarty->assign("message", $message);
		$smarty->assign("systemError", $systemError);
		$smarty->assign("stackTrace", $this->getTraceAsString());



		$smarty->display('errorhandling/user_error_exception.smarty');

		self::$isRunning = false;
		die();
	}
}

?>
