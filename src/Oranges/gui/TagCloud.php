<?php
namespace Oranges\gui;

use Oranges\sql\Database;
use Oranges\user\Helper\User;

class TagCloud
{
	public $tags = array();

	public function readTagsFromTable($table)
	{
		$q = Database::query("SELECT text FROM $table WHERE uid = '". User::$id ."'");
		while ($tag = $q->fetch_object())
		{
			$t = explode(',', $tag->text);
			foreach ($t as $t1)
			{
				if (!isset($this->tags[$t1]))
					$this->tags[$t1] = 1;
				else
					$this->tags[$t1]++;
			}
		}
		$q->close();
	}

	/* From:
		http://www.roscripts.com/Create_tag_cloud-71.html
	*/
	public function printTagCloud() {
		$tags = $this->tags;
		ksort($tags);
        // $tags is the array
       
        arsort($tags);
       
        $max_size = 24; // max font size in pixels
        $min_size = 12; // min font size in pixels
       
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
        // loop through the tag array
        ksort($tags);
        foreach ($tags as $key => $value) {
                // calculate font-size
                // find the $value in excess of $min_qty
                // multiply by the font-size increment ($size)
                // and add the $min_size set above
                $size = round($min_size + (($value - $min_qty) * $step));
                echo '<a href="domains?q='.$key.'" style="font-size: ' . $size . 'px">' . $key . '</a> ';
        }

		if (empty($values))
			echo "There are no tags set in your account.";
	}
}

?>
