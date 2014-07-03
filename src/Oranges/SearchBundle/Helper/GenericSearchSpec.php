<?php
namespace Oranges\SearchBundle\Helper;

class GenericSearchResultsSpec extends SearchSpec
{

	public function __construct($table)
	{
		$this->tableName = $table;
	}
}
