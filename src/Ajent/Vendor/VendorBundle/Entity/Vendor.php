<?php
namespace Ajent\Vendor\VendorBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;
use Oranges\RedisBundle\Helper\Redis;
use Doctrine\ORM\Mapping as ORM;

use Ajent\Vendor\VendorBundle\Cache\VendorCategoryCache;
use Ajent\Mail\MailBundle\Entity\Category;
use Ajent\Mail\MailBundle\Cache\CategoryCache;
use Ajent\Vendor\VendorBundle\Event\VendorCategoryMailListener;


/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Vendor extends DatabaseModel
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
//	protected $email_suffix;

	/** @ORM\Column(type="string") */
//	protected $name;

	/** @ORM\Column(type="integer") */
//	protected $category_id;

	/**
	Values "" (blank string), "yearly", "monthly", "weekly"

	@ORM\Column(type="string")
	*/
//	protected $term;

	/**
	 Whether or not the specified vendor emails should be rejected, and the
	 vendor should not show up on the vendors page.

	 @ORM\Column(type="boolean")
	*/
//	protected $is_rejected = false;

	/**
	 Set by the CategoryCache class. This is read-only outside of that class.
	
	 @ORM\Column(type="integer")
	*/
//	protected $has_alert;
	/**
	 Set by the CategoryCache class. This is read-only outside of that class.
	
	 @ORM\Column(type="integer")
	*/
//	protected $num_messages;
	/**
	 Set by the CategoryCache class. This is read-only outside of that class.
	
	 @ORM\Column(type="integer")
	*/
//	protected $num_new_messages;

	/** @ORM\Column(type="boolean") */
//	protected $is_ignored = false;
	/** @ORM\Column(type="boolean") */
//	protected $is_unsubscribed = false;
	/** @ORM\Column(type="boolean") */
//	protected $pendingAddToAjent = false;

	/** @ORM\Column(type="boolean") */
//	protected $is_invisible = false;

	public function __construct()
	{
		parent::__construct();

		$this->is_rejected = false;
		$this->has_alert = false;
		$this->is_ignored = false;
		$this->is_unsubscribed = false;
		$this->pendingAddToAjent = false;
		$this->is_invisible = false;
		$this->term = "";

		$this->num_new_messages = 0;
		$this->num_messages = 0;
	}

	protected function getTable()
	{
		return "vendors";
	}

	public function create()
	{
		//if no category is specified, then set it to the Miscellaneous
		//category.
		if (!isset($this->category_id))
		{
			$c = new Category();
			$c->loadGeneralCategory($this->user_id);
			$this->category_id = $c->id;
		}
		parent::create();
	}

	public function hasAlerts()
	{
		$db = MongoDb::getDatabase();
		$count = $db->email_alerts->find(array(
			"vendor_id" => $this->id
			))->count();
		return $count > 0;
	}

	public function shortName()
	{
		return WgTextTools::truncate($this->name, 15);
	}

	public function getCategory()
	{
		$c = new Category();
		$c->load($this->category_id);
		return $c;
	}

	public function tryLoad($id)
	{
		return parent::loadQuery(array("_id" => $id), true);
	}

	public function tryLoadVendor($user_id, $email)
	{
		$email_suffix = explode('@', $email);
		if (isset($email_suffix[1]))
			$email_suffix = $email_suffix[1];
		else
			throw new \Exception("Invalid email: ". $email ."\n");

		$db = MongoDb::getDatabase();
		$q = MongoDb::modelQuery($db->vendors->find(array(
			"user_id" => $user_id)),
			"Ajent\Vendor\VendorBundle\Entity\Vendor");

		$forceId = null;
		foreach ($q as $vendor)
		{
			//if email ends with vendor suffix, or vice versa
			if (strpos($email, $vendor->email_suffix) !== false ||
				strpos($vendor->email_suffix, $email) !== false)
			{
				parent::load($vendor->id);
				return true;
			}
		}
		return false;
	}

	public function tryLoadEmailSuffix($email_suffix)
	{
		if ($user_id != -1)
			$user_id = SessionManager::$user->id;

		return parent::loadQuery(array(
			"email_suffix" => $email_suffix,
			"user_id" => $user_id
			), true);
	}

	/**
	 Returns whether or not a specific email should be classified as a vendor.
	
	 @param $email_body - The body of the email message.
	 @param $subject - The subject of the email message.
	*/
	public static function isVendorEmail($email_body, $subject)
	{
		//we don't scan "Re:" or "Fwd:" messages.
		$firstThree = substr($subject, 0, 3);
		if ($firstThree == "re:" ||
			$firstThree == "fw:" ||
			substr($subject, 0, 4) == "fwd:")
		{
			return false;
		}

		$email_body = strip_tags($email_body);

		if (strpos($email_body, "subscribe") === false && 
			strpos($email_body, "all rights reserved") === false &&
			strpos($email_body, "click here") === false)
		{
			return false;
		}
		return true;
	}

	public function loadVendorName($name)
	{
		return parent::loadQuery(
			array("user_id" => SessionManager::$user->id,
			 	"name" => $name));
	}

	public static function getDomain($host)
	{
		$data = explode(".", $host);
		$size = sizeof($data);
		return $data[$size-2] . ".". $data[$size-1];
	}

	public function setEmail($email)
	{
		$email_suffix = explode('@', $email);
		$email_suffix = $email_suffix[1];
		
		$this->email_suffix = $email_suffix;
	}

	public function getTruncatedVendorName()
	{
		return WgTextTools::truncate($this->name, 20);
/*
		$returnMe = "";
		$returnMe = imagettfbbox(12, 0, __DIR__."/arial.ttf", $this->name);
		return $returnMe;
*/
	}

	public function save()
	{
		if (isset($this->__isChanged['is_unsubscribed']))
		{
			$db = MongoDb::getDatabase();

			//hide all vendor categories equal to this vendor if unsubscribed
			//(or vice versa if subscribed).
			//XXX: I'm starting to wonder if we should stick an event mechanism
			//	in here. it's getting a little crazy.
			$db->vendor_categories->update(
				array("vendor_id" => $this->id),
				array('$set' => array("is_invisible" => $this->is_unsubscribed)),
				array("multiple" => true)
				);
			$db->email_messages->update(
				array("vendor_id" => $this->id),
				array('$set' => array("is_invisible" => $this->is_unsubscribed)),
				array("multiple" => true)
				);

			$l = new VendorCategoryCache();
			$l->refreshVendorCategoryCache($this->user_id);

			$l = new \Ajent\Mail\MailBundle\Cache\CategoryCache();
			$l->refreshCache($this->user_id);
		}

		parent::save();
	}
}
