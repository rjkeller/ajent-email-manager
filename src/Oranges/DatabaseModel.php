<?php
namespace Oranges;

use Oranges\sql\Database;
use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\InternalException;
use Oranges\sql\SqlProfiler;
use Oranges\searchResults\SearchResultsSpec;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

/**
 A database model in the WordGrab system

 @author R.J. Keller <rjkeller@wordgrab.com>
 */
abstract class DatabaseModel 
	implements \ArrayAccess
{
	/**
	 Name of the currently loaded table.
	*/
	private $__tableName = "";

	/**
	 An array of booleans. The boolean is set to true if the specified row was
	 modified.
	*/
	protected $__isChanged;

	private $__dbh;

	private $__wasInited = false;

	/**
	 Which columns of the table are encrypted. If these columns are set to
	 true, then this class will handle encryption/decryption of those
	 fields.
	*/
	protected $__encrypt;

	/** The maximum number of characters an ID can have. */
	protected $__cap = false;

	/** If you would like 2-way encryption, then set this to the encryption
	  key you wish to use. */
	protected $__key = null;

	/** A list of columns in this database table. */
	private $__columns;

	/**
	 If you want debug output to be printed by this class, set this equal to
	 true.
	*/
	private $__printOnly = false;


	/**
	 Extra values that may pop up with table inheritance.
	*/
	private $__extraColumns; // = array();

	/** If set to true, then every load() call will be cached in memory for this instance. This is good if you keep loading the same ID many times. However, it might increase memory usage since loaded objects won't be garbage collected in memory. */
	protected $__EnableModelCache = false;

	private static $__ModelCache = null;

	/**
	 Returns the name of this table.
	*/
	abstract protected function getTable();

    public function getSearchResultsSpec($whereClause = "")
    {
        $spec = new SearchResultsSpec();
        $spec->tableName = $this->getTable();
        $spec->whereClause = $whereClause;
        return $spec;
    }

	/**
	 @param $key - If this table uses encryption, then pass in the key to
	   encrypt with.
	 */
	public function __construct($key = null)
	{
//		if ($key == null)
//			$key = "f|7hk&O*U[g\$D%IE4fw@*&v\$+zki]N";

//		$this->__key = $key;

		$data = get_object_vars($this);
		$columns = array();
		foreach ($data as $key => $value)
		{
			if (substr($key, 0, 2) == "__")
			{
				unset($data[$key]);
				continue;
			}
			$data[$key] = false;
			$columns[] = $key;
		}
		$this->__extraColumns = array();
		$this->__columns = $columns;
		$this->__isChanged = $data;

		$this->__tableName = $this->getTable();
		$this->__dbh = $this->getDatabase();

		if (!($this->__dbh instanceof \Doctrine\DBAL\Connection))
			throw new UnrecoverableSystemException("", "",
				"SQL Error: Database is not of type mysqli");
	}

	public function __get($get) { return $this->getColumn($get); }
	public function __set($set, $val) { $this->setColumn($set, $val); }

	protected function getDatabase()
	{
		return Database::getDatabase();
	}

    public function offsetExists ( $offset )
    {
        return in_array($offset, $this->__columns) || isset($this->__extraColumns[$offset]);
    }
    public function offsetGet ( $offset )
    {
        return $this->getColumn($offset);
    }
    public function offsetSet ( $offset , $value )
    {
		$this->setColumn($offset, $value);
    }
    public function offsetUnset ( $offset )
    {
        //not supported
    }

	/**
	 Sets the value of the specified column in MySQL.
	
	 @param $set - The name of the column.
	 @param $val - The value to set the column to.
	*/
	public function setColumn($set, $val)
	{
		//ignore bogus data
		if (!in_array($set, $this->__columns))
			throw new InternalException("column '". $set ."' doesn't exist");
		$this->__isChanged[$set] = true;
		$this->$set = $val;
	}

	/**
	Returns the value of the specified column.
	
	@param $get - The name of the column.
	@return The value of the column passed in.
	*/
	public function getColumn($get)
	{
		if (isset($this->__extraColumns[$get]))
			return $this->__extraColumns[$get];
		if (!in_array($get, $this->__columns))
			throw new InternalException("row '". $get ."' doesn't exist");
		return $this->$get;
	}

	/**
	 Load the row with the specified ID from this table for modification.
	 */
	public function load($id)
	{
		return $this->loadQuery("id = '$id'", false);
	}

	/**
	 Load the row with the specified query parameters from this table for modification.
	 */
	protected function loadQuery($query, $forceSuccess = false)
	{
		//check if the result is cached
		$query_hash = md5($query);
		if (self::$__ModelCache != null &&
			isset(self::$__ModelCache[$this->__tableName]) && 
			isset(self::$__ModelCache[$this->__tableName][$query_hash])
			)
		{
			$results = self::$__ModelCache[$this->__tableName][$query_hash];
		}
		else
		{
			$rows = $this->getRowString();
			$results = $this->__dbh->fetchAll("
				SELECT
					$rows
				FROM
					$this->__tableName
				WHERE
					$query
				LIMIT
					1
			");
			
			if ($this->__EnableModelCache)
			{
				if (self::$__ModelCache == null)
				{
					self::$__ModelCache = array();
				}
				if (!isset(self::$__ModelCache[$this->__tableName]))
				{
					self::$__ModelCache[$this->__tableName] = array();
				}
				self::$__ModelCache[$this->__tableName][$query_hash] = $results;
			}
		}

		if (!isset($results[0]))
		{
			if ($forceSuccess)
				return false;
			throw new UnrecoverableSystemException("", "",
				"Cannot initialize $this->__tableName because table row does not exist: $query");
		}

		foreach ($results[0] as $key => $value)
		{
			if (isset($this->__key) && isset($this->__encrypt[$key]))
			{
			    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

				$this->$key = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->__key, base64_decode($value), MCRYPT_MODE_ECB, $iv);
			}
			else
				$this->$key = html_entity_decode($value);
		}
		$this->__wasInited = true;
		return true;
	}

	/**
	 This function is useful for form output (because you can do
	 fill($_POST)). The idea here is to take all the values in the array
	 and set them to the values in the table.

	 @param $array - The array to update values in this table with.
	 */
	public function fill($array)
	{
		if (!is_array($array))
			throw new UnrecoverableSystemException("", "",
				"Argument to DatabaseModel->fill() is not an array.");

		foreach ($this->__columns as $key)
		{
			$this->$key = $array[$key];
			unset($array[$key]);
		}

		//if there are any entries in the array passed in that weren't yet
		//added to the model, then they might be extra columns from table
		//inheritance. So if so, add those as "extra" columns and can still be
		//retrieved using getColumn.
		foreach ($array as $key => $value)
		{
			$this->__extraColumns[$key] = $value;
		}
	}

	public function fillWithObject($obj)
	{
		$this->initRow();

		foreach ($this->__columns as $key)
		{
			if ($key == "id")
				continue;
			if (isset($obj->$key) && in_array($key, $this->__columns))
			{
				$this->$key = $obj->$key;
			}
		}
	}

	public function getArray()
	{
		$array = array();
		foreach ($this->__columns as $value)
		{
			$array[$value] = $this->$value;
		}
		return $array;
	}

	/**
	 Inserts these entries into the database. It also generates a unique 33
	 character id that goes in the ID column of the table.
	 */
	public function create()
	{
		$query = $this->getCreateQuery($this->__cap);

		$out = $this->__dbh->executeUpdate($query);

		//if applicable, update the "id" attribute. If the ID is manually
		//generated by the user, then do nothing.
		if ($this->id == "NULL" || $this->id == "")
		{
			$this->id = $this->__dbh->lastInsertId($out);
		}
		$this->__wasInited = true;

		return $this->id;
	}

	/**
	 Returns the SQL query executed if you were to run the create() function.
	 This helps with certain scripts that need to generate recommended SQL
	 operations.
	*/
	public function getCreateQuery($cap = false)
	{
		$values = "";
		$rows = "";
		$isStart = true;

		foreach ($this->__columns as $col)
		{
			$i = $this->$col;
			$i = addslashes($i);

			if ($isStart)
				$isStart = false;
			else
				$values = $values . ", ";

			if (isset($this->__encrypt[$col]) && $this->__encrypt[$col])
			{
				if ($this->__key == null)
					$values .= "ENCRYPT('$i')";
				else
				{
				    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
				    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

					//encrypt using PHP MCrypt library
					$values .= "'". base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->__key, $i, MCRYPT_MODE_ECB, $iv)) . "'";
				}
			}
			else if ($col == "timestamp")
				$values .= "'". date("Y-m-d H:i:s") . "'";
			else if ($i == "DEFAULT" || $i == "NULL" || $i == "TRUE" ||
					 $i == "FALSE")
				$values .= $i;
			else
				$values .= "'$i'";

			if (empty($rows))
				$rows .= $this->__tableName . ".". $col;
			else
				$rows .= ", ". $this->__tableName . ".". $col;
		}

		return "INSERT INTO $this->__tableName ($rows) VALUES ($values)";
	}

	/**
	 Updates the SQL ID with $this->id to the entry values in this table.
	 */
	public function save()
	{
		if (!$this->__wasInited)
				throw new UnrecoverableSystemException("", "",
					"DatabaseModel Error: Cannot update entry without an initialization");

		$values = "";
		$isStart = true;

		$id = -1;

		$prep = array();

		foreach ($this->__columns as $key)
		{
			$value = $this->$key;
			if ($key == "id")
				continue;

			if ($isStart)
				$isStart = false;
			else
				$values = $values . ", ";

			if (isset($this->__encrypt[$key]) && $this->__encrypt[$key])
			{
			    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

				$value = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->__key, $value, MCRYPT_MODE_ECB, $iv));

				$prep[] = $value;
				$values = $values . "$this->__tableName.$key = ?";
			}
			else if ($value == "DEFAULT" || $value == "NULL" || $value == "TRUE" ||
				 $value == "FALSE")
			{
				$prep[] = $value;
				$values = $values . "$this->__tableName.$key = ?";
			}
			else
			{
				$prep[] = $value;
				$values = $values . "$this->__tableName.$key = ?";
			}
		}

		$query = "
			UPDATE
				$this->__tableName
			SET
				$values
			WHERE
				id = ?
		";
		$prep[] = $this->id;
		$this->query($query, $prep);
	}

	/**
	 Deletes the row currently loaded, but does NOT clear its contents from
	 this class instance.
	*/
	public function delete()
	{
		$this->query("
			DELETE FROM
				". $this->__tableName ."
			WHERE
				id = '". $this->id ."'
		");
	}

	/**
	 Verifies the current database model. If validation fails, errors are
	 stored in $errorList.
	
	 @return Whether or not the database model returns errors.
	*/
	public function validate(&$errorList)
	{
		$validator = MasterContainer::get('validator');
	    $errorList = $validator->validate($message);
		return empty($errorList);
	}

	//------------------------- PRIVATE METHODS -----------------------------//
	private function query($q, $prep = null)
	{
		if ($this->__printOnly)
		{
			echo $q ."<br>";
			return;
		}

		if ($this->__dbh == null)
			throw new UnrecoverableSystemException("", "",
				"Cannot connect to SQL database. Please try again later.");

		if ($prep == null)
			$out = $this->__dbh->executeUpdate($q);
		else
			$out = $this->__dbh->executeUpdate($q, $prep);

		return $out;
	}

	//helper function for query($q).
	static $count = 0;
	private function throwSqlException($q)
	{
		throw new UnrecoverableSystemException("", "",
			"MySQL Error on Query:<br>$error<br>$q");
	}

	private function getRowString()
	{
		$rowName = "";
		foreach ($this->__columns as $key)
		{
			if ($rowName != null)
				$rowName .= ",";
			else
				$rowName = "";

			$rowName .= "`$key`";
		}
		return $rowName;
	}

	private function parseArray($array)
	{
		$a = array();
		foreach ($this->__columns as $key)
		{
			echo "is key set? ". isset($this->__key) . "\nis encrypted? ". isset($this->__encrypt[$key]) . "\n";
			if (isset($this->__key) && isset($this->__encrypt[$key]))
			{
				echo "HIT ENCRYPTION!\n";
			    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

				$a[$key] = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->__key, base64_decode($array[$key]), MCRYPT_MODE_ECB, $iv);
			}
			else
				$a[$key] = $array[$key];
		}
		return $a;
	}

	/**
	 Returns a list of properties and their values used in this database model.
	*/
	private function getRow()
	{
		$data = get_object_vars($this);
		foreach ($data as $key => $value)
		{
			if (substr($key, 0, 2) == "__")
				unset($data[$key]);
		}
		return $data;
	}
}
