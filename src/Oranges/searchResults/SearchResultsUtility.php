<?php
namespace Oranges\searchResults;

class SearchResultsUtility
{
	/**
	 Requires spec definitions of:
	  - $filterColumn
	  - $show
	  */
	public static function filter($spec, $filterOpt)
	{
		if (in_array($filterOpt, $spec->show))
			return "$spec->filterColumn = '$filterOpt'";
		else
			return null;
	}

	public static function query($spec, $q)
	{
		$firsttime = true;
		$where = null;
		foreach ($spec->searchColumns as $i) {
			if ($where != null) {
				$where .= " OR $spec->tableName.$i LIKE '%$q%'";
			} else {
				$where = "";
				if ($firsttime)
					$where = "(";
				$where .= "$spec->tableName.$i LIKE '%$q%'";
			}
			$firsttime = false;
		}
		if ($where == null)
			return null;
		$where .= ")";
		return $where;
	}
}
