<?php
namespace Pixonite\TagCloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oranges\sql\Database;

use Pixonite\TagCloudBundle\Helper\TagCloudPrinter;

class ShowBegTagsController extends Controller
{
	public function indexAction($beg_id)
	{
		$printer = new TagCloudPrinter($this->get('router'));
		$printer->readTagsFromTable("tags_related WHERE product_id = '". $beg_id ."'");

		return $this->render('TagCloudBundle:widgets:begTags.twig.html',
			array("tags" => $printer));
	}
}
