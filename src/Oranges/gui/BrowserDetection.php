<?php
namespace Oranges\gui;

class BrowserDetection
{
    static public $supportsCookies;
    static public $isMsie;

    public function init()
    {
        if (setcookie("t", "t", time()+60))
            self::$supportsCookies = true;
        else
            self::$supportsCookies = false;

        if (stripos($_SERVER['HTTP_USER_AGENT'], "MSIE") === false)
            self::$isMsie = false;
        else
            self::$isMsie = true;
    }
}
