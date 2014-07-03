<?php
namespace Ajent\Mail\ExternalMailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Mail\ExternalMailBundle\Helper\SendMessageListener;

use Ajent\Mail\MailBundle\Entity\EmailAccount;
use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Vendor\VendorBundle\Entity\Vendor;

use Ajent\Mail\ExternalMailBundle\Helper\MessageHandler;

/**
 Represents a folder inside of someone's email account (like Inbox, Trash,
 Sent, CustomFolder, etc.)
 
 Best to get these objects from $emailAccount->getFolders();

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class ExternalFolder
{
	public $connString;
	
	public $external_account;

	public $conn;

	private $isOpen = false;

	public function __construct(
		$conn, $ea)
	{
		$this->connString = $conn;
		$this->external_account = $ea;
	}

	public function getFolderName()
	{
		$out = str_replace('.', '', $this->connString);
		$out = str_replace('{', '', $out);
		$out = str_replace('}', '', $out);
		echo "$out<br>";
		return $out;
	}

	/**
	 Pass in an instance of ExternalFolder (or null) and it'll reopen the
	 connection for this folder. It's faster than closing and reopening the
	 connection.
	*/
	public function reopen($prev, $writeAccess = false)
	{
		if ($this->isOpen || $prev == null)
			return $this->open($writeAccess);

		//if they are the same folder, just move the connections and do no
		//imap calls.
		if ($this->connString == $prev->connString)
		{
			$this->conn = $prev->conn;
		}

		//otherwise, reopen the connection using imap_reopen
		else if ($writeAccess)
		{
			imap_reopen($prev->conn, $this->connString, OP_READONLY | CL_EXPUNGE);
			$this->conn = $prev->conn;
		}
		else
		{
			imap_reopen($prev->conn, $this->connString, CL_EXPUNGE);
			$this->conn = $prev->conn;
		}

		$prev->isOpen = false;
		$prev->conn = null;

		$this->isOpen = true;
	}

	public function open($writeAccess = false)
	{
		if ($this->isOpen)
			return $this->conn;
		else
			$this->isOpen = true;

		if (!$writeAccess)
		{
			$this->conn = @imap_open(
					$this->connString,

					$this->external_account->username,
					$this->external_account->password,
					OP_READONLY);
		}
		else
		{
			$this->conn = @imap_open(
					$this->connString,

					$this->external_account->username,
					$this->external_account->password);
		}
		return $this->conn;
	}

	public function close()
	{
		if (!$this->isOpen)
			return;

		$this->isOpen = false;

		imap_close($this->conn, CL_EXPUNGE);

		//surpress any stupid notices from IMAP.
		imap_errors();
	}

	/**
	 OK, this function is a little weird. Basically, this thing streams
	 messages into a handler. This is done to prevent the system from running
	 out of memory with crazy email accounts. So for this streaming, we pass in
	 a MessageHandler object, which has a receiveMessage(ExternalMessage $msg)
	 method that we call for each connection.
	 
	 Second parameter is an array of imap_search() search queries.
	 
	 @return boolean - Whether or not the query was ran successfully.
	 */
	public function getMessagesHandler(MessageHandler $handler, $imap_search)
	{
		$messages = imap_search($mailbox, $imap_search);

		if (empty($messages))
			return false;

		foreach ($messages as $value)
		{
			$handler->receiveMessage(new ExternalMessage(
				$this->conn,
				$value,
				$this->external_account)
			);
		}
		return true;
	}


	public function searchMessages($array_imap_search)
	{
		if (empty($array_imap_search))
			return array();

		$msgs = array();
		foreach ($array_imap_search as $imap_search)
		{
			$messages = imap_search($this->conn, $imap_search);
			\OrangesLogger("Searching ". $imap_search ." => ". sizeof($messages) ."...", "searchMessages", array(
					"conn" => $this->connString,
					"imap_search" => $array_imap_search,
					"results" => $messages));
			
			if (empty($messages))
				continue;

			foreach ($messages as $value)
			{
				$msgs[] = $value;
			}
		}

		//sometimes rsort() dies if there are no messages returned by
		//imap_search. So we'll just suppress errors so it doesn't bomb out in
		//that circumstance. Should be pretty safe.
		rsort($msgs);
		$msgs = array_unique($msgs);

		//reset the keys. Man, this seems stupid. Is there a better way?
		$msgOut = array();
		foreach ($msgs as $i)
		{
			$msgOut[] = $i;
		}
		$msgs = $msgOut;

		$len = sizeof($msgs);

		for ($i = 0; $i < $len; $i++)
		{
			$msgs[$i] = new ExternalMessage(
				$this->conn,
				$msgs[$i],
				$this->external_account);
		}

		return $msgs;
	}

	public function getAllMessages($startNum = 1)
	{
		$msgs = array();
		$num_messages = imap_num_msg($this->conn);
		for ($i = $startNum; $i <= $num_messages; $i++)
		{
			$msgs[] = new ExternalMessage(
				$this->conn,
				$i,
				$this->external_account);
		}
		return $msgs;
	}

	public function getNumMessages()
	{
		return imap_num_msg($this->conn);
	}
}
