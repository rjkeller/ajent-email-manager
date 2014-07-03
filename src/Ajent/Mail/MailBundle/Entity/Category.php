<?php
namespace Ajent\Mail\MailBundle\Entity;

use Oranges\MongoDbBundle\Helper\DatabaseModel;
use Oranges\framework\BuildOptions;
use Oranges\sql\Database;
use Oranges\misc\WgTextTools;
use Oranges\UserBundle\Helper\User;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Doctrine\ORM\Mapping as ORM;

/**
 A category for email messages as specified by the user.
 
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class Category extends DatabaseModel
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
//	protected $name;

	/** @ORM\Column(type="integer") */
//	protected $num_new_messages;

	/** @ORM\Column(type="integer") */
//	protected $num_messages;

	protected function getTable()
	{
		return "email_categories";
	}

	public function refreshCategoryCache()
	{
		$db = MongoDb::getDatabase();
		
		$this->num_new_messages = $db->email_messages->find(array(
			"category_id" => $this->id,
			"folder" => array('$ne' => 'trash'),
			'is_read' => false
			))->count();

		$this->num_messages = $db->email_messages->find(array(
			"category_id" => $this->id,
			"folder" => array('$ne' => 'trash')
			))->count();

		$this->save();
	}

	public function loadGeneralCategory($user_id = null)
	{
		if ($user_id == null)
			$user_id = SessionManager::$user->id;

		$loadData = array(
			"user_id" => $user_id,
			"name" => BuildOptions::$get['MailBundle']['InboxName']);

		if (!parent::loadQuery($loadData, true))
		{
			$this->user_id = $user_id;
			$this->name = BuildOptions::$get['MailBundle']['InboxName'];
			$this->create();
		}
		return true;
	}


	public function loadCategoryName($name)
	{
		return parent::loadQuery(
			array("user_id" => SessionManager::$user->id,
			 	"name" => $name));
	}

	public function tryLoadCategoryName($name)
	{
		return parent::loadQuery(
			array("user_id" => SessionManager::$user->id,
			 	"name" => $name)
			, true);
	}

	public function createCategory($name)
	{
		$this->id = "";
		$this->user_id = SessionManager::$user->id;
		$this->name = $name;
		return $this->create();
	}

	public function getName()
	{
		return ucwords(WgTextTools::truncate($this->name, 17));
	}

	public function delete()
	{
		$generalCategory = new Category();
		$generalCategory->loadGeneralCategory();

		$db = MongoDb::getDatabase();
		$db->vendors->update(
			array("category_id" => $this->id),
			array("category_id" => $generalCategory->id)
			);

		parent::delete();
	}

	private $messages = null;
	public function getMessages()
	{
		if ($this->messages == null)
		{
			$this->messages = MongoDb::modelQuery(
				$db->email_messages->find(array(
					"category_id" => $this->id
				)),
				"Ajent\Mail\MailBundle\Entity\EmailMessage");
		}

		return $this->messages;
	}

	/** Returns how many messages are in this category. */
	public function getNumMessages()
	{
		return number_format($this->num_messages);
	}

	public function getCategoryClass()
	{
		switch ($this->name)
		{
		case 'Blogs':
			return 'category_blogs';
		case 'Clothing':
		case 'Fashion':
			return 'category_fashion';
		case 'Daily Deals':
			return 'category_daily_deals';
		case 'Travel':
			return 'category_travel';
		case 'Home':
			return 'category_home';
		case 'Food':
			return 'category_food';
		case 'Sports':
			return 'category_sports';
		case 'Receipts':
			return 'category_receipts';
		case 'Social Networking':
			return 'category_social_networking';
		case 'Miscellaneous':
			return 'category_misc';
		case 'Shopping':
			return 'category_shopping';
		default:
			return 'category_generic';
		}
	}
}
