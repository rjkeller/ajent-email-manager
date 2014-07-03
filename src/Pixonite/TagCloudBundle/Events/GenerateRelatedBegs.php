<?php
namespace Pixonite\TagCloudBundle\Events;

use Oranges\sql\Database;

use Pixonite\TagCloudBundle\Helper\KeywordCloud;
use Pixonite\TagCloudBundle\Entity\Tag;
use Pixonite\TagCloudBundle\Entity\RelatedTag;

/**
 This event should be ran when a beg is created.

 It generates "Related Begs" for the specified beg.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class GenerateRelatedBegs
{
	public function onRun($data)
	{
		$beg = $data;

		$q = Database::query("
			SELECT
				keyword
			FROM
				tags_related
			WHERE
				product_id = '". $beg->id ."'
		");
		$keywords = "";
		$i = 0;
		while ($keyword = $q->fetch_object())
		{
			if ($keyword->num > 2)
			//only use the first 15 keywords
			if ($i++ > 15)
				break;

			if ($keywords == "")
				$keywords = "keyword = '". $keyword->keyword ."'";
			else
				$keywords .= " OR keyword = '". $keyword->keyword ."'";
		}

		Database::query("DELETE FROM related_begs WHERE product_id = '". $beg->id ."'");

		if (empty($keywords))
			continue;

		Database::query("
			INSERT INTO
				related_begs
			SELECT
				NULL, '". $beg->id ."', product_id, COUNT(keyword)
			FROM
				tags_related
			WHERE
				(". $keywords .") AND
				product_id != '". $beg->id ."'
			GROUP BY
				product_id
			ORDER BY
				COUNT(keyword) DESC
			LIMIT
				5
		");
		
	}
}