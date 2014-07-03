<?php
namespace Ajent\Mail\ExternalMailBundle\Entity;

use Ajent\Mail\MailBundle\Entity\EmailMessage;
use Ajent\Mail\MailBundle\Entity\EmailAccount;

/**
 Represents an email messages on an external IMAP server. Provides various
 functions to simplify management of external messages.
 
 @author R.J. Keller <rjkeller@pixonite.com>
 */
class ExternalMessage
{
    public $message_id;

    public $conn;

    public $external_account;

    public function __construct($conn, $message_id, $external_account)
    {
        $this->message_id = $message_id;
        $this->conn = $conn;
		$this->external_account = $external_account;
    }

	/**
	 @param boolean $hideMe - Sets the initial value of is_invisible of the
	  email message. This is useful if you aren't sure you want to keep the
	  imported message, since it'll hide it from the user until you can decide
	  what to do with it.
	*/
    public function copyToEmailAccount(
        EmailAccount $account, $hideMe = false)
    {
        $temp_file_name =  "ext_". uniqid(rand(), true);
		$tmp_file = tempnam(sys_get_temp_dir(), $temp_file_name);

		//check if the vendor exists.
		$maildir_data = imap_savebody($this->conn, $tmp_file, $this->message_id, "", FT_PEEK);


        $email = new EmailMessage();
		$email->is_invisible = $hideMe;
        $email->loadFromMaildirFile($account, $tmp_file);

		unlink($tmp_file);
		return $email;
    }

    public function delete()
    {
        $isGood = imap_delete($this->conn, $this->message_id);

        //do we really need this? According to some articles, the email might
        //still show up in "All Mail", but I might be OK with that. Removing
        //for now, because doing this is super slow.
        /*
		if ($this->external_account->server == "imap.gmail.com")
		{
			echo "HIT GMAIL DELETE\n";
			//GMail has this annoying thing where their trash bin is
			//called "Trash" in the U.S., and "bin" in the UK. It's also
			//contained in the "[Google Mail]" folder, or the [Gmail] folder
			//depending on your version of GMail. So lets just search for Trash
			//and hope we find the right mailbox.
			$trashFolder = $this->external_account->getTrashBinName();

			imap_mail_move($this->conn,
				$this->message_id . ":". $this->message_id,
				$trashFolder);
			print_r(imap_errors());
			echo "+++ Moving message: ". $this->message_id ." => ". $trashFolder ."\n";
		}
		*/
    }

	public function getHeaders()
	{
		$headers = imap_headerinfo($this->conn, $this->message_id);
		$this->cleanHeaders($headers);
		return $headers;
	}

	private function cleanHeaders(&$headers)
	{
		//make sure headers are not encoded
		foreach ($headers as $key => $value)
		{
			if (is_array($value))
			{
				if (is_array($headers))
					$this->cleanHeaders($headers[$key]);
				if (is_object($headers))
					$this->cleanHeaders($headers->$key);

				continue;
			}

			if (is_object($value))
			{
				if (is_array($headers))
					$this->cleanHeaders($headers[$key]);
				if (is_object($headers))
					$this->cleanHeaders($headers->$key);
				continue;
			}

			$value = trim($value);
			if (isset($value{0}) && $value{0} == "=")
			{
				$value = str_replace("?", "", utf8_decode(
					iconv_mime_decode($value, 2, 'UTF-8')
				));
			}
		    $value = utf8_encode($value);

			if (is_array($headers))
				$headers[$key] = $value;
			if (is_object($headers))
				$headers->$key = $value;
		}
	}
}
