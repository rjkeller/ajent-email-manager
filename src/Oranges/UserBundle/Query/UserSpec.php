<?php
namespace Oranges\user\Query;

use Oranges\searchResults\SearchResultsSpec;
use Oranges\user\Helper\User;

class UserSpec extends SearchResultsSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName = "user";

	/* Format: title -> column name */
	public $show = array(
		"Reseller" => "Reseller",
		"Customer" => "Customer",
		"Registrar" => "Registrar");
	public $sort = array(
		"Username" => "username",
		"Creation Date" => "creationDate",
		"Email" => "email",
		"Role" => "role",
		"Reseller" => "resellerid");
	public $actions = null;

	public $showLetters = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = array("username", "creationDate", "email", "backupemail", "role");
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null. */
	public $filterColumn = "role";

	public $defaultOrderBy = null;

	/**
	 This parameter contains SQL data that goes after the WHERE statement in a
	 SELECT query. So use this to preload condition data to the query. Please set
	 to null if there is no WHERE clauses you'd like to include.
	 */
	public $whereClause = "isDeleted = FALSE";

	public function __construct()
	{
		if (User::$role == "Reseller")
		{
			$this->whereClause .= " AND resellerid = '". User::$userinfo->resellerid ."' AND id != '". User::$id ."'";
		}
	}
}

?>
