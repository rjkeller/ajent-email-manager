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

/**
 * Represents an external email account (like a users old GMail account) whose
 * emails can be imported into their Ajent email account.
 * 
 * @author R.J. Keller <rjkeller@pixonite.com>
 */
class ExternalAccount extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="string") */
//	protected $server;

	/** @ORM\Column(type="string") */
//	protected $username;

	/** @ORM\Column(type="string") */
//	protected $password;

	/** @ORM\Column(type="string") */
//	protected $type;

	/** @ORM\Column(type="string") */
//	protected $sync_message_id = 1;

	/** @ORM\Column(type="boolean") */
//	protected $allow_personal_emails = false;

	private $myFolders = null;

	protected function getTable()
	{
		return "email_external_accounts";
	}


	public function __construct()
	{
		parent::__construct();

		$this->__key = md5("Ajent\Mail\MailBundle\Entity\ExternalAccount");
		$this->__encrypt['password'] = true;
	}

	public function loadUser($user_id = -1)
	{
		if ($user_id == -1)
			$user_id = SessionManager::$user->id;

		return parent::loadQuery(
			array("user_id" => $user_id), true);
	}

	public function getQueryString()
	{
		if ($this->type == "Gmail")
		{
		    return "imap.gmail.com:993/imap/ssl/novalidate-cert";
		}
		else if ($this->type == "Yahoo")
		{
		    return "imap.mail.yahoo.com:993/imap/ssl";
		}
		else if ($this->type == "AOL")
		{
		    return "imap.aol.com:993/imap/ssl";
		}

        return $this->server .":143/novalidate-cert";
	}

	/**
	 Returns true if we are able to successfully connect to this external
	 account. Returns false otherwise.
	*/
	public function canConnect()
	{
		if (($this->username == "") ||
			($this->password == "") ||
			($this->type == ""))
		{
			return false;
		}

		//prevent annoying PHP session blocking
		session_write_close();

		$auth = @imap_open(
			"{".
			    $this->getQueryString()
			. "}INBOX",

				$this->username,
				$this->password,
				OP_HALFOPEN);
		if (!$auth)
			return false;

		imap_close($auth);

		//surpress any stupid notices from IMAP.
		imap_errors();

		return true;
	}

    public function getInbox()
    {
        return new ExternalFolder(
            "{". $this->getQueryString() ."}INBOX",
            $this);
    }

	public function getFolder($connString)
	{
		$folders = $this->getFolders();
		foreach ($folders as $f)
		{
			if ($f->connString == $connString)
				return $f;
		}
		return null;
	}

	public function getFolders()
	{
		if ($this->myFolders != null)
			return $this->myFolders;

	    $queryString = $this->getQueryString();
		$auth = imap_open(
			"{".
				$queryString .
			"}",

				$this->username,
				$this->password,
				OP_HALFOPEN);
		if (!$auth)
		{
			$this->myFolders = array();
			return $this->myFolders;
		}

        $list = imap_getmailboxes($auth, "{". $queryString ."}", "*");
        
        if (is_array($list))
        {
            //because email blows, the server sometimes gives us the same
            //folder twice. We mitigate this problem by making the folder name
            //the key to the array and setting it to true. That way we can loop
            //through the array keys to get all the valid folders.
            $folderNames = array();
            foreach ($list as $key => $value)
            {
				//We skip folders that we only support getting explicitly.
				if (strpos($value->name, "All Mail") !== false ||
					strpos($value->name, "Spam") !== false ||
					strpos($value->name, "[Gmail]") !== false ||
					strpos($value->name, "Trash") !== false ||
					strpos($value->name, "Drafts") !== false ||
					strpos($value->name, "Sent") !== false)
					continue;
				$folderNames[$value->name] = true;
            }

            $folders = array();
            foreach ($folderNames as $key => $value)
            {
                $folders[] = new ExternalFolder($key, $this);
            }

            $this->myFolders = $folders;
        }
        else
            $this->myFolders = array();

		imap_close($auth);

		//surpress any stupid notices from IMAP.
		imap_errors();

		return $this->myFolders;
	}

    private $trashBinName = null;
    public function getTrashBinName()
    {
        if ($this->trashBinName != null)
            return $this->trashBinName;

		$mailFolders = $this->external_account->getFolders();

        $trashFolder = null;
		foreach ($mailFolders as $folder)
		{
			if (strpos($folder->connString, 'Trash') !== false)
			{
				$folder = explode('}', $folder->connString);
				$this->trashBinName = $folder[1];
				return $this->trashBinName;
			}
		}
		
    }
}
