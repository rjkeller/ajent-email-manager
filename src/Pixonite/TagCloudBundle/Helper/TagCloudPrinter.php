<?
namespace Pixonite\TagCloudBundle\Helper;

use Oranges\sql\Database;
use Oranges\framework\BuildOptions;

class TagCloudPrinter
{
	public $tags = array();

	public $min_font_size = 12;
	public $max_font_size = 24;

	public $disable_max_value = false;

	public $colors = array(
		"#FF7600", "#DE2159", "#039FAF", "#87A800",
	);

	public $router;

	public $hasTags = false;

	/** @param $router - Symfony router. */
	public function __construct($router)
	{
		$this->router = $router;
	}

	public function readTagsFromTable($tagQuery)
	{
		$q = Database::modelQuery("
			SELECT
				*
			FROM
				". $tagQuery
		,
		"Pixonite\TagCloudBundle\Entity\Tag");
		foreach ($q as $tag)
		{
			//prevent big tags from dominating the cloud.
			if (!$this->disable_max_value &&
				$tag->num > BuildOptions::$get['TagCloudBundle']['maxValue'])
			{
				$tag->num = BuildOptions::$get['TagCloudBundle']['maxValue'];
			}
			$this->tags[$tag['keyword']] = $tag['num'];
		}
		$this->hasTags = sizeof($q) > 0;
	}

	/* From:
		http://www.roscripts.com/Create_tag_cloud-71.html
	*/
	public function printTagCloud() {
		$tags = $this->tags;
		ksort($tags);
        // $tags is the array
       
        arsort($tags);
       
        $max_size = $this->max_font_size; // max font size in pixels
        $min_size = $this->min_font_size; // min font size in pixels
       
        // largest and smallest array values
		$values = array_values($tags);
		$max_qty = $min_qty = null;
		if (empty($values))
		{
			$max_qty = 0;
			$min_qty = 0;
		}
		else
		{
			$min_qty = min($values);
        	$max_qty = max($values);
		}
       
        // find the range of values
        $spread = $max_qty - $min_qty;
        if ($spread == 0) { // we don't want to divide by zero
                $spread = 1;
        }
       
        // set the font-size increment
        $step = ($max_size - $min_size) / ($spread);

		$append_to_url = "";
		if (isset(BuildOptions::$get['TagCloudBundle']['AppendToUrl']))
			$append_to_url = BuildOptions::$get['TagCloudBundle']['AppendToUrl'];

        // loop through the tag array
        ksort($tags);
		$i = 0;
        foreach ($tags as $key => $value) {
                // calculate font-size
                // find the $value in excess of $min_qty
                // multiply by the font-size increment ($size)
                // and add the $min_size set above
                $size = round($min_size + (($value - $min_qty) * $step));
				$color = $i++ % (sizeof($this->colors)-1);
                echo '<span class="word"><a href="';
				echo $this->router->generate('TagCloudBundleTagsKeyword',
					array('keyword' => str_replace(" ", "-", $key) . $append_to_url));
				echo '" style="font-size: ' . $size . 'px">' . $key . '</a></span> ';
				echo "\n\n";
        }
	}
}
