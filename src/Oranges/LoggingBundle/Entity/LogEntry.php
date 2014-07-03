<?php
namespace Oranges\LoggingBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\errorHandling\CheckType;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\UserBundle\Entity\User;

use Oranges\framework\BuildOptions;

use Oranges\misc\WgTextTools;

use Doctrine\ORM\Mapping as ORM;

/**
 Represents a entry in the master database log. This is designed to log all
 items throughout the web application.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class LogEntry extends DatabaseModel
{
	public function getFields()
	{
		return array(
			"timestamp",
			"type",
			"description");
		/* Optional fields:
			- user_id
			- is_warning
			- stack_trace
			- extra_data
		*/
	}

	protected function getTable()
	{
		return "logs";
	}

	public function create($autogenStacktrace = true)
	{
		global $DEBUG_TOOLBAR;
		if ($DEBUG_TOOLBAR)
			$DEBUG_TOOLBAR->addDebug("$type: $str");

		if ($autogenStacktrace)
		{
			$ex = new \Exception();
			$this->stack_trace = $ex->getTraceAsString();
		}

		if (SessionManager::$logged_in)
		{
			$this->user_id = SessionManager::$user->id;
		}
		$this->timestamp = microtime(true);
		parent::create();
	}

	public function getImage()
	{
		if (isset($this->is_warning) && $this->is_warning)
			return "/bundles/logging/images/error-log.gif";

		switch ($this->type)
		{
		case 'dql':
			return "/bundles/logging/images/api-terminal.gif";
		case 'registrarServer':
			return "/bundles/logging/images/registrar-server.gif";
		case 'switch':
			return "/bundles/logging/images/email_md.gif";
		case 'invoice':
		case 'billing':
			return "/bundles/logging/images/billing_mds.gif";
		case 'cart':
			return "/bundles/logging/images/MyCart_sm.gif";
		case 'contact':
			return "/bundles/logging/images/profile_mds.gif";
		case 'cron':
			return "/bundles/logging/images/forums_32.png";
		case 'domains':
			return "/bundles/logging/images/domains_mds.gif";
		default:
			return "/bundles/logging/images/whois-icon.gif";
		}
	}

	/**
	 Returns a truncated version of the full log description. Only shows a
	 maximum of 40 characters.
	*/
	public function getShortDescription()
	{
		return WgTextTools::truncate($this->description, 40);
	}

	/**
	 Returns a nicely formatted timestamp.
	*/
	public function getTimestamp($format = "m/d/Y g:i a")
	{
		return date($format, (int)$this->timestamp);
	}

	public function printLogEntry()
	{
		print_r($this->getArray());
	}

	/**
	 Returns the username of the user associated with this log entry (or n/a
	 if there is no user associated with it).
	*/
	public function getUsername()
	{
		if (!isset($this->user_id))
			return "n/a";

		$user = new User();
		$user->load($this->user_id);
		return $user->username . " (". $user->id .")";
	}

	public function getTemplate()
	{
		if (!isset(BuildOptions::$get['LoggingBundle']) ||
			!isset(BuildOptions::$get['LoggingBundle'][$this->type]))
			return "";

		return BuildOptions::$get['LoggingBundle'][$this->type];
	}

	/**
	 Returns the log description and extra data, but with the <br> tag replaced
	 with a newline, so the description looks good in a <textarea>.
	*/
	public function toString()
	{
		return str_replace("<br>", "\n", $this->desc) . "\n\n". $row->extraData;
	}
}
