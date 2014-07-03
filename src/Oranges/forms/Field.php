<?php
namespace Oranges\forms;

class Field
{
  public $name;
  public $happyName;
  public $type;
  public $error;
  public $attrs;
  public $value;
  public $category;

  public function __construct($name, $happyName, &$value, $type, $category, $error = "Invalid Input", $attrs = null)
  {
    $this->name = $name;
    $this->happyName = $happyName;
    $this->type = $type;
    $this->error = $error;
    $this->attrs = $attrs;

    $this->value = $value;
    $this->category = $category;
  }
}

?>
