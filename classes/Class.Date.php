<?php
/**
 * Class.Date
 * @package SMPL\Date
 */

/**
 * Date Class
 * Produces strict datetime objects that provide a fluent interface for
 * converting into various types and formats
 * @package Date
 */
class Date
{
    /* Time Constants, in seconds */
    const YEAR   = 31556926;
    const MONTH  = 2629744;
    const WEEK   = 604800;
    const DAY    = 86400;
    const HOUR   = 3600;
    const MINUTE = 60;
    const SECOND = 1;

    /* Parameter Constants */
    const SUPPRESS_SEMICOLON = true;
    const SUPPRESS_TENSE = true;
    const USE_LOCAL = true;

    private $year;
    private $month;
    private $day;
    private $hours;
    private $minutes;
    private $seconds;

    /**
     * Private Date constructor so that Date objects can only be created
     * via public methods. Formed from a datetime string in the
     * format: YYYYMMDDHHmmSS. The datetime is always interpreted as UTC.
     * @param $datetime
     * @throws StrictException
     * @return Date
     */
    private function __construct($datetime)
    {
        $valid = Pattern::Validate(Pattern::SIGNATURE_DATETIME, $datetime);
        if ($valid === false) {
            throw new StrictException('Invalid Date String: '. $datetime);
        }
        $this->year = $valid[1];
        $this->month = $valid[2];
        $this->day = $valid[3];
        $this->hours = $valid[4];
        $this->minutes = $valid[5];
        $this->seconds = $valid[6];
    }

    /**
     * Generates Date object with current datetime
     * @return Date
     */  
    public static function Now()
    {
        return new self(date("YmdHis"));
    }

    /**
     * Generates Date object from given string
     * @param string $datetime Datetime string in YYYYMMDDHHmmSS format
     * @return Date
     */ 
    public static function FromString($datetime)
    {
        return new self($datetime);
    }
    
    /**
     * Generates Date object from given Unix timestamp
     * @param int $time
     * @return Date
     */ 
    public static function FromTime($time)
    {
        $string = date("YmdHis", $time);
        return new self($string);
    }

    /**
     * Generates timezone offset
     * @param bool $suppressSemiColon Set whether or not to include semicolon in timezone string
     * @throws StrictException
     * @return string Returns timezone offset in HHMM or HH:MM format
     */
    public static function TimeZone($suppressSemiColon = false)
    {
        $value = intval(Config::Get('dateOffset'));
        if ($value > 14 || $value < -12) {
            throw new StrictException("System Timezone offset of ".$value." is invalid.");
        }
        
        if ($value < 0) {
            $timeZone = '-' . str_pad(abs($value), 2, '0', STR_PAD_LEFT);
        }
        else {
            $timeZone = '+' . str_pad(abs($value), 2, '0', STR_PAD_LEFT);
        }
        
        if ($suppressSemiColon) {
            $timeZone .= '00';
        }
        else {
            $timeZone .= ':00';
        }        

        return $timeZone;
    }

    /**
     * Returns time difference between two timestamps, in human readable format.
     * If the second timestamp is not given, the current time will be used.
     * Also consider using [Date::FuzzySpan] when displaying a span.
     *
     *     $span = Date::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     *     $span = Date::span(60, 182, 'minutes'); // 2
     *
     * @param \Date $remote timestamp to find the span of
     * @param \Date $local timestamp to use as the baseline
     * @param array $units formatting string
     * @return  int|array
     */
    public static function Span(Date $remote, Date $local = null, array $units = null)
    {
        if ($units === null) {
            $units = array(Date::SECOND, Date::MINUTE, Date::HOUR, Date::DAY, Date::WEEK, Date::MONTH, Date::YEAR);
        }
        if ($local === null) {
            $local = Date::Now();
        }

        $output = array();
        $span = abs($remote->ToInt() - $local->ToInt());

        if (in_array(Date::YEAR, $units)) {
            $output['years'] = (int) floor($span / Date::YEAR); // should these be cast as int?
            $span -= Date::YEAR * $output['years'];
        }
        if (in_array(Date::MONTH, $units)) {
            $output['months'] = (int) floor($span / Date::MONTH);
            $span -= Date::MONTH * $output['months'];
        }
        if (in_array(Date::WEEK, $units)) {
            $output['weeks'] = (int) floor($span / Date::MONTH);
            $span -= Date::WEEK * $output['weeks'];
        }
        if (in_array(Date::DAY, $units)) {
            $output['days'] = (int) floor($span / Date::MONTH);
            $span -= Date::DAY * $output['days'];
        }
        if (in_array(Date::HOUR, $units)) {
            $output['hours'] = (int) floor($span / Date::MONTH);
            $span -= Date::HOUR * $output['hours'];
        }
        if (in_array(Date::MINUTE, $units)) {
            $output['minutes'] = (int) floor($span / Date::MONTH);
            $span -= Date::MINUTE * $output['minutes'];
        }
        if (in_array(Date::SECOND, $units)) {
            $output['seconds'] = $span;
        }

        if (count($output) === 1) {
            // Only a single output was requested, return it
            return array_pop($output);
        }
        else {
            return $output;
        }
    }

    /**
     * Returns the difference between a time and now in a "fuzzy" way.
     * Displaying a fuzzy time instead of a date is usually faster to read and understand.
     *
     *     $span = Date::fuzzy_span(time() - 10); // "moments ago"
     *     $span = Date::fuzzy_span(time() + 20); // "in moments"
     *
     * A second parameter is available to manually set the "local" timestamp,
     * however this parameter shouldn't be needed in normal usage and is only
     * included for unit tests
     *
     * @param \Date $remote "remote" timestamp
     * @param \Date $local "local" timestamp, defaults to current time
     * @param bool $suppressTense
     * @return  string
     */
    public static function FuzzySpan(Date $remote, Date $local = null, $suppressTense = false)
    {
        if (null === $local) {
            $local = Date::Now();
        }

        // Determine the difference in seconds
        $offset = abs($local->ToInt() - $remote->ToInt());

        if ($offset <= Date::MINUTE) {
            $span = 'moments';
        }
        elseif ($offset < (Date::MINUTE * 20)) {
            $span = 'a few minutes';
        }
        elseif ($offset < Date::HOUR) {
            $span = 'less than an hour';
        }
        elseif ($offset < (Date::HOUR * 4)) {
            $span = 'a couple of hours';
        }
        elseif ($offset < Date::DAY) {
            $span = 'less than a day';
        }
        elseif ($offset < (Date::DAY * 2)) {
            $span = 'about a day';
        }
        elseif ($offset < (Date::DAY * 4)) {
            $span = 'a couple of days';
        }
        elseif ($offset < Date::WEEK) {
            $span = 'less than a week';
        }
        elseif ($offset < (Date::WEEK * 2)) {
            $span = 'about a week';
        }
        elseif ($offset < Date::MONTH) {
            $span = 'less than a month';
        }
        elseif ($offset < (Date::MONTH * 2)) {
            $span = 'about a month';
        }
        elseif ($offset < (Date::MONTH * 4)) {
            $span = 'a couple of months';
        }
        elseif ($offset < Date::YEAR) {
            $span = 'less than a year';
        }
        elseif ($offset < (Date::YEAR * 2)) {
            $span = 'about a year';
        }
        elseif ($offset < (Date::YEAR * 4)) {
            $span = 'a couple of years';
        }
        elseif ($offset < (Date::YEAR * 8)) {
            $span = 'a few years';
        }
        elseif ($offset < (Date::YEAR * 12)) {
            $span = 'about a decade';
        }
        elseif ($offset < (Date::YEAR * 24)) {
            $span = 'a couple of decades';
        }
        elseif ($offset < (Date::YEAR * 64)) {
            $span = 'several decades';
        }
        else {
            $span = 'a long time';
        }

        if ($suppressTense) {
            return $span;
        }
        elseif ($remote <= $local) {
            return $span . ' ago';
        }
        else {
            return 'in ' . $span;
        }
    }
    
    /**
     * Shift the stored date in seconds
     * @param int $timeInSeconds Amount in seconds to shift the stored time. Negative value will subtract time
     * @return Date Returns self for fluent interface
     */
    public function AddTime($timeInSeconds)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        $time += $timeInSeconds;
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
     * __toString magic method
     * @return string
     */
    public function __toString()
    {
        return $this->ToString();
    }

    /**
     * Returns date in string format
     * @param string $format Set format for datetime string
     * @param bool $useLocalOffset Set whether or not to offset time by system timezone
     * @return string Returns datetime
     */
    public function ToString($format = null, $useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if (null === $format) {
            $format = "YmdHis";
        }
        
        if ($useLocalOffset) {
            $time += (intval(Config::Get('dateOffset')) * 3600);
        }
        
        return date($format, $time);
    }
    
    /**
     * Returns date in int format
     * @param bool $useLocalOffset Set whether or not to offest time by system timezone
     * @return int Returns datetime
     */
    public function ToInt($useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($useLocalOffset)
            $time += (intval(Config::Get('dateOffset')) * 3600);
        
        return floatval(date("YmdHis", $time));
    }
    
    /**
     * Returns date in Unix timestamp format
     * @param bool $useLocalOffset Set whether or not to offset time by system timezone
     * @return int Returns Unix timestamp
     */
    public function ToTime($useLocalOffset = false)
    {
        $time = mktime($this->hours, $this->minutes, $this->seconds, $this->month, $this->day, $this->year);
        
        if ($useLocalOffset)
            $time += (intval(Config::Get('dateOffset')) * 3600);
        
        return $time;
    }
}
?>