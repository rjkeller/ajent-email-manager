<?php
namespace AjentApps\Social\SocialPostsBundle\Query;

use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\UserBundle\Helper\SessionManager;

class FavoritesSpec extends SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "social_wall_posts";

	/* Format: title -> column name */
	public $show = array();
	public $sort = array(
		"Message" => "message");
	public $actions = null;

	public $showLetters = false;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("name", "about_me", "occupation");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn;

	public $defaultOrderBy = "date";

	public $whereClause;

	public $db_entity = "AjentApps\Social\SocialPostsBundle\Entity\WallPost";

	public $template = "SocialBundle:query:favorites.twig.html";

	public $forceNumResults = 4;

	public $label = "My Favorites";

	public function __construct($user_id = null)
	{
		if ($user_id == null)
			$user_id = SessionManager::$user->id;
		$this->whereClause = array(
			"user_id" => $user_id,
			"is_favorite" => true
		);
	}
}
