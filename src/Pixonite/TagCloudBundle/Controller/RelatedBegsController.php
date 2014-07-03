<?php
namespace Pixonite\TagCloudBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Oranges\sql\Database;

use Pixonite\BlogBundle\Entity\Beg;
use Pixonite\TagCloudBundle\Helper\TagCloudPrinter;
use Pixonite\TagCloudBundle\Helper\KeywordCloud;

class RelatedBegsController extends Controller
{
	public function indexAction($beg_id)
	{
		$q = Database::modelQuery("
			SELECT
				*
			FROM
				related_begs
			LEFT JOIN
				blog_posts
			ON
				blog_posts.id = related_begs.related_product_id				
			WHERE
				related_begs.product_id = '". $beg_id ."'
			LIMIT
				5
		",
		"Pixonite\BlogBundle\Entity\BlogPost");

		return $this->render('TagCloudBundle:widgets:relatedBegs.twig.html',
			array("begs" => $q,
				"hasRows" => sizeof($q) > 0));
	}
}
