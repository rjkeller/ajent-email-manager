<?php
namespace Oranges\forms;

/**
 Generates a <select> list where a certain item in the $_GET statement is
 automatically selected based on the name.
 */
class ListGenerator
{
    /**
     @param $name - The value that goes in <select name="$name">
     @param $elements - A key/value pair where the key is the $_GET[] value and
       the name is what goes in between the <option> tags.
     */
    public function __construct($name, $elements, $array = null, $daBox = true)
    {
        if ($array == null)
          $array = $_GET;

		if ($daBox)
	        echo "<select name=\"$name\">\n";
        foreach ($elements as $key => $value)
        {
            echo "  <option value=\"$value\"";
            if ($array[$name] == $value && $value != "null")
                echo " selected";
            echo ">$key</option>\n";
        }
        if($daBox)
	        echo "</select>";
    }

    public static function printList($name, $elements, $item = null, $daBox = true)
    {
		if ($daBox)
	        echo "<select name=\"$name\">\n";
        foreach ($elements as $key => $value)
        {
            echo "  <option value=\"$value\"";
            if ($item == $value)
                echo " selected";
            echo ">$key</option>\n";
        }
        if($daBox)
	        echo "</select>";
    }
}

?>
