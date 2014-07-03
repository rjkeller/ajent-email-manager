<?php
namespace Oranges\SearchBundle\Helper;

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
			return array($spec->filterColumn => $filterOpt);
		else
			return null;
	}

	public static function query($spec, $q)
	{
		$where = array();

		foreach ($spec->searchColumns as $i)
		{
			$where[$i] = "/". $q ."/";
		}

		return $where;
	}
}
