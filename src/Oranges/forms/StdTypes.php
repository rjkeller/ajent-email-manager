<?php
namespace Oranges\forms;

use Oranges\errorHandling\UserErrorHandler;
use Oranges\errorHandling\ErrorMetaData;
use Oranges\sql\Database;

class StdTypes
{
    private $type;
    private $data;

    public function __construct($type, $data = null)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function check(&$value, Field $field, UserErrorHandler $errorHandler)
    {
        if ($field->attrs == null)
        	$field->attrs = array();

		if (!isset($field->attrs['optional']))
			$field->attrs['optional'] = false;

		if ($field->attrs['optional'] && empty($value))
			return;
		if (!isset($field->message))
			$field->message = false;

        switch ($this->type)
        {
			case "button":
			case "raw":
				break;
        	case "list":
        		if (empty($value) && !$field->attrs['optional'])
        		{
        			if (empty($field->message))
    	    			$errorHandler->error(new ErrorMetaData("Invalid $field->happyName selected."));
					else
	        			$errorHandler->error(new ErrorMetaData($field->message));
        			return false;
    			}
                if (array_search($value, $field->attrs['vals']) === false)
                {
        			if (empty($field->message))
    	    			$errorHandler->error(new ErrorMetaData("Invalid $field->happyName selected."));
					else
	        			$errorHandler->error(new ErrorMetaData($field->message));
	        		return false;
                }
                break;
        	case "password":
        		$errorHandler->checkStr($value, false, new ErrorMetaData("Please enter a valid password"));
                break;

            case "email":
                //$field->message should equal false if the field is required,
                //true otherwise.
        		$errorHandler->checkEmail($value, false, new ErrorMetaData("Please enter a email address"));
                break;

            case "date": //$field->message required for error in this field.
                $str = explode("-", $value);
                if (sizeof($str) != 3)
                {
                	$errorHandler->error(new ErrorMetaData($field->message));
                	return false;
                }
                else
                {
                    $s = sizeof($str);
                    for ($i = 0; $i < $s; $i++)
                    {
                    	$errorHandler->checkInt($value, false, new ErrorMetaData($field->message));
                    }
                }
                break;

			case "phonecc":
            	if (!$field->message)
            		$field->message = "Please enter a valid $field->happyName number country code";
			case "phoneext":
            	if (!$field->message)
            		$field->message = "Please enter a valid extension for your $field->happyName phone";
            	$field->attrs['optional'] = true;
            case "phone":
            	if (!$field->message)
            		$field->message = "Please enter a valid $field->happyName number";
            	$errorHandler->checkInt($value, $field->attrs['optional'], new ErrorMetaData($field->message));
                break;

            case "bool":
                if ($value == "on" || $value == "1")
                    $value = 1;
                else
                    $value = 0;
                break;

			case "username":
				if (!$field->message)
					$field->message = "Invalid username entered";
				$errorHandler->checkUsername($value, new ErrorMetaData($field->message));

                $isTaken = Database::scalarQuery("
					SELECT
						COUNT(*)
					FROM
						users
					WHERE
						username = '$value'
				");
                if ($isTaken > 0)
                	return $errorHandler->error(new ErrorMetaData("Username is already taken"));
                break;

            case "str":
			case "textarea":
            	if (!$field->message)
            		$field->message = "Invalid $field->happyName entered";
            	$errorHandler->checkStr($value, $field->attrs['optional'], new ErrorMetaData($field->message));
                break;

            case "id":
            	if (!$field->message)
            		$field->message = "Invalid $field->happyName entered";
            	$errorHandler->checkId($value, false, new ErrorMetaData($field->message));
                break;

            case "money":
				if (empty($value) && $field->attrs['optional'])
					return;

                if ((!is_numeric($value) && !is_double($value)) || $value < 0)
                {
                    if (!$field->message)
                        return $errorHandler->error(new ErrorMetaData("Invalid $field->happyName entered"));
                    else
                        return $errorHandler->error(new ErrorMetaData($field->message));
                }
                break;

            case "int":
            	if (!$field->message)
            		$field->message = "Invalid $field->happyName entered";
            	$errorHandler->checkInt($value, false, new ErrorMetaData($field->message));
                break;

            case "id":
            	if (!$field->message)
            		$field->message = "Invalid ID entered";
            	$errorHandler->checkId($value, false, new ErrorMetaData($field->message));
                break;

            case "agreement":
                if ($value != "on")
                {
                	return $errorHandler->error(new ErrorMetaData("You must accept the agreement to continue"));
                }
                break;

            case "image":
            	if (!$field->attrs['optional'] && !FileUpload::isImageUploaded($value))
            	{
	            	if (!$field->message)
	            		return $errorHandler->error(new ErrorMetaData("No image was uploaded. Please upload an image."));
	            	else
	            	    return $errorHandler->error(new ErrorMetaData($field->message));
				}
                break;
        }
        return null;
    }


    /** ABSTRACT METHOD*/
    public function printField($name, $value, $attrs)
    {
        if (isset($attrs['autovalidate']) && $attrs['autovalidate'])
        {
            echo "<div id=\"$name\">\n";
        }

        switch ($this->type)
        {
        	case "raw":
        		echo $this->name;
        		break;
        	case "button":
				echo '<div align="right"><input type="image" src="images/submit_button.gif" title="submit"  class="submit"></div>';
				break;

            case "bool":
            	echo "<input type=\"checkbox\" name=\"$name\" id=\"$name\" class=\"checkbox\"";
            	if ($value == "on" || $value == 1)
            		echo " checked";
            	echo ">";
            	break;

			case "phoneext":
				?> <input name="<?= $name ?>"  style="width:80px;" style="width:65px;" type="text" id="<?= $name ?>" value="<?= $value ?>" size="8"<?= $attrs['extra'] ?>> <?
				break;

			case "phonecc":
				?> <input name="<?= $name ?>" class="phonecc" id="<?= $name ?>" type="text" value="<? if (empty($value)) echo "1"; else echo $value; ?>" size="4" <?= $attrs['extra'] ?>> <?
				break;

			case "phone"
				?> <input name="<?= $name ?>" type="text" id="<?= $name ?>" value="<?= $value ?>" size="24" mask="numeric" style="width:170px;" <?= $attrs['extra'] ?>> <?
              break;

  			case "textarea":
  				?> <textarea name="<?= $name ?>" rows="<?= $attrs['rows'] ?>" cols="<?= $attrs['cols'] ?>" class="input-1"<?= isset($attrs['extra']) ? $attrs['extra'] : "" ?>><?= $value ?></textarea>
  				<?
  				break;

            case "fname":
            case "lname":
            case "email":
            case "city":
            case "date":
            case "zip":
            case "ccnumber":
            case "ccexpr":
            case "username":
            case "str":
            case "id":
            case "int":
            case "id":
            case "money":
                echo "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\"";
                if (isset($attrs['extra']))
                    echo $attrs['extra'];
                if (!empty($attrs['size']))
                {
                	echo " class=\"rjbox\" size=\"$attrs[size]\"";
            	}
            	else
            	{
            		echo " class=\"input-1\"";
        		}
                echo ">";
                break;

			case "list":
            	echo "<select name=\"$name\"". $attrs['extra'];
            	if (empty($attrs['class']))
            		echo " class=\"input-1\"";
            	else
            		echo " class=\"$attrs[class]\"";
            	echo ">\n";
		        foreach ($attrs['vals'] as $key => $v)
		        {
            		echo "  <option value=\"$v\"";
            		if ($value == $v && $value != "null")
                		echo " selected";
            		echo ">$key</option>\n";
        		}
            	echo "</select>";
            	break;

			case "password":
                echo "<input type=\"password\" name=\"$name\" id=\"$name\" value=\"$value\" class=\"input-1\"";
				if (isset($attrs['extra']))
					echo $attrs['extra'];
				echo ">\n";
                break;

            case "state":
                echo "<select name=\"$name\" id=\"$name\" class=\"input-1\">";
                echo '<option value="">Please Select</option>';
                echo '<option value="AL" >Alabama</option>';
                echo '<option value="AK" >Alaska</option>';
                echo '<option value="AS" >Arizona</option>';
                echo '<option value="AR" >Arkansas</option>';
                echo '<option value="CA" >California</option>';
                echo '<option value="CO" >Colorado</option>';
                echo '<option value="CT" >Connecticut</option>';
                echo '<option value="DE" >Delaware</option>';
                echo '<option value="DC" >District of Columbia</option>';
                echo '<option value="FL" >Florida</option>';
                echo '<option value="GA" >Georgia</option>';
                echo '<option value="HI" >Hawaii</option>';
                echo '<option value="ID" >Idaho</option>';
                echo '<option value="IL" >Illinois</option>';
                echo '<option value="IN" >Indiana</option>';
                echo '<option value="IA" >Iowa</option>';
                echo '<option value="KS" >Kansas</option>';
                echo '<option value="KY" >Kentucky</option>';
                echo '<option value="LA" >Louisiana</option>';
                echo '<option value="ME" >Maine</option>';
                echo '<option value="MD" >Maryland</option>';
                echo '<option value="MA" >Massachusetts</option>';
                echo '<option value="MI" >Michigan</option>';
                echo '<option value="MN" >Minnesota</option>';
                echo '<option value="MS" >Mississippi</option>';
                echo '<option value="MO" >Missouri</option>';
                echo '<option value="MT" >Montana</option>';
                echo '<option value="NE" >Nebraska</option>';
                echo '<option value="NV" >Nevada</option>';
                echo '<option value="NH" >New Hampshire</option>';
                echo '<option value="NJ" >New Jersey</option>';
                echo '<option value="NM" >New Mexico</option>';
                echo '<option value="NY" >New York</option>';
                echo '<option value="NC" >North Carolina</option>';
                echo '<option value="ND" >North Dakota</option>';
                echo '<option value="OH" >Ohio</option>';
                echo '<option value="OK" >Oklahoma</option>';
                echo '<option value="OR" >Oregon</option>';
                echo '<option value="PW" >Pennsylvania</option>';
                echo '<option value="RI" >Rhode Island</option>';
                echo '<option value="SC" >South Carolina</option>';
                echo '<option value="SD" >South Dakota</option>';
                echo '<option value="TN" >Tennessee</option>';
                echo '<option value="TX" >Texas</option>';
                echo '<option value="UT" >Utah</option>';
                echo '<option value="VT" >Vermont</option>';
                echo '<option value="VI" >Virginia</option>';
                echo '<option value="WA" >Washington</option>';
                echo '<option value="WV" >West Virginia</option>';
                echo '<option value="WI" >Wisconsin</option>';
                echo '<option value="WY" >Wyoming</option>';
                echo '</select>';
                break;

            case "label":
                echo $name;

            case "country":
				$cc = StdData::getCountry();
		        echo "<select name=\"$name\" id=\"$name\" style=\"width:171px !important;\"$attrs[extra]>\n";
		        foreach ($cc as $key => $v)
		        {
		            echo "<option value=\"$key\"";
		            if (empty($value) && $key == "US")
		            	echo " selected";
		            if ($key == $value)
		                echo " selected";
		            echo ">$v</option>\n";
		        }
		        echo "</select>";
                break;

            case "image":
            	FileUpload::printField($name);
              break;

            case "agreement":
	        	echo "<input type=\"checkbox\" name=\"$name\" id=\"$name\" class=\"checkbox\"";
				if (isset($attrs['extra']))
					echo $attrs['extra'];
				echo ">\n";

			break;
        }

        if (isset($attrs['autovalidate']) && $attrs['autovalidate'])
        {
            echo '    <img src="images/ok.gif" title="Valid" alt="Valid" class="validMsg" border="0">';
            echo "\n";
            echo '  <img src="images/error.gif" title="Error" alt="Error" class="textfieldRequiredMsg" border="0">';
            echo "\n";
            echo "</div>\n";
        }
    }

    /** ABSTRACT METHOD*/
    public function js($name, $attrs)
    {
        switch ($this->type)
        {
            case "fname":
            case "lname":
            case "email":
            case "city":
            case "state":
            case "date":
            case "zip":
            case "country":
            case "ccnumber":
            case "ccexpr":
            case "phone":
            case "bool":
            case "username":
            case "str":
            case "id":
            case "int":
            case "id":
            case "password":
                echo "    new Spry.Widget.ValidationTextField(\"$name\", \"none\", {useCharacterMasking:true, validateOn:[\"change\"]});\n";
                break;

            case "image":
        }
    }
}


?>
