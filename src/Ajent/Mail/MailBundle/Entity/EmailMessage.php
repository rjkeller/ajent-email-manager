<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MasterContainer;
use Oranges\framework\BuildOptions;
use Oranges\misc\WgTextTools;
use Oranges\misc\KDateModifier;
use Oranges\sql\Database;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\LoggingBundle\Helper\Logger;

use Doctrine\ORM\Mapping as ORM;

use Pixonite\TagCloudBundle\Helper\TagManager;

use Ajent\Mail\ExternalMailBundle\Entity\ExternalAccount;
use Ajent\Mail\MailBundle\Event\MailEvent;
use Ajent\Mail\MailBundle\MailEvents;

use Ajent\Vendor\VendorBundle\Entity\Vendor;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class EmailMessage extends DatabaseModel
{

	/**
	This is the date of when the email should be displayed to the user. So if
	the email shouldn't be shown until in the future, you can set this date in
	the future to hide the email until later (if it is determined to not be
	relevant until later).

	@ORM\Column(type="integer", length="11")
	*/
//	protected $date;

	/**
	This is the date the email was created. This is ONLY set when the email is
	created. It is READ-ONLY after the email object has been created. Any
	future date skews should be done on $this->date.
	
	@ORM\Column(type="integer", length="11")
	*/
//	protected $creation_date;

	public function __construct()
	{
		parent::__construct();

		$this->type = "standard";
		$this->type_id = "standard";
		$this->folder = "inbox";
		$this->category_id = -1;
		$this->is_public = false;
		$this->is_invisible = false;
		$this->is_external_message = false;
		$this->body_type = "";
		$this->body_file_id = "";
		$this->mime_file_id = "";
		$this->vendor_id = "";
		$this->tag_id = 0;
		$this->has_attachments = false;
		$this->is_read = false;
		$this->date = time();
		$this->creation_date = time();
	}

	protected function getTable()
	{
		return "email_messages";
	}

	public function hasAlert()
	{
		$db = MongoDb::getDatabase();
		$alerts = $db->email_alerts->find(array(
				"expiration_date" => array('$gt' => time()),
				"is_invisible" => false,
				'message_id' => $this->id,
				"user_id" => SessionManager::$user->id))
				->limit(1)
				->count();
		return $alerts > 0;
	}

	public function getCreationDate()
	{
		$date = new \DateTime("now",
					new \DateTimeZone("GMT"));
		$date->setTimestamp($this->creation_date);

		//convert to eastern time to print.
		//XXX: Add timezone conversion code here?
		$date->setTimezone(new \DateTimeZone("America/Los_Angeles"));

		return $date->format("M d, Y");
	}

	public function printMessageBody()
	{
		if ($this->body_type == "text/plain")
			echo "<pre>";

		$this->printRawMessageBody();

		if ($this->body_type == "text/plain")
			echo "</pre>";
	}

	public function printRawMessageBody()
	{
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();

		$file = $grid->get(new \MongoId($this->body_file_id));
		$stream = $file->getResource();
		while (!feof($stream))
			echo fread($stream, 8192);
	}

	private $vendor = null;
	public function getVendor()
	{
		if ($this->vendor == null)
		{
			$this->vendor = new Vendor();
			$this->vendor->load($this->vendor_id);
		}
		return $this->vendor;
	}

	private $category = null;
	public function getCategory()
	{
		if ($this->category == null)
		{
			$this->category = new Category();
			$this->category->load($this->category_id);
		}
		return $this->category;
	}

	/**
	 Sets the message body equal to the file name passed in.
	
	 Note that the message body will be saved to the database immediately.
	*/
	public function setMessageBody($file_name)
	{
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();

		$metadata = array();
		if ($this->body_file_id != "")
		{
			$grid->delete(new \MongoId($this->body_file_id));
			$metadata = array("_id" => $this->body_file_id);
		}
		$grid->storeFile($file_name, $metadata, array("safe" => true));
	}

	public function getFromEmail()
	{
		if ($this->from_name == "")
			return WgTextTools::truncate($this->from_email, 15);
		else
			return WgTextTools::truncate($this->from_name, 15);
	}

	public function getTruncatedSubject()
	{
		return WgTextTools::truncate($this->subject, 25);
		
	}

	/**
	 Takes the value in $this->from_address and splits it out to the name and
	 email address of the sender, and stores that information in
	 $this->from_name and $this->from_email respectively.
	*/
	public function parseFromEmail()
	{
		$parseString = $this->from_address;
		$parseString = str_replace('"', '', $parseString);
		$parseString = str_replace('>', '', $parseString);
		$parseString = str_replace(')', '', $parseString);

		$data = explode("<", $parseString);
		if (count($data) > 1)
		{
			$this->from_name = trim($data[0]);
			$this->from_email = trim($data[1]);
		}
		else
		{
			$data = explode("(", $parseString);
			if (count($data) <= 1)
			{
				$this->from_name = "";
				$this->from_email = trim($parseString);
			}
			else
			{
				$this->from_name = trim($data[1]);
				$this->from_email = trim($data[0]);
			}
		}

        //if we have a mime encoding in the from box, then decode it.
        if (isset($this->from_name{0}) && $this->from_name{0} == "=")
            $this->from_name = imap_mime_header_decode($this->from_name);
        if (isset($this->from_email{0}) && $this->from_email{0} == "=")
            $this->from_email = imap_mime_header_decode($this->from_email);
	}

	protected function __single_obj_create()
	{
	    //if the "From Email" address is not set, then we'll automatically
	    //define it using the parseFromEmail method.
		if (empty($this->from_email))
		{
			$this->parseFromEmail();
		}

		if (empty($this->creation_date))
			$this->creation_date = $this->date;

		Logger::log("+++ Email Creation: Creating message object...", "mail");

		$out = parent::__single_obj_create();


		//if the creation was successful, refresh various caches.
		if ($out)
		{
			//turning this off for now.
//			TagManager::refreshTagCache();
			//XXXrj: Move this to the event manager
//			Contact::refreshContactCache($this->recipient_user_id);
		}

		//refresh in case there are any table modifications.
		$this->load($this->id);

		return $out;
	}

	public function save()
	{
		//notify any listening bundles that an email has been saved
		$event = new MailEvent($this, $this->__isChanged);
		$dispatcher = MasterContainer::get("event_dispatcher");
		$dispatcher->dispatch(MailEvents::onEmailMessageSave, $event);

		parent::save();

		//notify any listening bundles that an email has been saved
		$dispatcher->dispatch(MailEvents::onEmailMessagePostSave, $event);
	}

	/** Moves the email to the trash bin. */
	public function delete()
	{
		$this->folder = "trash";
		$this->save();

		//notify any listening bundles that an email has been moved to the
		//trash bin
		$event = new MailEvent($this);
		$dispatcher = MasterContainer::get("event_dispatcher");
		$dispatcher->dispatch(MailEvents::onEmailMessageTrash, $event);
	}

	/** Does a soft-delete of the email from the trash bin. */
	public function removeFromTrash()
	{
		$this->is_invisible = true;
		$this->save();

		//notify any listening bundles that an email deletion has taken place.
		$event = new MailEvent($this);
		$dispatcher = MasterContainer::get("event_dispatcher");
		$dispatcher->dispatch(MailEvents::onEmailMessageDelete, $event);
	}

	/** Permanently deletes this email message.. */
	public function hardDelete()
	{
		//notify any listening bundles that an email deletion has taken place.
		$event = new MailEvent($this);
		$dispatcher = MasterContainer::get("event_dispatcher");
		$dispatcher->dispatch(MailEvents::onEmailMessageDelete, $event);

		//XXXRJ: should we unlink the email message file here? Looks like we're
		//just letting it leak.

		parent::delete();
	}

	/**
	 Renders an email that has already been rendered.
	*/
	public function rerenderEmail()
	{
		//upload the MIME temp file to GridFS in case we need to re-do the
		//rendering if there's a bug or something.
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();

		$file =
			$grid->get(new \MongoId($this->mime_file_id));

		$temp_file_name =  uniqid(rand(), true);
		$tmp_file = tempnam(sys_get_temp_dir(), $temp_file_name);

		$f = fopen($tmp_file, "w");
		$stream = $file->getResource();
		while (!feof($stream))
			fwrite($f, fread($stream, 8192));
		fclose($f);

		$account = new EmailAccount();
		$account->loadUser($this->recipient_user_id);

		$rawHeaders = $this->renderFromMaildirFile($account,
				$tmp_file);

		unlink($tmp_file);
		$this->save();
	}

	/**
	 Creates an EmailMessage object in MySQL equal to data mined from the
	 $maildir_email_file passed in. Most of the fields of this class will be
	 set by calling this method (see below for exceptions).

	 The expected usage of this method is to import a maildir file that doesn't
	 already have an object in MySQL associated with it (which is the case for
	 newly received email messages). If you already have this maildir file
	 loaded into an email message in MySQL, you should be able to just load()
	 that email and use the methods to interact with the maildir data (instead
	 of using this method).

	 NOTE: is_external_message will not be set by this method. Please set it
	 before calling.

	 @param $emailAccount - The email account that the Maildir email message
	  belongs to.
	 @param $maildir_email_file - The full path to the maildir file.
	 @throws Exception if the email file passed in doesn't have proper
	  permissions. Shouldn't happen unless the server is misconfigured.
	*/
	public function loadFromMaildirFile(EmailAccount $emailAccount, $maildir_email_file)
	{
		//upload the MIME temp file to GridFS in case we need to re-do the
		//rendering if there's a bug or something.
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();
		$metadata = array("date" => new \MongoDate());
		$grid_id =
			$grid->storeFile($maildir_email_file, $metadata, array("safe" => true));
		$this->mime_file_id = $grid_id->__toString();


		$rawHeaders = $this->renderFromMaildirFile($emailAccount,
				$maildir_email_file);
	}

	/**
	 Renders a Maildir file and stores its contents in $rendered_file_id. This
	 method will also set the following fields:
	  $this->body_file_id
	  $this->body_type
	
	@param $maildir_email_file - The file, in Maildir format, to render the
	  email message for.
	@param $rendered_file_id - The file ID to store the rendered HTML. If not
	  passed in, a new file will be created and set to the body_file_id field.
	*/
	public function renderFromMaildirFile(EmailAccount $emailAccount, $maildir_email_file, $rendered_file_id = null)
	{
		$db = MongoDb::getDatabase();
		$grid = $db->getGridFS();

		$html_file_id = $rendered_file_id;

		$maildirFileHandle = fopen($maildir_email_file, "r");
		//will open this file for writing later.
		$htmlFileHandle = null;

		$gridfs_file_name =  "mail_". uniqid(rand(), true);
		$html_file = tempnam(sys_get_temp_dir(), $gridfs_file_name);


		$previousLine = '';
		$htmlBody = '';
		$type = "";
		$delimiter = '';
		$detectedContentType = false;
		$waitingForContentStart = true;
		$hasContentEverBeenLoaded = false;

		$isHeaders = true;
		$rawHeaders = array();
		$currentHeader = '';

		$is_base64_encode = false;

		$isStart = true;

		while (($line = fgets($maildirFileHandle)) !== false)
		{
			if ($isStart && self::isNewLine($line))
			{
				continue;
			}
			$isStart = false;
			//---------------- BEGIN PARSING OF EMAIL HEADERS ----------------//
			if (self::isNewLine($line))
			{
				$isHeaders = false;
			}

			if ($isHeaders)
			{
				//is this line the start of a new header?
				if (preg_match('/^[A-Za-z]/', $line)) // start of new header
				{
					preg_match('/([^:]+): ?(.*)$/', $line, $matches);
					if (count($matches) > 0)
					{
						$currentHeader = strtolower($matches[1]);
						$rawHeaders[$currentHeader] = $matches[2];
					}
				}
				else // more lines related to the current header
				{
					if (!isset($rawHeaders[$currentHeader]))
						$rawHeaders[$currentHeader] = "";
					$rawHeaders[$currentHeader] .= substr($line, 1);
				}
			}



			//---------------- BEGIN PARSING OF EMAIL BODY ----------------//
			if (preg_match('/^MIME-version:/i', $line, $matches))
			{
				continue;
			}
			if (preg_match('/^Content-Type: ?text\/html/i', $line, $matches))
			{
				//NOTE: this has the dual effect of truncating the file when we
				//open this file. The idea is that if an email comes in both
				//plain/text and text/html formats, then we bias the HTML. In
				//some scenarios the plain/text content comes first, so with
				//re-opening this file, we basically wipe out the file contents
				//and start over.
				if ($detectedContentType)
					fclose($htmlFileHandle);
				$htmlFileHandle = fopen($html_file, "w");


				$detectedContentType = true;
				$waitingForContentStart = true;
				$delimiter = trim($previousLine);
				$type = "text/html";
			}
			else if (preg_match('/^Content-Type: ?text\/plain/i', $line, $matches))
			{
				//NOTE: this has the dual effect of truncating the file when we
				//open this file. The idea is that if an email comes in both
				//plain/text and text/html formats, then we bias the HTML. In
				//some scenarios the plain/text content comes first, so with
				//re-opening this file, we basically wipe out the file contents
				//and start over.
				if ($detectedContentType)
					fclose($htmlFileHandle);
				$htmlFileHandle = fopen($html_file, "w");

				$detectedContentType = true;
				$waitingForContentStart = true;
				$delimiter = trim($previousLine);
				$type = "text/plain";
			}
			else if (!$hasContentEverBeenLoaded && !$isHeaders && !$detectedContentType)
			{
				//if we can't find a Content-Type, then start off with plain/text.
				$htmlFileHandle = fopen($html_file, "w");

				$detectedContentType = true;
				$waitingForContentStart = false;
				$delimiter = "";
				$type = "text/plain";
			}
			else if ($detectedContentType && $waitingForContentStart)
			{
				if (preg_match('/^Content-Transfer-Encoding: ?base64/i', $line, $matches))
				{
					$is_base64_encode = true;
				}
				if (self::isNewLine($line))
				{
					$waitingForContentStart = false;
				}
			}
			else if ($detectedContentType && !$waitingForContentStart)
			{
				// collecting the actual content until we find the delimiter

				$length = strlen($delimiter);

				$line = trim($line);
				if (!empty($delimiter) && strpos($line, $delimiter) !== false) {	 // found the delimiter
					$detectedContentType = false;
					$waitingForContentStart = true;
					$previousLine = $line;
					$hasContentEverBeenLoaded = true;
					continue;
				}
				//if we hit an equal sign at the end of the line, then trim it off.
				if (substr($line, -1, 1) == "=")
					$line = substr($line, 0, -1);
				else
					$line = $line . "\n";

				if ($type == "text/html")
				{
					$line = str_replace('3D"', '"', $line);
					$line = str_replace('&lt;', '<', $line);
					$line = str_replace('&gt;', '>', $line);
				}
				fwrite($htmlFileHandle, quoted_printable_decode($line));
			}

			$previousLine = $line;
		}

		fclose($maildirFileHandle);
		if ($htmlFileHandle != null)
		    fclose($htmlFileHandle);

		//if the message is base64 encoded, then we need to manually decode it.
		if ($is_base64_encode || (
				isset($rawHeaders["content-transfer-encoding"]) &&
				$rawHeaders["content-transfer-encoding"] == "base64"
				)
			)
		{
			Logger::log("Detected base64 encoding. Decoding now...", "mail");
			file_put_contents($html_file,
				base64_decode(
					str_replace("\n", "", 
						file_get_contents($html_file)
					)
				)
			);
		}

		//make sure headers are not encoded
		foreach ($rawHeaders as $key => $value)
		{
			$value = trim($value);

			$decoder = imap_mime_header_decode($value);
			$rawHeaders[$key] = "";
			foreach ($decoder as $i)
			{
				$rawHeaders[$key] .= iconv_mime_decode($i->text, 2, 'UTF-8');
			}
		}

		//start setting the various email fields
		$this->recipient_user_id = $emailAccount->user_id;
		$this->from_address = $rawHeaders["from"];

		//yeah, i have no idea when this stupid stuff happens, but it seems to
		//pop up from time to time. We'll just throw a blank "To" in there to
		//make it all feel warm and fuzzy.
		if (!isset($rawHeaders["to"]))
			$rawHeaders["to"] = "";
        if (!isset($rawHeaders["subject"]))
			$rawHeaders["subject"] = "";

		$this->to_address = $rawHeaders["to"];
	    $this->subject = $rawHeaders['subject'];
		//making the assumption here that the PHP timezone is set to GMT in
		//app/AppKernel.php.
		$this->date = strtotime($rawHeaders["date"]);

		$metadata = array();
		$id = $grid->storeFile($html_file, $metadata, array("safe" => true));

		$this->body_file_id = $id->__toString();
		$this->body_type = $type;

		unlink($html_file);

		return $rawHeaders;
	}

	/**
	 *
	 * @param string $line
	 * @return boolean
	 */
	private static function isNewLine($line)
	{
		$line = str_replace("\r", '', $line);
		$line = str_replace("\n", '', $line);

		return (strlen($line) === 0);
	}
}
