<?php
namespace Pixonite\TagCloudBundle\Controller;

use Pixonite\BlogBundle\Query\BlogPostSearch;
use Pixonite\BlogBundle\Query\BlogPostSpec;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oranges\sql\Database;
use Oranges\errorHandling\ForceError;
use Oranges\framework\BuildOptions;
use Oranges\UserBundle\Helper\SessionManager;

use Pixonite\TagCloudBundle\Helper\TagCloudPrinter;

class ShowTagsController extends Controller
{
	public function indexAction()
	{
		$printer = new TagCloudPrinter($this->get('router'));
		$printer->min_font_size = 10;
		$printer->max_font_size = 72;

		$lowestNumEntries = BuildOptions::$get['TagCloudBundle']['lowestNumEntries'];
		$highestNumEntries = BuildOptions::$get['TagCloudBundle']['highestNumEntries'];

		$printer->readTagsFromTable("tags WHERE user_id = '". SessionManager::$user->id ."' AND num <= ". $highestNumEntries ." AND num > ". $lowestNumEntries);

		$template_vars = array("tags" => $printer,
			"company_name" => BuildOptions::$get['company_name_short']);

		return $this->render('TagCloudBundle:pages:tags.twig.html',
			$template_vars);
	}

	public function showBegsForPhraseAction($keyword)
	{
		$keyword = str_replace("-", " ", $keyword);
		ForceError::$inst->checkStr($keyword);

		//check for an appendage to remove.
		if (isset(BuildOptions::$get['TagCloudBundle']['AppendToUrl']))
		{
			$append_to_url = BuildOptions::$get['TagCloudBundle']['AppendToUrl'];
			$keyword = substr($keyword, 0,
				strlen($keyword) - strlen($append_to_url));
		}

		$spec = new BlogPostSpec();
		$spec->setKeyword($keyword);

	    $search = new BlogPostSearch($spec);

		$template_vars = array(
			"search" => $search,
			"entries" => $search->getSqlQuery(),
			"keyword" => ucwords($keyword),
			"tagEntityName" => BuildOptions::$get['TagCloudBundle']['TagEntityName'],
			"company_name" => BuildOptions::$get['company_name_short']);

		return $this->render('TagCloudBundle:pages:tagsForKeyword.twig.html',
			$template_vars);
	}

	public function showMiniTagsAction()
	{
		$printer = new TagCloudPrinter($this->get('router'));
		$printer->min_font_size = 10;
		$printer->max_font_size = 36;
		$printer->disable_max_value = true;
		$printer->readTagsFromTable("tags WHERE user_id = '". SessionManager::$user->id ."' ORDER BY num DESC LIMIT 20");

		$template_vars = array("tags" => $printer,
			"company_name" => BuildOptions::$get['company_name_short']);

		return $this->render('TagCloudBundle:widgets:begTags.twig.html',
			$template_vars);
	}
}
