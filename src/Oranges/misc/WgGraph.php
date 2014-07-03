<?php
namespace Oranges\misc;

include_once( __DIR__.'/open-flash-chart.php' );

use Oranges\sql\Database;

/**
 Prints a flash chart to the screen.
 */
class WgGraph
{
	public $date1;
	public $date2;
	/**
	 Possible values:
	   3m
	   6m
	   1y
	   1d
	   1w
	 */
	public $increments;
	public $steps = 20;

	public $sqlQuery;
	public $dateField;
	public $link;

	/** possible values: "bar", "line".
	*/
	public $graphType = "bar";

	public function printGraph()
	{
		$bar_blue = new \bar_glass( 75, '#A62525', '#C76867' );

		$range1 = new KDate($this->date1);
		$range2 = new KDate($this->date2);

		$max = 0;
		$min = 1000000;

		$labels = array();
		$i = 0;

		$q = Database::query($this->sqlQuery);


		while ($row = $q->fetch_row())
		{
			$num = $row[0];
			$label = $row[1];

			$bar_blue->add_link($num,
				$this->link
			);
			$labels[] = $label;

			if ($num > $max)
				$max = $num;
			if ($num < $min)
				$min = $num;
		}

		// create the graph object:
		$g = new \graph();

		$g->data_sets[] = $bar_blue;
		$g->title(' ', '{font-size: 20px;}');

		$g->x_axis_colour( '#909090', '#ADB5C7' );
		$g->y_axis_colour( '#909090', '#ADB5C7' );

		if ($this->graphType == "line")
			$g->line_hollow( 2, 4, '0x80a033', '', 10 );

		$g->set_x_labels($labels);
		$g->set_x_label_style(6, '#ffffff');

		//$g->set_x_labels( array( 'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug', "Sept", "Oct", "Nov", "Dec" ) );
		$g->set_y_max( $max );
		$g->set_y_min( $min );
		$g->y_label_steps( $this->steps );
		$g->set_bg_colour("#FFFFFF");
		//$g->set_y_legend( 'Open Flash Chart', 12, '#736AFF' );
		echo $g->render();
	}
}
