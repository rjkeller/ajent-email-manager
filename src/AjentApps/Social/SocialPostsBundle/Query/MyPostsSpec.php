<?php
namespace AjentApps\Social\SocialPostsBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;

use AjentApps\Social\SocialPostsBundle\Entity\UserProfile;

class MyPostsSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "social_wall_posts";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"date" => "date DESC");
	public $actions = null;

	public $showLetters = false;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("message");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy = "date DESC";

	public $whereClause;

	public $db_entity = "AjentApps\Social\SocialPostsBundle\Entity\WallPost";

	public $forceNumResults = 4;

	public $template = "SocialBundle:query:RecentPosts.twig.html";

	/**
	 Searches for wall posts in the specified user's profile.
	*/
	public function __construct(UserProfile $profile)
	{
		$this->whereClause = array("user_id" => $profile->user_id);
	}
}
