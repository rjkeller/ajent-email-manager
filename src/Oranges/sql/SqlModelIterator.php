<?php
namespace Oranges\sql;

/**
 Same as SqlIterator, except instead of passing in a function and object to
 parse each row, a DatabaseModel class name is passed in, that is used instead.

 Each row will be fed into the specified DatabaseModel, and returned.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class SqlModelIterator extends SqlIterator
{
	private $model;

	/**
	 @param $q - The SQL query you would like to run.
	 @param $model - Database model class name (incl namespace also).
	*/
	public function __construct($q, $model)
	{
		parent::__construct($q, "parseRow", $this);
		$this->model = $model;
	}

	public function parseRow($row)
	{
		$cls = new $this->model();
		$cls->fill((array)$row);
		return $cls;
	}


	public function getArray()
	{
		$vals = array();
		foreach ($this as $value)
		{
			$vals[] = $value;
		}
		return $vals;
	}
}
