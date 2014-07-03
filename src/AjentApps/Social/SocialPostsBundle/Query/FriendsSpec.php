<?php
namespace AjentApps\Social\SocialPostsBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

class FriendsSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "social_user_profiles";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"Name" => "name",
		"date" => "date",
		"is_favorite" => "is_favorite ASC,date",
		"comments" => "comments");
	public $actions = null;

	public $showLetters = false;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("name");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy = "name";

	public $whereClause;

	public $db_entity = "AjentApps\Social\SocialPostsBundle\Entity\UserProfile";

	public $template = "SocialBundle:query:friends.twig.html";

	public $label = "My Friends";

	/**
	 Modifies the spec to only show people this user is following.
	*/
	public function onlyPeopleImFollowing()
	{
		$db = MongoDb::getDatabase();
		$q = $db->social_followers->find(array(
			"user_id" => SessionManager::$user->id
			));

		$friends = array();
		foreach ($q as $sf)
		{
			$friends = $sf['friend_user_id'];
		}

		$this->whereClause = array("user_id" => array('$in' => $friends));
	}

	/**
	 Modified the spec to only show people that are following this user.
	*/
	public function onlyPeopleFollowingMe()
	{
		$db = MongoDb::getDatabase();
		$q = $db->social_followers->find(array(
			"friend_user_id" => SessionManager::$user->id
			));

		$friends = array();
		foreach ($q as $sf)
		{
			$friends = $sf['friend_user_id'];
		}

		$this->whereClause = array("user_id" => array('$in' => $friends));
	}

	/**
	 Modified the spec to only show people that are following this user.
	*/
	public function onlyMyFriends(UserProfile $profile)
	{
		$db = MongoDb::getDatabase();
		$q = $db->social_followers->find(array(
			"user_id" => $profile->user_id
			));

		$friends = array();
		foreach ($q as $sf)
		{
			$friends = $sf['friend_user_id'];
		}

		$this->whereClause = array("user_id" => array('$in' => $friends));


		if ($profile->user_id == SessionManager::$user->id)
			$this->label = "My Friends";
		else
			$this->label = $profile->name . " Friends";	
	}

}
