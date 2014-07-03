<?php
namespace Oranges\SearchBundle\Helper;

use Oranges\sql\Database;
use Oranges\sql\SqlModelIterator;
use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\frontend\WgSmarty;
use Oranges\forms\ListGenerator;
use Oranges\errorHandling\ForceError;

use Oranges\SearchBundle\Entity\SearchParams;
use Oranges\SearchBundle\Entity\SearchQuery;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\MongoDbBundle\Helper\MongoDb;
use Oranges\MongoDbBundle\Helper\ModelIterator;

/**
 This class provides a convenient interface for displaying
 a large number of items that can be searched through in
 search engine format.

 Reserved GET/POST parameters:
   - filter
   - orderby
   - numResults
   - page
   - q
   - others maybe?
 */
class SearchEngine
{
	public $spec;

	private $hasNext;
	private $hasPrev;
	private $startNumber;
	private $endNumber;
	private $total;

	private $nextLink;
	private $prevLink;

	public $sql;
	private $query;

	/**
	 The filter option for the search results.
	*/
	protected $filter;

	/**
	 How to order the search results;
	*/
	protected $orderBy;
	/**
	 How many results to show.
	*/
	protected $numResults;
	/**
	 A search query to run
	*/
	protected $q;

	/**
	 Which page of search results we're on
	*/
	protected $page = 0;

	public function loadGetParameters()
	{
		$this->loadFromArray($_GET);
	}

	public function loadPostParameters()
	{
		$this->loadFromArray($_POST);
	}

	/**
	 @paaram $spec - The SearchResultsSpec object to use for this query.
	*/
	public function init(SearchSpec $spec)
	{
		$this->spec = $spec;
		$db = MongoDb::getDatabase();

		//Adding some default options to the spec.
		if ($spec->show != null)
			$spec->show['Show'] = "";
		if ($spec->sort != null)
			$spec->sort['Sort'] = "";
		if ($spec->actions != null)
			$spec->actions['Actions'] = "";

        //if this query needs to load more results
        if ($spec->loadResultsAtRuntime)
        {
            $searchQuery = new SearchQuery();
            if (!$searchQuery->loadQuery($spec->tableName))
            {
                $searchQuery->user_id = SessionManager::$user->id;
                $searchQuery->query_name = $spec->tableName;
                $searchQuery->load_more_results = true;
                $searchQuery->page_num = 0;
                $searchQuery->create();
            }

            if ($searchQuery->load_more_results &&
				$this->page >= $searchQuery->page_num)
            {
                $spec->loadMoreResults();
            }
        }

		//generate the pieces to the query
		$orderby;
		$filter;
		$numResults;
		$where = $spec->whereClause;
		$tableName = $spec->tableName;

		$filter = $this->filter;
		if ($filter != null) {
			$querystuff = $spec->filter($filter);
			if ($querystuff != null)
			{
				$where = $querystuff;
			}
		}

		$orderby = null;
		if ($orderby != null && in_array($orderby, $spec->sort) === false)
			$orderby = array();

		if ($orderby == null &&
			isset($spec->defaultOrderBy) &&
			$spec->defaultOrderBy != null)
		{
			$orderby = $spec->defaultOrderBy;
		}

		$numResults = $this->numResults;
		if ($spec->forceNumResults != -1)
			$numResults = $spec->forceNumResults;
		else if (!ctype_digit($numResults))
			$numResults = 25;

		$q = $this->q;
		if ($q != null && $q != "Search" && $q != "                              Search") {
			ForceError::$inst->checkStr($q);

			$querystuff = $spec->query($q);
			if ($querystuff != null)
			{
				$where = array_merge($querystuff, $where);
			}
		}

		$collection = $db->selectCollection($spec->tableName);

		//generate the total number of results possible.
//		echo "QUERY: $spec->tableName ->find( ".print_r($where,true) ." ) ->sort ( ". print_r($orderby, true) ." ) -> limit( ". print_r($numResults, true) ." ) -> skip ( $this->startNumber )\n";die();
		if (!is_array($orderby))
			$orderby = array($orderby => 1);
		$this->total = $collection->find($where)
			->sort($orderby)
			->count();

		//generate the start number
		$page = $this->page;
		$this->startNumber = $numResults * $page;

		//generate the end number
		$this->query = $collection
			->find($where)
			->sort($orderby)
			->limit($numResults)->skip($this->startNumber)
			;


		$this->endNumber = $this->startNumber + $this->query->count(true);

		$this->hasPrev = $this->startNumber != 0;
		$this->hasNext = ($this->query->count(true) + $page*$numResults) <
			$this->total;

		$currentPageNumber = $page;

		//fun trick to generate link URLs by updating the GET parameters with
		//the new link location, and then resetting it back again.
		$this->prevLink = $this->getParams($currentPageNumber - 1);

		$this->nextLink = $this->getParams($currentPageNumber + 1);

		//because page numbers are off by 1, we need to readjust the startNumber
		//(and we can't do it earlier or it'll throw off the code above)
		if ($this->endNumber != 0)
			$this->startNumber++;
	}

	/**
	 Returns a MongoCursor for the results of this query.
	 */
	public function getSqlQuery()
	{
		return new ModelIterator(
			$this->query,
			$this->spec->db_entity);
	}

	/**
	 Returns the item number where the query starts off.
	 */
	public function min()
	{
		return $this->startNumber;
	}

	/**
	 Returns the item number where the query ends.
	 */
	public function max()
	{
		return $this->endNumber;
	}

	public function total()
	{
		return $this->total;
	}

	/**
	 Returns whether or not a user can click "Previous" to see the previous
	 search results page.
	 */
	public function hasPrev() //tableGenerator->prevLink
	{
		return $this->hasPrev;
	}

	/**
	 Returns whether or not a user can click "Next" to see the previous
	 search results page.
	*/
	public function hasNext()
	{
		return $this->hasNext;
	}

	public function nextLink() //tableGenerator->nextLink
	{
		return $this->nextLink;
	}

	/**
	 Prints the URL that will go to the previous results page in the query.
	 */
	public function prevLink()
	{
		return $this->prevLink;
	}

	//-------------------- PRIVATE FUNCTIONS --------------------//
	protected function loadFromArray($array)
	{
		$this->filter = $this->getArrayEntry($array, "filter");
		$this->orderBy = $this->getArrayEntry($array, "orderby");
		$this->numResults = $this->getArrayEntry($array, "numResults");
		$this->q = $this->getArrayEntry($array, "q");
		$this->page = $this->getArrayEntry($array, "page");
	}

	private function getArrayEntry(array $array, $str)
	{
		if (isset($array[$str]))
			return $array[$str];
		return null;		
	}


	private function getParams($page)
	{
		$params = "";
		foreach ($_GET as $key => $value)
		{
			if ($key != "page" &&
				$key != "filter" &&
				$key != "orderby" &&
				$key != "numResults" &&
				$key != "q")
			{
				$params .= $key . "=". $value."&";
			}
		}

		return "?". $params ."page=". $page .
			"&filter=". $this->filter .
			"&orderby=". $this->orderBy .
			"&numResults=". $this->numResults .
			"&q=". $this->q;
	}
}
