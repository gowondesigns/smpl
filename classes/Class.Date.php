<?php
/* SMPL Date Classes
// 
//
//*/


//SMPL Date Strings are always stored in the following format: YYYYMMDDHHMMSS

class Date
{
    // Create new DateData or generate DateData from SMPL Date Strings
    public static function Create($fromSmplDateString = null)
    {
        if (null === $fromSmplDateString)
            $fromSmplDateString = date("YmdHis");
            
        return new DateData($fromSmplDateString);
    }

    // Flatten DateData into a printable/passable string (Default to SMPL Date String format) 
    public static function CreateFlat(DateData $dateData = null, $stringFormat = null)
    {
        if (null === $dateData)
            $dateData = new DateData(date("YmdHis"));
        
        return $dateData->Flatten($stringFormat);
    }
}

class DateData
{
    private $year;
    private $month;
    private $day;
    private $hours;
    private $minutes;
    private $seconds;
    
        
    public function __construct($smplDateString)
    {
        $this->year = substr($smplDateString, 0, 4);
        $this->month = substr($smplDateString, 4, 2);
        $this->day = substr($smplDateString, 6, 2);
        $this->hours = substr($smplDateString, 8, 2);
        $this->minutes = substr($smplDateString, 10, 2);
        $this->seconds = substr($smplDateString, 12, 2);
    }
    
    public function Get($item = null)
    {
        if (null === $item)
            return array(
                'year' => $this->year,
                'month' => $this->month,
                'day' => $this->day,
                'hours' => $this->hours,
                'minutes' => $this->minutes,
                'seconds' => $this->seconds);
        else
            return $this->$item;
    }
    
    // Return date string in specified format. If null, default to SMPL Date Format
    public function Flatten($stringFormat = null)
    {
        if (null === $stringFormat)
            $stringFormat = "YmdHis";
        
        return date($stringFormat, mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year));
    }
}
?>