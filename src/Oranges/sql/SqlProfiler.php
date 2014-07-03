<?php
namespace Oranges\sql;

use Oranges\framework\BuildOptions;

class SqlProfiler
{
    static public $data;

    public static function addQuery($q)
    {
		return;





        if ($q instanceof mysqli)
            throw new InternalException("Mysql profiler is getting crap in the input.");
        $startsWtih = "SELECT";
//        if (BuildOptions::$get['enable_sql_profiler']) // && substr($q, 0, strlen($startsWtih)) == $startsWtih
			$out = debug_backtrace();
			$out = array_slice($out, 0, 10);
			
			$i = 0;
			foreach ($out as $key => $v1)
			{
				unset($out[$key]['object']);
				unset($out[$key]['args']);
			}
            self::$data[] = array(
				"query" => $q,
				"stacktrace" => $out);
    }
}

?>
