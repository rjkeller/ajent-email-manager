<?php
namespace Oranges\misc;


class KDate
{
    public $day;
    public $month;
    public $year;

    public function __construct($date = null)
    {
        //HACK: for php being a bitch
        if ($date == null)
            $date = date("Y-m-d");
        $this->year = 0;
        $this->month = 0;
        $this->day = 0;

        $split = explode("-", $date);
        $this->year += $split[0];
        $this->month += $split[1];
        $this->day += $split[2];
    }

    public function addYear($years = 1)
    {
        $finalTime = mktime(0, 0, 0, $this->month, $this->day, $this->year+$years);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function addMonth($months = 1)
    {
        $finalTime = mktime(0, 0, 0, $this->month+$months, $this->day, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function removeMonth($months = 1)
    {
        $finalTime = mktime(0, 0, 0, $this->month-$months, $this->day, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function removeDay($day = 1)
    {
        $finalTime = mktime(0, 0, 0, $this->month, $this->day - $day, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function addDay($days = 1)
    {
        $finalTime = mktime(0, 0, 0, $this->month, $this->day + $days, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function gotoBeginningMonth()
    {
        $finalTime = mktime(0, 0, 0, $this->month, 0, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }
    public function gotoBeginningYear()
    {
        $finalTime = mktime(0, 0, 0, 0, 0, $this->year);
        $this->__construct(date("Y-m-d", $finalTime));
    }

    public function __toString()
    {
        $finalTime = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        return date("Y-m-d", $finalTime);
    }

    public function format($f = "wg")
    {
        $finalTime = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        if ($f == "wg")
        {
            return date("m/d/y", $finalTime);
        }
        return date($f, $finalTime);
    }

    public function mktime()
    {
        return mktime(0, 0, 0, $this->month, $this->day, $this->year);
    }

    public function isAfter(KDate $time)
    {
        $t1 = mktime(0, 0, 0, $this->month, $this->day, $this->year);
        $t2 = mktime(0, 0, 0, $time->month, $time->day, $time->year);
        return $t1 > $t2;
    }

    public static function isBetween($start, $end, $date)
    {
        $time = $date->mktime();
        return $time >= $start->mktime() && $time <= $end->mktime();
    }
}
