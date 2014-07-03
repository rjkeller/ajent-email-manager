<?php
namespace Ajent\AlertBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MasterContainer;
use Oranges\misc\WgTextTools;
use Oranges\misc\KDateModifier;
use Oranges\sql\Database;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Entity\VendorEmailMessage;
use Ajent\Vendor\VendorBundle\Entity\Vendor;
use Ajent\Mail\MailBundle\Entity\Category;

/**
 * Ajent email alert.
 * 
 * @author R.J. Keller <rjkeller@pixonite.com>
 */
class Alert extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
//	protected $id;

	/** @ORM\Column(type="integer") */
//	protected $user_id;

	/** @ORM\Column(type="integer") */
//	protected $message_id;

	/** @ORM\Column(type="string") */
//	protected $type;

	/** @ORM\Column(type="string") */
//	protected $message;

	/** @ORM\Column(type="integer", length="11") */
//	protected $creation_date;

	/** @ORM\Column(type="integer", length="11") */
//	protected $expiration_date;

	/** @ORM\Column(type="boolean") */
//	protected $is_triggered = false;

	/** @ORM\Column(type="boolean") */
//	protected $is_invisible = false;

	/** @ORM\Column(type="integer", length="11") */
//	protected $vendor_id;


	protected function getTable()
	{
		return "email_alerts";
	}

	public function __construct()
	{
		parent::__construct();
		$this->is_invisible = false;
		$this->is_triggered = false;
		$this->creation_date = time();
	}

	public function getVendorId()
	{
		return Database::scalarQuery("
			SELECT
				vendor_id
			FROM
				email_messages
			WHERE
				id = '". $this->message_id ."'
			");
	}

	public function getExpirationDate()
	{
		$date = new \DateTime("now",
					new \DateTimeZone("GMT"));
		$date->setTimestamp($this->expiration_date);

		return KDateModifier::getTimeAgo($date, " until alert");
//		return date("F j g:i a", strtotime($this->expiration_date));
	}

	public function getCreationDate()
	{
		return date("Y-m-d H:i:s", $this->creation_date);
	}

	private $email_message = null;
	public function getMessage()
	{
		if ($this->email_message == null)
		{
			$this->email_message = new VendorEmailMessage();
			$this->email_message->load($this->message_id);
		}
		return $this->email_message;
	}

	private $vendor = null;
	public function getVendor()
	{
		if ($this->vendor == null)
		{
			$this->vendor = new Vendor();
			$this->vendor->load($this->getMessage()->vendor_id);
		}
		return $this->vendor;
	}

	public function getViewEmailUrl()
	{
		$router = MasterContainer::get("router");
		$message = $this->getMessage();
		
		$category = new Category();
		$category->load($message->category_id);

		$url = $router->generate(
			'VendorBundleViewCategoryVendor',
			array('vendor_id' => $message->vendor_id,
			 	'category_name' => $category->name));
		return $url . "?message_id=". $message->_id;
	}

	/**
	 Changes the expiration date to 24 hours from right now. Also, if this
	 alert was triggered, the trigger tag is removed so the Alert will pop up
	 again next time it expires.
	*/
	public function remindMeLater()
	{
		$date = new \DateTime("now",
					new \DateTimeZone("GMT"));
		$date->modify("+24 hours");

		$this->expiration_date = $date->getTimestamp();
		$this->is_triggered = false;
		$this->save();
	}
}
