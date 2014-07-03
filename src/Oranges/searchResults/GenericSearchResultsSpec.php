<?php
namespace Oranges\searchResults;

use Oranges\searchResults\SearchResultsSpec;

class GenericSearchResultsSpec extends SearchResultsSpec
{

	public function __construct($table)
	{
		$this->tableName = $table;
	}
}
