<?php
namespace Oranges\SearchBundle\Helper;

use Oranges\sql\Database;
use Oranges\sql\SqlModelIterator;
use Oranges\SearchBundle\Helper\SearchSpec;
use Oranges\frontend\WgSmarty;
use Oranges\forms\ListGenerator;
use Oranges\errorHandling\ForceError;

use Oranges\SearchBundle\Entity\SearchParams;
use Oranges\SearchBundle\Entity\SearchQuery;
use Oranges\UserBundle\Helper\SessionManager;

use Oranges\MasterContainer;

/**
 This class provides a convenient interface for displaying
 a large number of items that can be searched through in
 search engine format.

 Reserved GET/POST parameters:
   - filter
   - orderby
   - numResults
   - page
   - q
   - others maybe?
 */
class AjaxSearchEngine extends SearchEngine
{
	public function loadJsonParameters($json)
	{
		$this->loadFromArray(json_decode($json, true));
	}

	public function getJsonParameters()
	{
		return json_encode(array(
			"filter" => $this->filter,
			"orderby" => $this->orderBy,
			"numResults" => $this->numResults,
			"q" => $this->q,
			"page" => $this->page
		));
	}

	private $searchQuery = null;
	public function saveQuery()
	{
		if ($this->searchQuery != null)
			return $this->searchQuery;

		$params = new SearchParams();
		$params->user_id = SessionManager::$user->id;
		$params->search_query = base64_encode($this->getJsonParameters());
		$params->spec = base64_encode(serialize($this->spec));
		$params->create();

		$this->searchQuery = $params->id;
		return $params->id;
	}

	/**
	 @param $no_delete - If you don't want garbage collection to occur on the
	    current query. This is used by the AJAX delete function.
	*/
	public function loadQuery($query_id, $no_delete = false)
	{
		$params = new SearchParams();
		$params->loadUserQuery($query_id);

		$this->loadJsonParameters(
			base64_decode($params->search_query));

		$this->spec = unserialize(base64_decode($params->spec));

		//once we load a search query, we get rid of it since it'll likely
		//be re-created later anyways (unless told otherwise)
		if (!$no_delete)
			$params->delete();
	}

	public function flipToNextPage()
	{
		$this->page++;
	}

	public function flipToPreviousPage()
	{
		if ($this->page > 0)
			$this->page--;
	}

	public function nextLink()
	{
		$router = MasterContainer::get("router");
		$url = $router->generate(
			'SearchBundleAjaxNextPage',
			array('query' => $this->saveQuery() ));
		return $url;
	}

	public function prevLink()
	{
		$router = MasterContainer::get("router");
		$url = $router->generate(
			'SearchBundleAjaxPreviousPage',
			array('query' => $this->saveQuery() ));
		return $url;
	}
}
