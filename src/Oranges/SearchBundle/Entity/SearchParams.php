<?php
namespace Oranges\SearchBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\RedisBundle\Helper\Redis;

use Oranges\errorHandling\UnrecoverableSystemException;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
*/
class SearchParams extends DatabaseModel
{
	public $id;

	public $user_id;

	public $search_query;

	public $spec;

	protected function getTable()
	{
		return "search_params";
	}

	public function loadUserQuery($query_id)
	{
		$redis = Redis::getInstance();

		$this->id = $query_id;
		$this->user_id = $redis->get("SearchParams". $this->id . "UserId");
		$this->search_query = $redis->get("SearchParams". $this->id . "SearchQuery");
		$this->spec = $redis->get("SearchParams". $this->id . "Spec");

		if ($this->user_id != SessionManager::$user->id)
		{
			throw new UnrecoverableSystemException("", "",
				"Cannot initialize SearchParams because table row does not exist: ". $query_id);
			$this->search_query = null;
			$this->spec = null;
			return false;
		}
	}

	public function save()
	{
		throw new UnrecoverableSystemException("", "",
			"Cannot save a SearchParams object");
	}

	public function create()
	{
		$redis = Redis::getInstance();
		$id = $redis->incr("SearchParamsID");
		
		$this->id = $id;
		$redis->set("SearchParams". $id . "UserId", $this->user_id);
		$redis->set("SearchParams". $id . "SearchQuery", $this->search_query);
		$redis->set("SearchParams". $id . "Spec", $this->spec);

		$oneDay = 60*60*24;
		$redis->expire("SearchParams". $id . "UserId", $oneDay);
		$redis->expire("SearchParams". $id . "SearchQuery", $oneDay);
		$redis->expire("SearchParams". $id . "Spec", $oneDay);

		$redis->lpush("SearchParams". SessionManager::$user->id, $id);
	}

	public function delete()
	{
		$redis = Redis::getInstance();

		$redis->del("SearchParams". $this->id . "UserId");
		$redis->del("SearchParams". $this->id . "SearchQuery");
		$redis->del("SearchParams". $this->id . "Spec");
	}

	public function deleteAll()
	{
		$redis = Redis::getInstance();

		$data = null;
		$key = "SearchParams". SessionManager::$user->id;
		while (($data = $redis->lpop($key)) != null)
		{
			$this->id = $data;
			$this->delete();
		}
	}
}
