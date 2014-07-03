<?php
namespace Oranges\SearchBundle\Helper;

use Oranges\DatabaseModel;

class SearchSpec
{
	public $numResults = array(25, 50, 75, 100, 250);

	public $tableName;

	/* Format: title -> column name */
	public $show = array();
	public $sort = array();
	public $actions = array();

	public $showLetters = true;

	public $sizeSearchBox = -1;
	/** What columns you want the query box to search. Set to null if you
	  want to search all columns. */
	public $searchColumns = null;
	
	/** The column for filter queries. MUST be set if filter queries are
	 supported, otherwise just leave as null.
	
	 If you want to make your own filter($f) function to handle filter
	 queries, then set this to -1 and the SearchResults class will ask
	 that function to return a SQL statement representing the appropriate
	 filter information.
	*/
	public $filterColumn = null;

	/**
	 If you want the search results to return an array of Entities, then
	 specify the entity class here.
	*/
	public $db_entity = null;

	/**
	 This parameter contains SQL data that goes after the WHERE statement in a
	 SELECT query. So use this to preload condition data to the query. Please set
	 to null if there is no WHERE clauses you'd like to include.
	 */
	public $whereClause = null;

	public $forceNumResults = -1;

	public $template = "";

    /**
     The query engine supports the "loading" of additional results at runtime.
     If you wish to enable this feature, set this variable to true. By default
     it is turned off.
     
     When this is set to true, the loadMoreResults() method will be called
     whenever the search system detects that more results need to be loaded.
     */
    public $loadResultsAtRuntime = false;

	public $disableCount = true;

	/**
	 Whether or not an AJAX deleted is supported or not. By default, this is
	 turned off.
	*/
	public $canDelete = false;

	public function delete($item_id) {
		throw new \Exception("Access Denied");
	}

    public function loadMoreResults()
    {   }

	public function query($q)
	{ return SearchResultsUtility::query($this, $q); }

	public function filter($f)
	{ return SearchResultsUtility::filter($this, $f); }
}
