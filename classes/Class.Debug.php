<?php
/**
 * Class.Debug
 * @package SMPL\Debug
 */

/**
 * Debug Static Class
 * Procedure: All issues throw a PHP standard error type. Debug error handler turns Errors into Exceptions that can be caught.
 * Warnings and Notices only throw exceptions in Strict Mode. Logging defaults to PHP error log.
 * @package Debug
 */
class Debug
{
    /**
     * Turn on Debug Messages, all uses of Debug::Message() are captured
     */
    const DEBUG_ON = 'DEBUGON';

    /**
     * Turn on Strict Debugging: E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE, 
     * E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_USER_DEPRECATED, E_STRICT
     * will generate Exceptions 
     * Otherwise, only E_USER_ERROR, E_RECOVERABLE_ERROR will generate exceptions
     */
    const STRICT_ON = 'STRICTON';
    
    
    /**
     * Turn on Verbose Output, log encapsulated in <pre> tag at the end of execution
     * Turn off Verbose Output, log encapsulated in <!-- --> tag at the end of execution     
     */
    const VERBOSE_ON = 'VERBOSEON';
    
    /**
     * Save Debug Log to XML file (or error_log)
     */
    const SAVE_LOG = 'LOGGINGON';
    
    /**
     * Expand method will be sent to Debug Log vs returned in string
     */
    const EXPAND_TO_DEBUG = 'EXPANDTODEBUG';

    /**
     * Turn off Debug Output completely
     */
    const OUTPUT_OFF = 'OUTPUTOFF';
    
    /**
     * Remove styling of Debug Output
     */
    const OUTPUT_STYLE_OFF = 'OUTPUTSTYLEOFF';    

    /**
     * Lock Debug settings to prevent them from being changed
     */
    const LOCK = 'LOCKDEBUGGER';

    /**
     * Set a Timer marker
     */
    const TIMER_SET = null;

    /**
     * Show Timer stats
     */
    const TIMER_STATS = true;
    
    /**
     * Use Debug Messages
     * @var bool $debug
     */
    private static $debug;
    
    /**
     * Use Strict Debugging
     * @var bool $strict
     */
    private static $strict;
    
    /**
     * Use Verbose Output
     * @var bool $verbose
     */
    private static $verbose;
    
    /**
     * Use Debug Logging
     * @var bool $saveLog
     */
    private static $saveLog;

    /**
     * Use Debug Logging
     * @var bool $logging
     */
    private static $expandToDebug;
    
    /**
     * Use Debug Logging
     * @var bool $logging
     */
    private static $outputOff;
    
    /**
     * Use Debug Logging
     * @var bool $logging
     */
    private static $outputStyleOff;
    
    /**
     * Use Debug Logging
     * @var bool $logging
     */
    private static $lock;
        
    /**
     * Stores whether the Debug has already been set    
     * @var bool $initialized                                              
     */
    private static $initialized = false;
    
    /**
     * Debug log for messages and errors
     * @var array $logs
     */
    private static $logs = array();

    private static $timerMarkers = array();
    private static $times = array();

    /**
     * Path to the file to store the log in
     * @var string $logPath                                              
     */
    private static $logPath = null;
    
    /**
     * Float containing the time of execution in microseconds
     * @var float $initTime                                            
     */
    private static $initTime = null;

    /**
     * Private constructor so that Debug cannot be instantiated
     * @return Debug
     */
    private function __construct() {}

    /**
     * Initialize (or Reset) Debugger settings and set custom handlers
     * @return bool Returns TRUE on initial use, FALSE on subsequent uses
     */
    public static function Set()
    {
        if (!self::$initialized) {
            // Log initialization time
            self::$initTime = gettimeofday(true);
            self::$lock = false;
            
            //Set up PHP instance for debugging
            ini_set('display_errors', 'On');
            error_reporting(-1);
            set_error_handler(array('Debug', 'ErrorHandler'));
            set_exception_handler(array('Debug', 'ExceptionHandler'));
            register_shutdown_function(array('Debug', 'ExecutionEnd'));
            
            // create debug shortcuts for fast/lazy debugging
            if (!function_exists('l')) {
                function l() {
                    call_user_func_array(array('Debug', 'Log'), func_get_args());
                }
            }
            else {
                self::Log('Cannot use l() for Debug::Log(), function name has already been set.');
            }
            if (!function_exists('t')) {
                function t() {
                    return call_user_func_array(array('Debug', 'Timer'), func_get_args());
                }
            }
            else {
                self::Log('Cannot use t() for Debug::Timer(), function name has already been set.');
            }
            if (!function_exists('e')) {
                function e() {
                    return call_user_func_array(array('Debug', 'Expand'), func_get_args());
                }
            }
            else {
                self::Log('Cannot use e() for Debug::Expand(), function name has already been set.');
            }
            if (!function_exists('k')) {
                function k() {
                    call_user_func_array(array('Debug', 'ExpandExit'), func_get_args());
                }
            }
            else {
                self::Log('Cannot use k() for Debug::ExpandExit(), function name has already been set.');
            }
        }
        elseif (self::$lock) {
            trigger_error('Debugger was previously initialized and locked. Cannot override.', E_USER_WARNING);
        }

        // Initialize parameters
        self::$debug = false;
        self::$strict = false;
        self::$verbose = false;
        self::$saveLog = false;
        self::$expandToDebug = false;
        self::$outputOff = false;
        self::$outputStyleOff = false;

        foreach(func_get_args() as $arg) {
            switch ($arg) {
                case self::DEBUG_ON:
                    self::$debug = true;
                    break;
                case self::STRICT_ON:
                    self::$strict = true;
                    break;
                case self::VERBOSE_ON:
                    self::$verbose = true;
                    break;
                case self::SAVE_LOG:
                    self::$saveLog = true;
                    break;
                case self::EXPAND_TO_DEBUG:
                    self::$expandToDebug = true;
                    break;
                case self::OUTPUT_OFF:
                    self::$outputOff = true;
                    break;
                case self::OUTPUT_STYLE_OFF:
                    self::$outputStyleOff = true;
                    break;
                case self::LOCK:
                    self::$lock = true;
                    break;
                default:
                    trigger_error('Invalid Debugger parameter "' . $arg . '"', E_USER_ERROR);
                    break;
            }
        }
                
        if (self::$initialized) {
            self::Log('Debugger was previously initialized. Overriding values.');
            return false;
        }
        else {
            self::$initialized = true;
            return true;
        }
    }

    public static function SetSavePath($path = null)
    {
        if ($path === null) {
            self::$logPath = null;
            self::Log('Debugger log save path reset.');
        }
        else {
            self::$logPath = $path;
            self::Log('Debugger log save path set to: ' . $path);
        }
    }
    
    /**
     * Send message to Debugger
     * @param string $msg Message stored in the Debug Log
     */
    public static function Log($msg = null) {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        if (count($stack) > 1) {
            array_shift($stack); // Remove top level of stack, redundant info
        }
        
        // Check if user is using lazy Debug function l()
        if (isset($stack[1]['function']) && $stack[1]['function'] === 'l') {
            $caller = (isset($msg)) ? 'Debug\Log: ' : 'Debug\Log'; 
        }
        else {
            // Otherwise, Get the function/method that called it
            $caller = (isset($stack[0]['class'])) ? $stack[0]['class'] . "\\" : "[function]\\";
            $caller .= (isset($msg)) ? $stack[0]['function'] . ': ' : $stack[0]['function'];        
        }

        $message = array(
            'type' => 0,
            'message' => $caller . $msg,
            'stack' => $stack
            );
        self::$logs[] = $message;
    }
    
    public static function Timer($message = Debug::TIMER_SET, $label = '')
    {
        $log = null;
        $table = array();
        $valid = Pattern::Validate(Pattern::DEBUG_TIMER_LABEL_NAME, $label);
        if ($valid === false) {
            trigger_error('Invalid Timer Label: '. $label, E_USER_ERROR);
        }
        // Do not run if not in debug mode
        if (!self::$debug) {
            return false;
        }
        // Use default label if none is given
        if($label === '') {
            $label = '[default]';
        }
        // Check if marker has been initialized
        if (!isset(self::$times[$label])) {
            $log = $label . ' Initialized';
        }
        elseif($message === Debug::TIMER_SET || is_string($message)) {
            if (extension_loaded('bcmath')) {
                self::$timerMarkers[$label][$message] = bcsub(self::GetTime(), self::$times[$label], 6);
            }
            else {
                self::$timerMarkers[$label][$message] = self::GetTime() - self::$times[$label];
            }
            $log = sprintf('%s -> %s: %fs', $label, $message, self::$timerMarkers[$label][$message]);
        }
        elseif($message === Debug::TIMER_STATS && isset(self::$timerMarkers[$label])) {
            $base = min(array_filter(self::$timerMarkers[$label], function($x) { return $x > 0; }));
            foreach(self::$timerMarkers[$label] as $event => $duration) {
                $table[] = sprintf("\t\t%5u - %-38.38s <i>%.5fs</i>", round($duration / $base, 2), $event, round($duration, 5));
            }
            $log = $label . " Timer Statistics\n" .
                sprintf( "\t\t%'-61s\t\t %-46s%s\n\t\t%'-61s", PHP_EOL, 'Unit - Description', '<i>Duration</i>', PHP_EOL) .
                implode( PHP_EOL, $table ) . PHP_EOL;
        }
        // Otherwise, do nothing
        else {
            return false;
        }

        self::Log($log);
        self::$times[$label] = self::GetTime();
        return true;
    }

    /**
     * Output detailed description of one or more variables
     * @param mixed $item,...
     * @return null|string
     */
    public static function Expand($item)
    {
        $string = null;
        if (func_num_args() > 1) {
            foreach (func_get_args() as $arg) {
                $string[] = call_user_func(array('Debug', 'Expand'), $arg);
            }

            return implode("\n\n", $string);
        }

        $padding =
            function($number) {
                return str_repeat('  ', $number);
            };

        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($stack);
        $depth = 0;
        for ($j = 0; $j < count($stack); $j++) {
            if ($stack[$j]['function'] == __FUNCTION__) {
                $depth++;
            }
            else {
                break;
            }
        }

        switch (gettype($item)) {
            case 'boolean':
                $string .= ($item) ? '<strong>bool:</strong> true' : '<strong>bool:</strong> false';
                break;
            case 'integer':
                $string .= '<strong>int:</strong> ' . $item;
                break;
            // For "historical reasons," PHP's gettype() function calls floats "double"
            case 'double':
                $string .= '<strong>float:</strong> ' . $item;
                break;
            case 'resource':
                $string .= '<strong>resource:</strong> ' . get_resource_type($item);
                break;
            case 'NULL':
                $string .= '<strong>null</strong>';
                break;
            case 'string':
                $search = array("\0", "\a", "\b", "\f", "\n", "\r", "\t", "\v");
                $replace = array('\0', '\a', '\b', '\f', '\n', '\r', '\t', '\v');
                $item = str_replace($search, $replace, $item);
                // Add slashes to make string immediately reusable for PHP
                $string .= '<strong>string(' . strlen($item) . '):</strong> "' . addslashes($item) . '"';
                break;
            case 'array':
                if (count($item) < 1) {
                    $string .= '<strong>array(0)</strong> {}';
                }
                else {
                    if ($depth > 0) {
                        $string .= "\n" . $padding($depth);
                    }
                    $keys = array_keys($item);
                    $string .= "<strong>array(" . count($item) . ")</strong>\n" . $padding($depth) . '{';
                    foreach($keys as $key) {
                        $index = (is_string($key)) ? '"' . addslashes($key) . '"': $key;
                        $string .= "\n". $padding($depth + 1) . '[' . $index . '] => ' . self::Expand($item[$key]);
                    }
                    $string .= "\n" . $padding($depth) . '}';
                }
                break;
            case 'object':
                $id =
                    function ($object)
                    {
                        if(!is_object($object)) {
                            return false;
                        }
                        ob_start();
                        var_dump($object); // object(foo)#INSTANCE_ID (0) { }
                        preg_match('~^.+?#(\d+)~s', ob_get_clean(), $id);
                        return $id[1];
                    };

                $string .= '<strong>object(' . get_class($item) . ')#' . $id($item) . "</strong>";
                $object = new ReflectionClass($item);
                $properties[] = $object->getProperties();
                $interfaces = $object->getInterfaceNames();
                $parents = array();
                
                // Recursively crawl and catch all properties of parent classes
                while ($parent = $object->getParentClass()) {
                    /** @var $parent ReflectionClass */
                    $parents[] = $parent->getName();
                    $properties[$parent->getName()] = $parent->getProperties(ReflectionProperty::IS_STATIC | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
                    $object= $parent;
                }

                if (!empty($parents)) {
                    $string .= ' extends <strong>(' . implode(', ', $parents) . ')</strong>';
                }
                if (!empty($interfaces)) {
                    $string .= ' implements <strong>(' . implode(', ', $interfaces) . ')</strong>';
                }
                
                $string .= "\n" . $padding($depth) . '{';

                foreach ($properties as $key => $set) {
                    $key = (is_int($key)) ? '': $key . ':';
                    foreach ($set as $prop) {
                        /** @var $prop ReflectionProperty */
                        $prop->setAccessible(true);
                        if ($prop->isPrivate()) {
                            $visibility = 'private:';
                        }
                        elseif ($prop->isProtected()) {
                            $visibility = 'protected:';
                        }
                        else {
                            $visibility = 'public:';
                        }
                        if ($prop->isStatic()) {
                            $visibility .= 'static:';
                        }
                        $string .= "\n" . $padding($depth + 1) . '[' . $key . $visibility . '"' . $prop->getName() . '"] => ' . self::Expand($prop->getValue($item));
                    }
                }
                $string .= "\n" . $padding($depth) . '}';
                break;
            case 'unknown type':
            default:
                $string .= '<strong>[unknown type]:</strong> ' . serialize($item);
                break;
        }

        if(self::$expandToDebug) {
            Debug::Log($string);
            return null;
        }
        return $string;
    }


    /**
     * Output detailed description of one or more variables, then end execution
     * @param mixed $item,...
     */
    public static function ExpandExit($item)
    {
        $string = call_user_func_array(array('Debug', 'Expand'), func_get_args());
        if(!self::$expandToDebug) {
            echo "<pre>\n" . $string . "\n</pre>";
        }
        exit;
    }

    /**
     * Handler for the end of execution.
     * Triggered by an unhandled error or the end of execution.
     */
    public static function ExecutionEnd() {
        // The following error types cannot be handled with a user defined function:
        $executionEndTime = gettimeofday(true);
        $criticalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        $error = error_get_last();
        $lastError = null;
        
        // Check if a fatal error killed the process
        if (in_array($error['type'], $criticalErrors))
        {
            $error['message'] = htmlentities($error['message']);
            $lastError = '<b>EXECUTION ENDED BY FATAL ERROR</b> in <b>' . $error['file'] . '(' . $error['line'] . '):</b> '. $error['message'] . "\n";
            self::$verbose = self::VERBOSE_ON;
            self::$debug = self::DEBUG_ON;
        }
        
        // Show messages if anything > Message occurs or log is set in debug mode
        $showMessages = false;
        if (self::$debug && !empty(self::$logs)) {
            $showMessages = true;
        }
        else {
            foreach(self::$logs as $msg) {
                if ($msg['type'] > 0) {
                    $showMessages = true;
                    break;
                }
            }
        }

        $text = (self::$verbose) ? "\n<pre style=\"background-color: #ffffff; color: #000000;\">\n": "\n\n<!--\n";
        // Output server information
        $server_info = (empty($_SERVER['SERVER_SOFTWARE'])) ? PHP_SAPI . '; PHP' . PHP_VERSION . ' (' . PHP_OS . ')' : $_SERVER['SERVER_SOFTWARE']; 
        $text .= $lastError . "\nSERVER INFO: " . $server_info .
            "\nPEAK MEM USAGE: " . (memory_get_peak_usage() / 1024) .
            "kb\nSCRIPT EXECUTION TIME: " . ($executionEndTime - self::$initTime) .
            "s\nCLIENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

        $exportXML = array(
            '@attributes' => array(
                'version' => '0.1.0',
                'server' => htmlentities($server_info, ENT_QUOTES),
                'datetime' => Date::Now()->ToString(),
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:noNamespaceSchemaLocation' => 'logsetSchema.xsd'
            ),
            'log' => array()
        );

        $j = 1;
        for($i = 0; $i < count(self::$logs); $i++)
        {
            $msg = self::$logs[$i];
            if (!self::$debug && $msg['type'] == 0) {
                continue;
            }

            $log = array(
                '@attributes' => array(
                    'type' => null,
                    'number' => $j
                ),
                'severity' => array(
                    '@attributes' => array(
                        'value' => $msg['type'],
                    ),
                    '@value' => null
                ),
                'description' => $msg['message'],
                'stack' => array(
                    'method' => array()
                )
            );

            $text .= "\n\n#" . ($j++) . ' ';
            switch($msg['type'])
            {
                case 0:
                    $text .= "MESSAGE\t- ";
                    $log['@attributes']['type'] = 'message';
                    $log['severity']['@value'] = 'NONE';
                break;

                case E_WARNING:
                case E_USER_WARNING:
                case E_DEPRECATED:
                case E_USER_DEPRECATED:
                case E_STRICT:
                    $text .= "<b style=\"color: #0000cd\">WARNING</b>\t- ";
                $log['@attributes']['type'] = 'warning';
                $log['severity']['@value'] = 'WARNING';
                break;

                case E_NOTICE:
                case E_USER_NOTICE:
                    $text .= "<b style=\"color: #0000cd\">NOTICE</b> \t- ";
                $log['@attributes']['type'] = 'notice';
                $log['severity']['@value'] = 'NOTICE';
                break;

                case E_USER_ERROR:
                case E_RECOVERABLE_ERROR:
                default:
                    $text .= "<b style=\"color: #cd0000\">ERROR</b>  \t- ";
                    $log['@attributes']['type'] = 'error';
                    $log['severity']['@value'] = 'ERROR';
                break;
            }

            $text .= $msg['message'] . "\n\t\tStack trace:";

            // [MUSTCHANGE] handling of stack trace for display and logging in cases where stack is empty
            // should change into DO-WHILE method
            for($k = 0, $length = count($msg['stack']); $k < $length; $k++)
            {
                $stack = $msg['stack'][$k];
                if (!isset($stack['file']) || !isset($stack['line'])) {
                    $stack['file'] = '[internal] ' . $msg['stack'][($k + 1)]['file'];
                    $stack['line'] = $msg['stack'][($k + 1)]['line'];
                }

                $text .= "\n\t\t#" . ($k + 1) . ' ' . $stack['file'] . '(' . $stack['line'] . '): ';
                $caller = null;

                if (isset($stack['class'])) {
                    $caller .= $stack['class'];
                }
                if (isset($stack['type'])) {
                    $caller .= $stack['type'];
                }
                $caller .= $stack['function'] . '()';
                $text .= $caller;

                $log['stack']['method'][] = array(
                    '@attributes' => array(
                        'path' => $stack['file'],
                        'line' => $stack['line']
                    ),
                    '@value' => $caller
                );
            }
            
            if (empty($msg['stack'])) {
                $text .= "\n\t\t#1 {main}";
            } 

            $exportXML['log'][] = $log;
        }

        /* Output error and debug messages */
        if ($showMessages)
        {
            echo $text;
            echo (self::$verbose) ? "\n</pre>": "\n-->";
        }

        if (self::$saveLog) {
            try {
                // check if partnering XML class exists, if not, throw up to output to default error_log
                self::$logPath .= '\log-' . Date::Now()->ToString() . '.xml';
                $xml = XML::createXML('logset', $exportXML);
                $xml->save(self::$logPath);
            }
            catch(Exception $e) {
                echo "<pre>\n\n#". $j. "<b style=\"color: #cd0000\">ERROR</b>\t- Could not export log to XML file '" . self::$logPath . "'. Saving log to the PHP error log.</pre>";
                error_log($text, 1);
            }
        }
        exit;
    }

    /**
     * Custom Error Handler to log and output more human-friendly errors.
     * Converts catchable errors into exceptions so they can be handled by developers.
     * @param int $err_severity
     * @param string $err_msg
     * @param string $err_file
     * @param int $err_line
     * @param array $err_context
     * @return bool
     */
    public static function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context)
    {
        // The following error types cannot be handled with a user defined function:
        // E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING
        $errors = array(E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE,
            E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_USER_DEPRECATED,
            E_STRICT, E_USER_ERROR, E_RECOVERABLE_ERROR);
        
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($stack);
        
        $message = array('type' => $err_severity, 'stack' => $stack, 'context' => null);
        $err_msg = htmlentities($err_msg);

        if (in_array($err_severity, $errors)) {
            $message['message'] = '<b>'. $err_file . '(' .$err_line . '):</b> ' . $err_msg;
            if ($err_severity == E_USER_ERROR || $err_severity == E_RECOVERABLE_ERROR) {
                $message['context'] = $err_context;
            }
        }
        else {
            $message['message'] = '<b>UNKNOWN ERROR TYPE</b> in <b>'. $err_file . '(' .$err_line . '):</b> ' . $err_msg;
        }

        self::$logs[] = $message;

        if (self::$strict || $err_severity == E_USER_ERROR || $err_severity == E_RECOVERABLE_ERROR) {
            self::ThrowException($err_severity, $err_msg, $err_file, $err_line, $err_context);
        }

        // Don't execute PHP internal error handler
        return true;
    }
    
    /**
     * Debug Exception Handler to suppress default FATAL ERROR message on uncaught exceptions
     * @param Exception $e
     */
    public static function ExceptionHandler(Exception $e)
    {
        echo "<pre>\nUncaught <b>" . get_class($e) . '</b> thrown in <b>' . $e->getFile() . ':' . $e->getLine() .
            "</b>\n\twith message <b>'" . $e->getMessage() . "'</b>\n\tStack trace:\n\n" . $e->getTraceAsString();

        if (is_a($e, 'ErrorExceptionWithContext')) {
            /** @var $e ErrorExceptionWithContext */
            $context = $e->getErrorContext();
            if (!empty($context)) {
                echo "</b>\n\n\tError Context:\n\n" . $e->getErrorContextAsString();
            }
            
        }

        echo "</pre>";
    }

    /**
     * Produces time in microseconds
     * @return string|float
     */
    public static function GetTime()
    {
        if (extension_loaded('bcmath')) {
            return vsprintf('%d.%06d', gettimeofday());
        }
        else {
            return gettimeofday(true);
        }
    }

    /**
     * Throwing an exception related to the type of error that triggered it
     * @param int $err_severity
     * @param string $err_msg
     * @param string $err_file
     * @param int $err_line
     * @param array $err_context                    
     * @throws ErrorException
     * @return bool
     */
    private static function ThrowException($err_severity, $err_msg, $err_file, $err_line, array $err_context)
    {
        // The following error types cannot be handled with a user defined function:
        // E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING
        
        /*/ error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        //*/

        switch ($err_severity)
        {
            case E_WARNING:             throw new WarningException          ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_NOTICE:              throw new NoticeException           ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_USER_ERROR:          throw new UserErrorException        ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_USER_WARNING:        throw new UserWarningException      ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_USER_NOTICE:         throw new UserNoticeException       ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_STRICT:              throw new StrictException           ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_RECOVERABLE_ERROR:   throw new RecoverableErrorException ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_DEPRECATED:          throw new DeprecatedException       ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            case E_USER_DEPRECATED:     throw new UserDeprecatedException   ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
            default:                    throw new UnknownErrorException     ($err_msg, 0, $err_severity, $err_file, $err_line, $err_context);
        }
        /* Don't execute PHP internal error handler */
        //return true;
    }
}


/**
 * Wrapper for Exceptions caused by E_WARNING
 * Default PHP naming conventions are used for stylistic consistency 
 * @package Debug\Exception\Warning
 */
class ErrorExceptionWithContext extends ErrorException
{
    protected $errorContext = array();

    public function __construct($message, $code, $severity, $filename, $lineno, $err_context = null) {
        parent::__construct($message, $code, $severity, $filename, $lineno);
        $this->errorContext = $err_context;
    }

    public function getErrorContext()
    {
        return $this->errorContext;
    }

    public function getErrorContextAsString()
    {
        $context = null;
        foreach ($this->errorContext as $name => $value) {
            $context .= '$' . $name . ': ' . Debug::Expand($value) . "\n";
        }
        return $context;
    }
}

/**
 * Wrapper for Exceptions caused by E_WARNING 
 * @package Debug\Exception\Warning
 */
class WarningException              extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_NOTICE
 * @package Debug\Exception\Notice
 */
class NoticeException               extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_USER_ERROR 
 * @package Debug\Exception\UserError
 */
class UserErrorException            extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_USER_WARNING 
 * @package Debug\Exception\UserWarning
 */
class UserWarningException          extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_USER_NOTICE 
 * @package Debug\Exception\UserNotice
 */
class UserNoticeException           extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_STRICT
 * @package Debug\Exception\Strict
 */
class StrictException               extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_RECOVERABLE_ERROR
 * @package Debug\Exception\Recoverable
 */
class RecoverableErrorException     extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_DEPRECATED
 * @package Debug\Exception\Deprecated
 */
class DeprecatedException           extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by E_USER_DEPRECATED
 * @package Debug\Exception\UserDeprecated
 */
class UserDeprecatedException       extends ErrorExceptionWithContext {}

/**
 * Wrapper for Exceptions caused by unknown errors 
 * @package Debug\Exception\Unknown
 */
class UnknownErrorException         extends ErrorExceptionWithContext {}

?>