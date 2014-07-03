<?php
namespace Pixonite\TagCloudBundle\Helper;

use Pixonite\BlogBundle\Entity\Beg;
use Pixonite\TagCloudBundle\Entity\Tag;
use Pixonite\TagCloudBundle\Entity\RelatedTag;

use Oranges\sql\Database;

/**
 This class manages the tag cloud cache. Most of these methods provide the
 means for generating and caching tags for all the Blog Posts in the system.

 @author R.J. Keller <rjkeller@pixonite.com>
*/
class KeywordCloud
{
	/** Tags hard coded in the system
	@see HardCodedTag
	*/
	private static $hardcodedTags = null;

	private $user_id;

	public $stopWords;
	public $flipWords;

	public function __construct($user_id)
	{
		$this->words = array();
		$this->user_id = $user_id;

		$this->stopWords = explode(",", file_get_contents(__DIR__."/stop_words.txt"));
		$this->flipWords = parse_ini_file(__DIR__ ."/flip_words.txt");

		//if we don't have any hard-coded tags, then generate a list
		if (self::$hardcodedTags == null)
		{
			self::$hardcodedTags = array();

			//add in hard-coded tags if they're applicable
			$q = Database::query("
				SELECT
					keyword
				FROM
					tags_hardcoded
			");
			foreach ($q as $obj)
			{
				self::$hardcodedTags[] = $obj['keyword'];
			}
		}
	}

	/**
	 Hard-coded tags have a feature to inflate their value to make them appear
	 more popular than they actually are. This function applies the inflation
	 to the current tag data set.
	
	 Be careful to not run this method twice! Or else your tags will be
	 doubly inflated.
	*/
	public function inflateTags()
	{
		//hard-coded tags have an "inflation" feature, to give certain tags
		//extra weight. Check for this.
		$q = Database::query("
			SELECT
				keyword, inflate_num
			FROM
				tags_hardcoded
		");
		foreach ($q as $obj)
		{
			//check if the hard-coded tag exists. sometimes they don't.
			$count = Database::scalarQuery("
				SELECT
					COUNT(*)
				FROM
					tags
				WHERE
					keyword = '". $obj['keyword'] ."'
				LIMIT
					1
			");
			//if tag doesn't exist, create a new hard-coded tag. the page will
			//be blank (which is weird), but will be equal to its inflation
			//index.
			if ($count <= 0)
			{
				$tag = new Tag();
				$tag->num = $obj['inflate_num'];
				$tag->keyword = $obj['keyword'];
				$tag->create();
			}
			else
			{
				$tag = new Tag();
				$tag->loadKeyword($obj['keyword']);

				//inflate me!
				$tag->num += $obj['inflate_num'];

				$tag->save();
			}
		}
	}

	/**
	 Parses a list of words. and adds how often they occur to the $this->words
	 array (in the format of $words[number of occurances] = word).
	
	 @param string $verbiage - The verbiage that you want to parse in this 
		method.
	*/
	public function appendVerbiage($verbiage, $product_id)
	{
		$str = strtolower($verbiage);

		$len = strlen($str);

		$addedDash = false;

		$newStr = "";
		for ($i = 0; $i < $len; $i++)
		{
			if ($str{$i} >= 'A' && $str{$i} <= 'Z' ||
				$str{$i} >= 'a' && $str{$i} <= 'z' ||
				$str{$i} >= '0' && $str{$i} <= '9')
			{
				$addedDash = false;
				$newStr .= $str{$i};
			}
			else if (!$addedDash)
			{
				$this->addWord($newStr, $product_id);
				$newStr = "";
				$addedDash = true;
			}
		}

		$this->addWord($newStr, $product_id);

		//check for hard-coded tags
		foreach (self::$hardcodedTags as $tag)
		{
			$num = -1;
			$i = 0;

			do
			{
				//search the string for the next occurance of the tag.
				$i = strpos($verbiage, $tag, $i);

				//if the tag wasn't found given the offset, then cancel out
				//the loop.
				if ($i === false)
					break;

				//make sure to not start the next search at the exact position
				//that the tag was found at.
				$i++;

				//if the tag was found, then add it to the tag list.
				$this->addWord($tag, $product_id);

				//keep doing for each occurance of the tag in the string
				//$verbiage.
			} while (true);
		}
	}

	/**
	 Finds begs related to the beg passed in. Begs are returned as an array of
	 Beg objects. Only the 5 most relevant begs are returned.
	*/
	public function getRelatedProducts($product_id)
	{
		$this->appendVerbiage($beg->post, $product_id);
		$begs = $this->getBegsRelatedToKeywords($beg->id);

		$returnMe = array();
		$i = 0;
		foreach ($begs as $key => $value)
		{
			$returnMe[] = $key;
			if ($i++ > 5)
				break;
		}
		return $returnMe;
	}

	//------------ PRIVATE FUNCTIONS ---------------//
	private function addWord($word, $product_id)
	{
		if (isset($this->flipWords[$word]))
			$word = $this->flipWords[$word];

		if (array_search($word, $this->stopWords) !== false)
			return;

		if (empty($word))
			return;

		//i don't care about low numbers. large numbers might be a year.
		if (is_numeric($word) && ($word < 1800 || $word > 2100) && $word != '419')
			return;

		//sometimes IDs show up. so let's filter those out, since ID's are 
		//always more than 30 characters.
		if (strlen($word) > 20)
			return;

        $tag = new Tag();
        if ($tag->tryLoadKeyword($word))
        {
            $tag->num++;
            $tag->save();
        }
        else
        {
            $tag->num = 1;
            $tag->user_id = $this->user_id;
            $tag->keyword = $word;
            $tag->create();
        }

        $tag = new RelatedTag();
        if ($tag->tryLoad($word, $product_id))
        {
            $tag->num++;
            $tag->save();
        }
        else
        {
            $tag->num = 1;
            $tag->user_id = $this->user_id;
            $tag->keyword = $word;
            $tag->product_id = $product_id;
            $tag->create();
        }
	}

	private function getBegsRelatedToKeywords($beg_id)
	{
		//get list of begs that use keywords
		$begsToEvaluate = array();

        $q = Database::modelQuery("
            SELECT
                *
            FROM
                tags
            WHERE
                user_id = '". $this->user_id ."'
            ",
            "Pixonite\TagCloudBundle\Entity\Tag");

		foreach ($q as $tag)
		{
			$q = Database::query("
				SELECT
					product_id, num
				FROM
					tags_related
				WHERE
					keyword = '". $tag->keyword ."' AND
					product_id != '". $beg_id ."'
			");
			while ($obj = $q->fetch_object())
			{
				if (!isset($begsToEvaluate[$obj->beg_id]))
					$begsToEvaluate[$obj->beg_id] = 1;
				else
					$begsToEvaluate[$obj->beg_id] += 1;
			}
		}
		arsort($begsToEvaluate, SORT_NUMERIC);
		return $begsToEvaluate;
	}
}