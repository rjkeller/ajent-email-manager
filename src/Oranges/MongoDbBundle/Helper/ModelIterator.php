<?php
namespace Oranges\MongoDbBundle\Helper;

/**
 The goal of this class is to privde a easy, efficient method of providing
 MySQL queries to Smarty. Using this class, you can still utilize the smarty
 for-each loop, not use up a lot of memory involved with storing query data
 in arrays, and get additional eventing flexibility by passing in a function
 to parse query data.

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
class ModelIterator implements \Iterator,\Countable
{
	private $query;
	private $modelClass;

	public function __construct($query, $modelClass)
	{
		$this->query = $query;
		$this->modelClass = $modelClass;
	}

	public function rewind()
	{
		$this->query->rewind();
	}

	public function current()
	{
		$ret = $this->query->current();

		if (!$ret)
			return $ret;

		$obj = new $this->modelClass;
		$obj->fill($ret);
		return $obj;
	}

	public function key()
	{
		return $this->query->key();
	}

	public function next()
	{
		return $this->query->next();
	}

	public function valid()
	{
		return $this->query->valid();
	}

	public function size()
	{
		return $this->query->count();
	}

	public function count()
	{
		return $this->query->count();
	}

	public function getArray()
	{
		$array = iterator_to_array($this->query);
		foreach ($array as $key => $value)
		{
			$obj = new $this->modelClass;
			$obj->fill($value);
			$array[$key] = $obj;
		}
		return $array;
	}
}
