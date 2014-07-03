<?php
namespace Oranges\misc;

define('LOCK_SUFFIX', '.lock');

/**
* File locks
*
* @package NanoFramework\System
* @author Stéphane BRUN
* @version 0.0.1 
*/
class CronHelper 
{

        private static $pid;

        private static function isrunning() 
        {
                $pids = explode(PHP_EOL, `ps -e | awk '{print $1}'`);
                if(in_array(self::$pid, $pids))
                {
                        return true;
                }
                return false;
        }

        public static function lock() 
        {
                global $argv;

                $lock_file = sys_get_temp_dir()."/".str_replace("/", "_", $argv[0]).LOCK_SUFFIX;

                if(file_exists($lock_file)) 
                {
                        // Is running?
                        self::$pid = file_get_contents($lock_file);
                        if(self::isrunning()) 
                        {
//                              error_log("==".self::$pid."== Already in progress...");
                                return false;
                        }
                        else 
                        {
//                              error_log("==".self::$pid."== Previous job died abruptly...");
                        }
                }

                self::$pid = getmypid();
                file_put_contents($lock_file, self::$pid);
                return self::$pid;
        }

        public static function unlock() 
        {
                global $argv;
        $r = false;

                $lock_file = sys_get_temp_dir().$argv[0].LOCK_SUFFIX;

                if(file_exists($lock_file))
                {
                        unlink($lock_file);
                        $r = true;
        }
        else
        {
            $r = false;
        }
        
                return $r;
        }

}
