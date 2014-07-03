<?php
namespace Pixonite\TagCloudBundle\Helper;

use Oranges\sql\Database;

use Pixonite\TagCloudBundle\Helper\KeywordCloud;
use Pixonite\TagCloudBundle\Entity\Tag;
use Pixonite\TagCloudBundle\Entity\RelatedTag;

class TagCloudSync
{
	public function onRun($data)
	{
		$keywords = new KeywordCloud();
		$beg = $data;

		$keywords->appendVerbiage($beg->post);

		foreach ($keywords->words as $key => $value)
		{
			$tag = new RelatedTag();
			$tag->num = $value;
			$tag->keyword = $key;
			$tag->beg_id = $beg->id;
			$tag->create();

			Database::query("DELETE FROM tags WHERE keyword = '". $key ."'");

			//does a tag already exist?
			$q = Database::scalarQuery("
				SELECT
					COUNT(*)
				FROM
					tags
				WHERE
					keyword = '". $key ."'
				LIMIT
					1
			");
			if ($q > 0)
			{
				$tag = new Tag();
				$tag->loadKeyword($key);
				$tag->num += $value;
				$tag->save();
			}
			//if a tag doesn't already exist, create a new one.
			else
			{
				$tag = new Tag();
				$tag->num += $value;
				$tag->keyword = $key;
				$tag->create();
			}
		}
	}
}