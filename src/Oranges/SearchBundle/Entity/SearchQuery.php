<?php
namespace Oranges\SearchBundle\Entity;

use Oranges\DatabaseModel;
use Doctrine\ORM\Mapping as ORM;
use Oranges\UserBundle\Helper\SessionManager;

/**
 @author R.J. Keller <rjkeller@pixonite.com>
 @ORM\Entity
 @ORM\Table(name="search_query")
*/
class SearchQuery extends DatabaseModel
{
	/**
	  @ORM\Id
	  @ORM\Column(type="integer")
	  @ORM\GeneratedValue
	*/
	protected $id;

	/** @ORM\Column(type="integer") */
	protected $user_id;

	/** @ORM\Column(type="string") */
	protected $query_name;

	/** @ORM\Column(type="boolean") */
	protected $load_more_results;

	/**
	 The page number to load more results. Set this to -1 if there are no more
	 results to load.

	 @ORM\Column(type="string")
	 */
	protected $page_num;

	protected function getTable()
	{
		return "search_query";
	}

	public function loadQuery($query_name, $user_id = -1)
	{
	    if ($user_id == -1)
	        $user_id = SessionManager::$user->id;
		return parent::loadQuery("user_id = '". $user_id ."' AND query_name = '". $query_name ."'", true);
	}
}
