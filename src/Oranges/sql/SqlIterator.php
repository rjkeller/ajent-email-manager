<?php
namespace Oranges\sql;

/**
 The goal of this class is to privde a easy, efficient method of providing
 MySQL queries to Smarty. Using this class, you can still utilize the smarty
 for-each loop, not use up a lot of memory involved with storing query data
 in arrays, and get additional eventing flexibility by passing in a function
 to parse query data.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class SqlIterator implements \Iterator,\Countable
{
	private $q;
	public $i = 0;

	public $num_rows;
	public $obj;

	private $callObject;
	private $callFunction;

	/**
	 Creates a new object. The $func and $obj parameters work like this:
	- Every time a row is hit in query $q, the row is returned. However, if
	  $func and $obj are defined, then it will run $obj->$func($row) and
	  return the result of that instead. This allows you to make changes to
	  the query before you return it if you want to run manipulations.
	
	 @param $q - The SQL query you would like to run.
	 @param $func - The function you want to call to manipulate row data.
	 @param $obj - The object (if applicable) of the function you want to
	   call to maniuplate raw data.
	*/
	public function __construct($q, $func = null, $obj = null)
	{
		$this->q = $q;
		if (is_array($q))
		{
			$this->num_rows = sizeof($q);
			if ($this->num_rows > 0)
				$this->obj = $q[0];
		}
		else
		{
			$this->num_rows = $q->num_rows;
			$this->obj = $q->fetch_object();
		}

		$this->callObject = $obj;
		$this->callFunction = $func;
	}

	public function rewind()
	{
	}

	public function current()
	{
		if ($this->obj != null && $this->callObject != null && $this->callFunction != null)
		{
			$callObject = $this->callObject;
			$callFunction = $this->callFunction;
			return $callObject->$callFunction($this->obj);
		}
		return null;
	}

	public function key()
	{
		return $this->i;
	}

	public function next()
	{
		$this->i++;
		if ($this->i >= $this->num_rows)
			return null;
		if (is_array($this->obj))
			$this->obj = $this->q[$this->i];
		else
			$this->obj = $this->q->fetch_object();
	}

	public function valid()
	{

		if ($this->num_rows <= 0)
			return false;
		if ($this->i >= $this->num_rows)
			return false;
		if (!$this->obj)
			$this->close();
		return $this->obj;
	}

	public function size()
	{
		if (is_array($this->q))
			return sizeof($this->q);
		return $this->q->num_rows;
	}

	public function close()
	{
		if (!is_array($this->q))
			$this->q->close();
	}

	public function count()
	{
		if (is_array($this->q))
			return sizeof($this->q);
		return $this->q->num_rows;
	}

	public function getArray()
	{
		$vals = array();
		while ($this->valid())
		{
			$vals[] = $this->next();
		}
		return $vals;
	}
}
