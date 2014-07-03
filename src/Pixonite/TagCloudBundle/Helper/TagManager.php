<?php
namespace Pixonite\TagCloudBundle\Helper;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

use Pixonite\TagCloudBundle\Helper\KeywordCloud;
use Pixonite\TagCloudBundle\Entity\Tag;
use Pixonite\TagCloudBundle\Entity\RelatedTag;

/**

  @author R.J. Keller <rjkeller@pixonite.com>
*/
class TagManager
{
	public static function refreshTagCache()
	{
		$allUsers = Database::modelQuery("
			SELECT
				*
			FROM
				users
			",
			"Oranges\UserBundle\Entity\User");

		Database::query("DELETE FROM tags");
		Database::query("DELETE FROM tags_related");

		foreach ($allUsers as $user)
		{

			$tagCls = BuildOptions::$get['TagCloudBundle']['TagProductInterface'];
			$tagInterface = new $tagCls();
			$q = $tagInterface->getIterator($user->id);


			$keywords = new KeywordCloud($user->id);

			foreach ($q as $beg)
			{
				$keywords->appendVerbiage($tagInterface->getText($beg),
					$tagInterface->getProductId($beg));
			}

			$keywords->inflateTags();
		}
		
	}
}
