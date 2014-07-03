<?php
namespace Oranges\sql;

/**
 Some useful SQL functions.

 @param R.J. Keller <rjkeller@wordgrab.com>
 */
class SqlUtility
{
  public static function getLength($query)
  {
    $q = Database::query($query);
    $rows = $q->num_rows;
    $q->close();
    if (!$rows)
        return 0;

    return $rows;
  }

  public static function containsEntries($table)
  {
    $q = Database::query("SELECT id FROM $table LIMIT 1");
    if ($q->num_rows <= 0)
      $result = false;
    else
      $result = true;
    $q->close();
    return $result;
  }

  public static function contains($table, $contains, $doError = true)
  {
    $result = Database::query("SELECT * FROM $table WHERE $contains LIMIT 1");
    if (empty($result))
        return false;
    $data = $result->fetch_assoc();
    $result->close();

    if ($doError && !$data)
    {
		throw new UnrecoverableSystemException("", "", "Error executing query:<br>SELECT * FROM $table WHERE $contains LIMIT 1");
    }

    return !empty($data);
  }
}
