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
	private $isRunning = false;

	/** Array of open database connections. */
	private $dbh;

    private $em;
    
    public function __construct($em)
    {
        $this->em = $em;
    }

	/**
	 Returns the database name requested. This is generally not an exact database name,
	 but the name that is assigned to a database connection set in BuildOptions.
	*/
	public static function getDatabase($name = null)
	{
	    return $em;
	}

	/**
	 Runs a MySQL query against the requested database. Returns a mysqli-result object.
	
	 @param string $q - The MySQL query you would like to run.
	 @param string $database - The database name you want to run the query against.
	*/
	public static function query($q, $params = null)
	{
		//SQL recursion detection mechanism.
		if (self::$isRunning)
			throw new Exception("SQL recursion detected.");
		self::$isRunning = true;

		//now let's run the query.
		$query = $this->em->createNativeQuery($q);
		if ($params != null)
		{
		    $i = 1;
		    foreach ($params as $item)
		    {
		        $query->setParameter($i++, $item);
		    }
		}
		$out = $query->getResult();

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
		$q = self::query($q, $database);
		$obj = $q->fetch_array();
		$q->close();

        if (!$obj)
            return null;
		return $obj[0];
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
