<?php
namespace Oranges\searchResults;

use Oranges\sql\Database;
use Oranges\sql\SqlUtility;
use Oranges\searchResults\SearchResultsSpec;
use Oranges\frontend\WgSmarty;
use Oranges\forms\ListGenerator;
use Oranges\errorHandling\ForceError;

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
class SearchResults
{
	private $spec;

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
	 @paaram $spec - The SearchResultsSpec object to use for this query.
	*/
	public function __construct(SearchResultsSpec $spec)
	{
		$this->spec = $spec;
		$dbh = $this->getDatabase();

		//tweak the spec
		if ($this->spec->show != null)
			$this->spec->show['Show'] = "";
		if ($this->spec->sort != null)
			$this->spec->sort['Sort'] = "";
		if ($this->spec->actions != null)
			$this->spec->actions['Actions'] = "";

		//generate the pieces to the query
		$orderby;
		$filter;
		$numResults;
		$where = $spec->whereClause;
		$tableName = $spec->tableName;

		$filter = $this->isParamSet("filter");
		if ($filter != null) {
			$querystuff = $spec->filter($filter);
			if ($querystuff != null)
			{
				if ($where != null)
					$where .= " AND $querystuff";
				else
					$where = $querystuff;
			}
		}

		$orderby = $this->isParamSet("orderby");
		if ($orderby != null && in_array($orderby, $spec->sort) === false)
			$orderby = null;
		if ($orderby == null && isset($spec->defaultOrderBy) && $spec->defaultOrderBy != null)
			$orderby = $spec->defaultOrderBy;

		$numResults = $this->isParamSet("numResults");
		if ($spec->forceNumResults != -1)
			$numResults = $spec->forceNumResults;
		else if (!ctype_digit($numResults))
			$numResults = 25;

		$q = $this->isParamSet("q");
		if ($q != null && $q != "Search" && $q != "                              Search") {
			ForceError::$inst->checkStr($q);

			$querystuff = $spec->query($q);
			if ($querystuff != null)
			{
				if ($where != null)
					$where .= " AND $querystuff";
				else
					$where = $querystuff;
			}
		}

		//put the query together.
		$query = "FROM $spec->tableName";
		if ($where != null)
			$query .= " WHERE $where";
		if ($orderby != null)
			$query .= " ORDER BY $orderby";

		//generate the total number of results possible.
		$q = $dbh->fetchArray("SELECT COUNT(*) ".$query);
		$this->total = $q[0];

		//generate the start number
		$page = 0;
		if (isset($_GET['page']))
		{
			ForceError::$inst->checkInt($_GET['page']);
			$page = $_GET['page'];
		}
		$this->startNumber = $numResults * $page;

		//generate the end number
		$this->sql = "SELECT * $query LIMIT $this->startNumber,$numResults";
		$this->query = $dbh->fetchAll($this->sql);

		$this->endNumber = $this->startNumber + sizeof($this->query);

		$this->hasPrev = $this->startNumber != 0;
		$this->hasNext = (sizeof($this->query) + $page*$numResults) <
			$this->total;

		$currentPageNumber = $page;

		//fun trick to generate link URLs by updating the GET parameters with
		//the new link location, and then resetting it back again.
		$_GET['page'] = $currentPageNumber - 1;
		$this->prevLink = $this->getParams();

		$_GET['page'] = $currentPageNumber + 1;
		$this->nextLink = $this->getParams();

		//because page numbers are off by 1, we need to readjust the startNumber
		//(and we can't do it earlier or it'll throw off the code above)
		if ($this->endNumber != 0)
			$this->startNumber++;
	}

	protected function getDatabase()
	{
		return Database::getDatabase();
	}

	/**
	 Returns a mysqli Query object for the results of this query.
	 */
	public function getSqlQuery()
	{
		return $this->query;
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
	private function isParamSet($str)
	{
		if (!empty($_GET[$str]))
			return $_GET[$str];
		if (!empty($_POST[$str]))
			return $_POST[$str];
		return null;
	}

	private function getParams()
	{
		$q = "?";
		$isFirst = true;
		foreach ($_GET as $key => $value)
		{
			if ($isFirst)
				$isFirst = false;
			else
				$q .= "&";
			$q .= "$key=$value";
		}
		return $q;
	}
}
