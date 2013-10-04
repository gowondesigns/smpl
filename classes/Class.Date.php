<?php
/* SMPL Date Class
// 
//
//*/


class Date
{
    private $year;
    private $month;
    private $day;
    private $hours;
    private $minutes;
    private $seconds;
    
        
    private function __construct($smplDateString)
    {
        if (preg_match('((?!0{4})\d{4})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])([0-1][0-9]|2[0-3])[0-5][0-9][0-5][0-9]', $uri) !== 1)
            throw new StrictExceptions('Invalid Date');
        
        $this->year = substr($smplDateString, 0, 4);
        $this->month = substr($smplDateString, 4, 2);
        $this->day = substr($smplDateString, 6, 2);
        $this->hours = substr($smplDateString, 8, 2);
        $this->minutes = substr($smplDateString, 10, 2);
        $this->seconds = substr($smplDateString, 12, 2);
    }

    // Returns current date
    public static function Now()
    {
        return new self(date("YmdHis"));
    }

    // Create new Date or generate Date from SMPL Date Strings
    // SmplDateTime Strings are always stored in the following format: YYYYMMDDHHMMSS
    // Always interpreted as UTC
    // other possible names? FromFlat, FromFlatDate
    public static function FromString($string)
    {
        // Maybe validate string and throw error on fail
        return new self($string);
    }
    
    // Create new Date from Unix timestamp
    public static function FromTime($timestamp)
    {
        $string = date("YmdHis", $timestamp);
        return new self($string);
    }

    // Pass timezone offset in HHMM or HH:MM format
    public static function Offset($useSemiColon = true)
    {
        $value = intval(Configuration::Get('dateOffset'));
        if ($value > 14 || $value < -12)
            throw new StrictException("Date offset of ".$value." is invalid.");
        
        if ($value < 0)
            $offset = "-".str_pad(abs($value), 2, "0", STR_PAD_LEFT);
        else
            $offset = "+".str_pad(abs($value), 2, "0", STR_PAD_LEFT);
        
        if ($useSemiColon)
            $offset .= ":00";
        else
            $offset .= "00";        

        return $offset;
    }
    
    // Shift date in seconds
    public function AddTime($timeshift)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        $time += $timeshift; // Add time shift (subtract by using a negative amount)
        
        $date = date("YmdHis", $time);
        $this->year = substr($date, 0, 4);
        $this->month = substr($date, 4, 2);
        $this->day = substr($date, 6, 2);
        $this->hours = substr($date, 8, 2);
        $this->minutes = substr($date, 10, 2);
        $this->seconds = substr($date, 12, 2);
        
        return $this;
    }
        
    // Return date string in specified format. If null, default to SMPL Date Format
    public function ToString($stringFormat = null, $offset = false)
    {
        if (null === $stringFormat)
            $stringFormat = "YmdHis";
            
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($offset)
            $time += (intval(Configuration::Get('dateOffset')) * 3600); // Add offset
        
        return date($stringFormat, $time);
    }
    
    // Return date as integer in SMPL Date Format
    public function ToInt($offset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($offset)
            $time += (intval(Configuration::Get('dateOffset')) * 3600); // Add offset
        
        return floatval(date("YmdHis", $time));
    }
    
    // Return date in Unix timestamp format
    public function ToTime($offset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($offset)
            $time += (intval(Configuration::Get('dateOffset')) * 3600); // Add offset
        
        return $time;
    }
}
?>