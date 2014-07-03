<?php
namespace Ajent\Mail\MailBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;

use Ajent\Mail\MailBundle\Entity\EmailMessage;

class EmailSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "email_messages";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"Creation Date" => "creation_date");
	public $actions = null;

	public $showLetters = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("from_address", "subject", "date");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy;

	public $whereClause;

	public $forceNumResults = 10;

	public $db_entity = "Ajent\Mail\MailBundle\Entity\EmailMessage";

	public $template = null;

	public $canDelete = true;

	public function __construct($box = "inbox", $category_id = null, $vendor_id = null)
	{
		$this->defaultOrderBy = array("date" => -1);

		$conditions = array();
//		date < '". time() ."' AND

		$conditions['folder'] = $box;
		$conditions['recipient_user_id'] = SessionManager::$user->id;

		if ($category_id != null)
		{
			$conditions['category_id'] = $category_id;
		}

		if ($vendor_id != null)
		{
			$conditions['vendor_id'] = $vendor_id;
		}
		$conditions['is_invisible'] = array('$ne' => true);

		$this->whereClause = $conditions;
	}

	public function delete($item_id)
	{
		$message = new EmailMessage();
		$message->load($item_id);
		
		if ($message->recipient_user_id != SessionManager::$user->id)
			throw new \Exception("Access Denied");

		$message->delete();
	}
}
