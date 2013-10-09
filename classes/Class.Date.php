<?php
/**
 * Class.Date
 *
 * @package SMPL\Date
 */

/**
 * Date Class
 *
 * Produces strict datetime objects that provide a fluent interface for
 * converting into various types and formats
 * 
 * @package Date
 */
class Date
{
    private $year;
    private $month;
    private $day;
    private $hours;
    private $minutes;
    private $seconds;

    /**
     * Date constructor
     *
     * Private Date constructor so that Date objects can only be created
     * via public methods. Formed from a datetime string in the
     * format: YYYYMMDDHHmmSS. The datetime is always interpreted as UTC.
     *
     * @param $datetime
     * @throws StrictException
     * @return \Date
     */
    private function __construct($datetime)
    {
        if (preg_match('((?!0{4})\d{4})(0[1-9]|1[0-2])(0[1-9]|[1-2][0-9]|3[0-1])([0-1][0-9]|2[0-3])[0-5][0-9][0-5][0-9]', $datetime) !== 1)
            throw new StrictException('Invalid Date String: '. $datetime);
        
        $this->year = substr($datetime, 0, 4);
        $this->month = substr($datetime, 4, 2);
        $this->day = substr($datetime, 6, 2);
        $this->hours = substr($datetime, 8, 2);
        $this->minutes = substr($datetime, 10, 2);
        $this->seconds = substr($datetime, 12, 2);
    }

    /**
     * Generates Date object with current datetime
     *
     * @return Date
     */  
    public static function Now()
    {
        return new self(date("YmdHis"));
    }

    /**
     * Generates Date object from given string
     *
     * @param string $datetime Datetime string in YYYYMMDDHHmmSS format
     * @return Date
     */ 
    public static function FromString($datetime)
    {
        return new self($datetime);
    }
    
    /**
     * Generates Date object from given Unix timestamp
     *
     * @param int $timestamp
     *     
     * @return Date
     */ 
    public static function FromTime($timestamp)
    {
        $string = date("YmdHis", $timestamp);
        return new self($string);
    }

    /**
     * Generates timezone offset
     *
     * @param bool $useSemiColon Set whether or not to include semicolon in timezone string
     * @throws StrictException
     * @return string Returns timezone offset in HHMM or HH:MM format
     */
    public static function TimeZone($useSemiColon = true)
    {
        $value = intval(Configuration::Get('dateOffset'));
        if ($value > 14 || $value < -12) {
            throw new StrictException("System Timezone offset of ".$value." is invalid.");
        }
        
        if ($value < 0) {
            $timeZone = "-".str_pad(abs($value), 2, "0", STR_PAD_LEFT);
        }
        else {
            $timeZone = "+".str_pad(abs($value), 2, "0", STR_PAD_LEFT);
        }
        
        if ($useSemiColon) {
            $timeZone .= ":00";
        }
        else {
            $timeZone .= "00";
        }        

        return $timeZone;
    }
    
    /**
     * Shift the stored date in seconds
     *
     * @param int $timeshift Amount in seconds to shift the stored time. Negative value will subtract time
     *     
     * @return Date Returns self for fluent interface
     */
    public function AddTime($timeshift)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        $time += $timeshift;
        $date = date("YmdHis", $time);
        $this->year = substr($date, 0, 4);
        $this->month = substr($date, 4, 2);
        $this->day = substr($date, 6, 2);
        $this->hours = substr($date, 8, 2);
        $this->minutes = substr($date, 10, 2);
        $this->seconds = substr($date, 12, 2);        
        return $this;
    }
        
    /**
     * Returns date in string format
     *
     * @param string $format Set format for datetime string
     * @param bool $useLocalOffset Set whether or not to offset time by system timezone
     *     
     * @return string Returns datetime
     */
    public function ToString($format = null, $useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if (null === $format) {
            $format = "YmdHis";
        }
        
        if ($useLocalOffset) {
            $time += (intval(Configuration::Get('dateOffset')) * 3600);
        }
        
        return date($format, $time);
    }
    
    /**
     * Returns date in int format
     *
     * @param bool $useLocalOffset Set whether or not to offest time by system timezone
     *     
     * @return int Returns datetime
     */
    public function ToInt($useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($useLocalOffset)
            $time += (intval(Configuration::Get('dateOffset')) * 3600);
        
        return floatval(date("YmdHis", $time));
    }
    
    /**
     * Returns date in Unix timestamp format
     *
     * @param bool $useLocalOffset Set whether or not to offset time by system timezone
     *     
     * @return int Returns Unix timestamp
     */
    public function ToTime($useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($useLocalOffset)
            $time += (intval(Configuration::Get('dateOffset')) * 3600);
        
        return $time;
    }
}
?>