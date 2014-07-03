<?php
namespace Oranges\sql;

use Oranges\framework\BuildOptions;

use Oranges\errorHandling\UnrecoverableSystemException;

use Oranges\errorHandling\InternalException;

/**
 WordGrab Database Engine

 This engine is designed to help manage MySQL database connections, select appropriate
 connections for different environments, and provide database error handling.

 @author R.J. Keller <rjkeller@wordgrab.com>
*/
class Database
{
	/** Set to true if we're running a SQL query. This is used for SQL recursion detection. */
	static private $isRunning = false;

	/** Array of open database connections. */
	static private $dbh;

	/**
	 Returns the database name requested. This is generally not an exact database name,
	 but the name that is assigned to a database connection set in BuildOptions.
	*/
	public static function getDatabase($name = null)
	{
		if ($name == null)
			$name = "database_connection";
		return \Oranges\MasterContainer::get($name);
	}

	/**
	 Runs a mysqli->prepare on the requested database.
	
	 @param string $q - The MySQL query you would like to run.
	 @param string $database - The database name you want to run the query against.
	*/
	public static function prepare($q, $database = null)
	{
		//SQL recursion detection mechanism.
		if (self::$isRunning)
			throw new Exception("SQL recursion detected.");
		self::$isRunning = true;

		//log this query in the SQL Profiler
		SqlProfiler::addQuery($q);

		//now let's run the query.
		$dbh = self::getDatabase($database);
		$out = $dbh->prepare($q);
		$data = mysqli_error($dbh);
		if (!empty($data))
			self::throwSqlException($q);

		//turn off the SQL lock
		self::$isRunning = false;

		return $out;
	}

	/**
	 Runs a MySQL query against the requested database. Returns a mysqli-result object.
	
	 @param string $q - The MySQL query you would like to run.
	 @param string $database - The database name you want to run the query against.
	*/
	public static function query($q, $database = null)
	{
		//SQL recursion detection mechanism.
		if (self::$isRunning)
			throw new \Exception("SQL recursion detected.");
		self::$isRunning = true;

		//log this query in the SQL Profiler
		SqlProfiler::addQuery($q);

		//now let's run the query.
		$dbh = self::getDatabase($database);

		$startsWith = substr(trim($q), 0, 6);
		if ($startsWith == "INSERT" ||
			$startsWith == "UPDATE" ||
			$startsWith == "DELETE")
		{
			$out = $dbh->executeUpdate($q);
		}
		else
		{
			$out = $dbh->fetchAll($q);
		}

		//turn off the SQL lock
		self::$isRunning = false;

		return $out;
	}

	/**
	 Returns a single value for an SQL query that only returns a single
	 numeric result (like an SQL COUNT(*) query).

	 @param string $q - The MySQL query you would like to run.
	 @param string $database - The database name you want to run the query against.
	 @return mixed - Returns scalar value, or null if nothing is returned by MySQL.
	*/
	public static function scalarQuery($q, $database = null)
	{
		//SQL recursion detection mechanism.
		if (self::$isRunning)
			throw new Exception("SQL recursion detected.");
		self::$isRunning = true;

		//log this query in the SQL Profiler
		SqlProfiler::addQuery($q);

		//now let's run the query.
		$dbh = self::getDatabase($database);

		$out = $dbh->fetchArray($q);

		//turn off the SQL lock
		self::$isRunning = false;

		return $out[0];
	}

	/**
	 Returns a set of database models for each query result. The rows
	 returned must match the properties of the database model passed in.

	 @param string $q - The MySQL query you would like to run.
	 @param string $model - The class name of the model object you would like
	   returned.
	 @param string $database - The database name you want to run the query against.
	*/
	public static function modelQuery($q, $model, $database = null)
	{
		$q = self::query($q, $database);
		return new SqlModelIterator($q, $model);
	}
	/**
	 Close all open database connections.
	 */
	public static function close()
	{
		foreach (self::$dbh as $key => $value)
		{
			self::$dbh[$key]->close();
			self::$dbh[$key] = null;
		}
	}

	//--------------- PRIVATE FUNCTIONS -----------------//
	//helper function for query($q).
	private static function throwSqlException($q, $dbh = null)
	{
		if ($dbh == null)
			$dbh = self::$dbh;
		if (!($dbh instanceof \mysqli))
			throw new InternalException("MySQL Error: ". $q);
//		  die("MySQL Error on Query: $q\n". mysqli_error($dbh));
		throw new InternalException("MySQL Error on Query: ". mysqli_error($dbh). "<br>$q");
	}


}
