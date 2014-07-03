<?php
namespace AjentApps\Social\SocialPostsBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

class ProfileSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "social_user_profiles";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"Name" => "name");
	public $actions = null;

	public $showLetters = false;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("name", "about_me", "occupation");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy = "name";

	public $whereClause;

	public $db_entity = "AjentApps\Social\SocialPostsBundle\Entity\UserProfile";

	public $template = "SocialBundle:query:friends.twig.html";

	public function __construct()
	{
		$this->whereClause = "is_deleted = FALSE";
	}

	public function onlyShowMyFriends()
	{
		$db = MongoDb::getDatabase();
		$q = $db->social_friends->find(array(
			"user_id" => SessionManager::$user->id
			));

		$vendorIds = array();
		foreach ($q as $sf)
		{
			$vendorIds['$or'][] = array("user_id" => $sf['friend_user_id']);
		}

		$this->whereClause = $vendorIds;
	}
}
