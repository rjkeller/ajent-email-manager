<?php
namespace Oranges\MongoDbBundle\Helper;

use Oranges\sql\Database;
use Oranges\errorHandling\UnrecoverableSystemException;
use Oranges\errorHandling\ForceError;
use Oranges\errorHandling\InternalException;
use Oranges\sql\SqlProfiler;
use Oranges\searchResults\SearchResultsSpec;
use Oranges\framework\BuildOptions;

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
	 An array of booleans. The boolean is set to true if the specified row was
	 modified.
	*/
	protected $__isChanged;

	private $__wasInited = false;

	/**
	 Which columns of the table are encrypted. If these columns are set to
	 true, then this class will handle encryption/decryption of those
	 fields.
	*/
	protected $__encrypt;

	/** If you would like 2-way encryption, then set this to the encryption
	  key you wish to use. */
	protected $__key = null;

	/** A list of columns and their values in this database table. */
	private $__dbValues;

	/**
	 If you want debug output to be printed by this class, set this equal to
	 true.
	*/
	private $__printOnly = false;

	/**
	 Returns the name of this table.
	*/
	abstract protected function getTable();

	/**
	 Returns a list of fields that will be forced by the system. If null is
	 returned, then field checking is disabled. Override this method to do
	 field checking.
	*/
	public function getFields()
	{
		return null;
	}

	/**
	 @param $key - If this table uses encryption, then pass in the key to
	   encrypt with.
	 */
	public function __construct($key = null)
	{
		$this->__extraColumns = array();
		$this->__dbValues = array();
		$this->__isChanged = array();
	}

	public function __get($get) { return $this->getColumn($get); }
	public function __set($set, $val) { $this->setColumn($set, $val); }

    public function offsetExists ( $offset )
    {
        return isset($this->__dbValues[$offset]);
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

	public function __isset( $name )
	{
		return array_key_exists($name, $this->__dbValues);
	}

	public function __unset ( $name )
	{
		unset($this->__dbValues[$name]);
	}

	/**
	 Sets the value of the specified column in MySQL.
	
	 @param $set - The name of the column.
	 @param $val - The value to set the column to.
	*/
	public function setColumn($set, $val)
	{
		//XXX: is this a good thing to be doing?
		if ($set == "id")
			$set = "_id";
		$this->__isChanged[$set] = true;
		$this->__dbValues[$set] = $val;
	}

	/**
	Returns the value of the specified column.
	
	@param $get - The name of the column.
	@return The value of the column passed in.
	*/
	public function getColumn($get)
	{
		if ($get == "id")
			$get = "_id";
		if (!array_key_exists($get, $this->__dbValues))
			throw new InternalException("row ". $this->getTable() ."->". $get ." doesn't exist. Valid rows: ". print_r($this->__dbValues, true));
		return $this->__dbValues[$get];
	}

	public function isChanged()
	{
		return $this->__isChanged;
	}

	/**
	 Load the row with the specified ID from this table for modification.
	 */
	public function load($id)
	{
		return $this->loadQuery(array("_id" => $id), false);
	}

	/**
	 Load the row with the specified query parameters from this table for modification.
	 */
	protected function loadQuery($query, $forceSuccess = false)
	{
		if (isset($query['_id']))
			$query['_id'] = new \MongoId($query['_id']);
		$db = MongoDb::getDatabase();
		$c = $db->selectCollection($this->getTable());
		if (!is_array($query))
			throw new \Exception("loadQuery requires an array. Got: $query");

		$value = iterator_to_array($c->find($query));

		if (!$value)
		{
			if ($forceSuccess)
				return false;
			throw new UnrecoverableSystemException("", "",
				"Cannot initialize ". $this->getTable() ." because table row does not exist: ". print_r($query, true));
		}

		$this->__dbValues = null;
		foreach ($value as $i)
			$this->__dbValues = $i;

		$this->decryptFields();
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

		$this->__dbValues = $array;
		if (isset($array["_id"]) && !empty($array["_id"]))
		{
			$this->__wasInited = true;

			if ($array["_id"] instanceof \MongoId)
				$this->__dbValues['_id'] = $this->__dbValues['_id']->__toString();
		}

		$this->decryptFields();
		$this->__wasInited = true;
	}

	public function getArray()
	{
		return $this->__dbValues;
	}

	/**
	 Inserts these entries into the database. It also generates a unique 33
	 character id that goes in the ID column of the table.
	 */
	public function create()
	{
		$this->bulkCreate(array($this));

		return $this->_id;
	}

	/**
	 Does a bulk insert of objects into the database.
	 */
	public function bulkCreate(array $objs)
	{
		//run events
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['ModelEvent']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['ModelEvent'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->create($objs);
				}
			}
		}


		foreach ($objs as $model)
		{
			$model->__single_obj_create();
		}

		//refresh object cache
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['DbCache']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['DbCache'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->create($objs);
				}
			}
		}
	}

	protected function __single_obj_create()
	{
		$this->checkFields();

		$this->encryptFields();

		$db = MongoDb::getDatabase();
		$c = $db->selectCollection($this->getTable());
		$out = $c->insert($this->__dbValues, array('safe' => true));
		$this->__wasInited = true;

		$this->__dbValues['_id'] = $this->__dbValues['_id']->__toString();

		$this->decryptFields();

		return $this->_id;
		
	}

	/**
	 Updates the SQL ID with $this->id to the entry values in this table.
	 */
	public function save()
	{
		$this->bulkSave(array($this));
	}

	/**
	 Does a bulk saving of objects into the database.
	 */
	public function bulkSave(array $objs)
	{
		//run events
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['ModelEvent']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['ModelEvent'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->save($objs);
				}
			}
		}

		foreach ($objs as $model)
		{
			$model->__single_obj_save();
		}

		//refresh object cache
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['DbCache']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['DbCache'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->save($objs);
				}
			}
		}
	}

	protected function __single_obj_save()
	{
		if (!$this->__wasInited)
				throw new UnrecoverableSystemException("", "",
					"DatabaseModel Error: Cannot update entry without an initialization");

		$this->checkFields();

		$this->encryptFields();

		$changedValues = array();
		foreach ($this->__isChanged as $key => $value)
		{
			if (!$value)
				continue;

			if (!isset($this->__dbValues[$key]))
				$changedValues[$key] = false;
			else
				$changedValues[$key] = $this->__dbValues[$key];
		}

		$db = MongoDb::getDatabase();
		$c = $db->selectCollection($this->getTable());
		$out = $c->update(
			array("_id" => $this->__dbValues["_id"]),
			array('$set' => $changedValues),
			array('safe' => true)
		);
		
		$this->decryptFields();
	}


	/**
	 Deletes the row currently loaded, but does NOT clear its contents from
	 this class instance.
	*/
	public function delete()
	{
		$this->bulkDelete(array($this));
	}

	/**
	 Does a bulk deleting of objects into the database.
	 */
	public function bulkDelete(array $objs)
	{
		//run events
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['ModelEvent']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['ModelEvent'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->delete($objs);
				}
			}
		}

		foreach ($objs as $model)
		{
			$model->__single_obj_delete();
		}

		//refresh object cache
		if (isset(BuildOptions::$get['MongoDbModel']) &&
			isset(BuildOptions::$get['MongoDbModel']['DbCache']))
		{
			foreach (BuildOptions::$get['MongoDbModel']['DbCache'] as $key => $value)
			{
				if ($value['Model'] == get_class($this))
				{
					$cls = $value['Class'];
					$cache = new $cls();
					$cache->delete($objs);
				}
			}
		}
	}

	protected function __single_obj_delete()
	{
		$this->encryptFields();

		$db = MongoDb::getDatabase();
		$c = $db->selectCollection($this->getTable());
		$c->remove(
			array("_id" => $this->__dbValues["_id"]),
			array('safe' => true));
	}

	//------------------------- PRIVATE METHODS -----------------------------//
	private function checkFields()
	{
		$fields = $this->getFields();

		if ($fields != null)
		{
			foreach ($fields as $value)
			{
				if (!array_key_exists($value, $this->__dbValues))
					throw new UnrecoverableSystemException("", "",
						"Creation failed for ". $this->getTable() .". Database model requires field ". $value .". Field values:<br><pre>". print_r($this->__dbValues, true) ."</pre>");
			}
		}
	}

	private function encryptFields()
	{
		if (isset($this->__dbValues['_id']) && !($this->__dbValues['_id'] instanceof \MongoId))
			$this->__dbValues['_id'] = new \MongoId($this->__dbValues['_id']);

		if (!is_array($this->__encrypt))
			return;
		foreach ($this->__encrypt as $col => $value)
		{
			if (!$value)
				continue;

			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

			//encrypt using PHP MCrypt library
			$this->__dbValues[$col] = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->__key, $this->__dbValues[$col], MCRYPT_MODE_ECB, $iv));
		    
		}
	}

	private function decryptFields()
	{
		if (isset($this->__dbValues['_id']) && $this->__dbValues['_id'] instanceof \MongoId)
			$this->__dbValues['_id'] = $this->__dbValues['_id']->__toString();

		if (!is_array($this->__encrypt))
			return;
		foreach ($this->__encrypt as $col => $value)
		{
			if (!$value)
				continue;

			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
		    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

			//encrypt using PHP MCrypt library
			$this->__dbValues[$col] = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->__key, base64_decode($this->__dbValues[$col]), MCRYPT_MODE_ECB, $iv);
		    
		}
		
	}
}
