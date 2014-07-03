<?php
namespace Oranges\SearchBundle\Controller;

use Oranges\SearchBundle\Helper\AjaxSearchEngine;
use Oranges\SearchBundle\Entity\SearchParams;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;
use Oranges\sql\Database;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class AjaxController extends Controller
{
	public function printListAction($adapterName, $spec)
	{
		$adapter = BuildOptions::$get['SearchBundle'][$adapterName]['Spec'];
		$template = $spec->template;

		if (empty($template))
			throw new \Exception("Spec ". $adapter ." doesn't have a template specified.");

		$template_vars = array();

		$this->performGarbageCollection();

		$search = new AjaxSearchEngine();
		$search->loadGetParameters();
		$search->init($spec);

		$template_vars['results'] = $search->getSqlQuery()->getArray();
		$template_vars['results_size'] = sizeof($template_vars['results']);
		$template_vars['searchResults'] = $search;
		$template_vars['template'] = $template;
		$template_vars['spec'] = $search->spec;

		$template_vars['search_query'] = $search->saveQuery();
		$template_vars['adapter'] = $adapterName;

		return $this->render("SearchBundle:pages:results.twig.html",
			$template_vars);
	}

	/**
	 * @Route("/search_query_ajax/{query}/delete/{item_id}", name="SearchBundleAjaxDelete")
	 */
	public function deleteAction($query, $item_id)
	{
		$template_vars = array();

		$search = new AjaxSearchEngine();
		$search->loadQuery($query, true);

		$spec = $search->spec;
		if (!$spec->canDelete)
			throw new \Exception("Access Denied");
		else
			$spec->delete($item_id);


		$search->init($search->spec);

		$template_vars = array();

		$template_vars['results'] = $search->getSqlQuery()->getArray();
		$template_vars['results_size'] = sizeof($template_vars['results']);
		$template_vars['searchResults'] = $search;
		$template_vars['template'] = $spec->template;
		$template_vars['spec'] = $search->spec;

		return $this->render("SearchBundle:pages:deleteResults.twig.html",
			$template_vars);
	}

	/**
	 * @Route("/search_query_ajax/{query}/nextPage", name="SearchBundleAjaxNextPage")
	 */
	public function nextPageAction($query)
	{
		$template_vars = array();

		$search = new AjaxSearchEngine();
		$search->loadQuery($query);
		$search->flipToNextPage();
		$search->init($search->spec);

		$template_vars['results'] = $search->getSqlQuery();
		$template_vars['results_size'] = sizeof($template_vars['results']);
		$template_vars['searchResults'] = $search;
		$template_vars['template'] = $search->spec->template;
		$template_vars['spec'] = $search->spec;

		return $this->render("SearchBundle:pages:results.twig.html",
			$template_vars);
	}

	/**
	 * @Route("/search_query_ajax/{query}/previousPage", name="SearchBundleAjaxPreviousPage")
	 */
	public function previousPageAction($query)
	{
		$search = new AjaxSearchEngine();
		$search->loadQuery($query);
		$search->flipToPreviousPage();
		$search->init($search->spec);

		$template_vars['results'] = $search->getSqlQuery();
		$template_vars['results_size'] = sizeof($template_vars['results']);
		$template_vars['searchResults'] = $search;
		$template_vars['template'] = $search->spec->template;
		$template_vars['spec'] = $search->spec;

		return $this->render("SearchBundle:pages:results.twig.html",
			$template_vars);
	}


	/********************** PRIVATE FUNCTIONS ***********************/

	private static $isGarbageCollectionPerformed = false;
	
	private function performGarbageCollection()
	{
		if (self::$isGarbageCollectionPerformed)
			return;

		self::$isGarbageCollectionPerformed = true;

		$searchParams = new SearchParams();
		$searchParams->deleteAll();
	}
}
