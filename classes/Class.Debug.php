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
    const DEBUG_ON = true;
    
    /**
     * Turn off Debugger Messages
     */
    const DEBUG_OFF = false;
    
    /**
     * Turn on Strict Debugging: E_WARNING, E_USER_WARNING, E_NOTICE, E_USER_NOTICE, 
     * E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_USER_DEPRECATED, E_STRICT
     * will generate Exceptions 
     */
    const STRICT_ON = true;
    
    /**
     * Turn on Strict Debugging, only E_USER_ERROR, E_RECOVERABLE_ERROR will
     * generate exceptions     
     */
    const STRICT_OFF = false;
    
    /**
     * Turn on Verbose Output, log encapsulated in <pre> tag at the end of execution
     */
    const VERBOSE_ON = true;
    
    /**
     * Turn off Verbose Output, log encapsulated in <!-- --> tag at the end of execution
     */
    const VERBOSE_OFF = false;
    
    /**
     * Turn on Debugger Logging, output is stored in flat file
     */
    const LOGGING_ON = true;
    
    /**
     * Turn on Debugger Logging, output is not stored
     */
    const LOGGING_OFF = false;
    
   /**
    * Use Debug Messages
    * @var bool $isDebug
    */
    private static $isDebug = false;
    
   /**
    * Use Strict Debugging
    * @var bool $isStrict
    */
    private static $isStrict = false;
    
   /**
    * Use Verbose Output
    * @var bool $isVerbose
    */
    private static $isVerbose = false;
    
   /**
    * Use Debug Logging
    * @var bool $isLogging
    */
    private static $isLogging = false;
    
   /**
    * Stores whether the Debug has already been set    
    * @var bool $isInitialized                                              
    */
    private static $isInitialized = false;
    
   /**
    * Debug log for messages and errors
    * @var array $log
    */
    private static $log = array();
    
   /**
    * Path to the file to store the log in
    * @todo Currently logging to error_log, eventually log to XML flatfile    
    * @var string $logPath                                              
    */
    private static $logPath = null;

    /**
     * Private constructor so that Debug cannot be instantiated
     * @return Debug
     */
    private function __construct() {} 
    
    /**
     * Initialize (or Reset) Debugger settings and set custom handlers
     * @param bool $setDebugMode TRUE => Store Debug messages in log
     * @param bool $setStrict TRUE => All errors pass as exceptions | FALSE => Notices and Warnings passed as messages
     * @param bool $setVerbose Debug errors are printed to screen (on false, debug errors are passed as HTML5 comments)
     * @param bool $setLogging TRUE => Store Debug in XML file
     * @param string $logPath Path to error log directory
     * @return bool Returns TRUE on initial use, FALSE on subsequent uses     
     */
    public static function Set($setDebugMode, $setStrict, $setVerbose, $setLogging, $logPath = null)
    {
        self::$isDebug = $setDebugMode;
        self::$isStrict = $setStrict;
        self::$isVerbose = $setVerbose;
        self::$isLogging = $setLogging;
        self::$logPath = $logPath;
        
        if (self::$isInitialized) {
            self::Message('Debugger was reset.');
            return false;
        }
        else {
            error_reporting(-1);
            set_error_handler(array('Debug', 'ErrorHandler'));
            set_exception_handler(array('Debug', 'ExceptionHandler'));
            register_shutdown_function(array('Debug', 'ExecutionEnd'));
            return true;
        }
    }
    
    /**
     * Send message to Debugger
     * @param string $msg Message stored in the Debug Log
     */
    public static function Message($msg = null)
    {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($stack); // Remove top level of stack, redundant info

            // Get the function/method that called it
            $caller = (isset($stack[0]['class'])) ? $stack[0]['class'] . "\\" : NULL;
            $caller .= (isset($msg)) ? $stack[0]['function'] . ': ' : $stack[0]['function'];       
            $message = array(
                'type' => 0,
                'message' => $caller . $msg,
                'stack' => $stack
                );           
            self::$log[] = $message;
    }
    
    /**
     * Handler for the end of execution.
     * Triggered by an unhandled error or the end of execution.
     * @todo Implement XML for Debug Logging     
     */
    public static function ExecutionEnd() {
        // The following error types cannot be handled with a user defined function:
        $criticalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        $error = error_get_last();
        $lastError = null;
        
        // Check if a fatal error killed the process
        if (in_array($error['type'], $criticalErrors))
        {
            $error['message'] = htmlentities($error['message']);
            $lastError = '<b>EXECUTION ENDED BY FATAL ERROR</b> in <b>' . $error['file'] . '(' . $error['line'] . '):</b> '. $error['message'] . "\n";
            self::$isVerbose = self::VERBOSE_ON;
            self::$isDebug = self::DEBUG_ON;
        }
        
        // Show messages if anything > Message occurs or log is set in debug mode
        $showMessages = false;
        if (self::$isDebug && !empty(self::$log)) {
            $showMessages = true;
        }
        else {
            foreach(self::$log as $msg) {
                if ($msg['type'] > 0) {
                    $showMessages = true;
                    break;
                }
            }
        }
        
        /* Output error and debug messages */
        if ($showMessages)
        {
            echo (self::$isVerbose) ? "\n\n<pre>\n": "\n\n<!--\n";
            echo $lastError;
            
            // Should this include information about the database? Is so, need to make interface
            echo 'Server Specs: PHP ' . PHP_VERSION . ' (' . PHP_OS . 
                '); PEAK MEM USAGE: ' . (memory_get_peak_usage() / 1024) . "kb\n";
            $j = 1;
            
            for($i = 0; $i < count(self::$log); $i++)
            {
                $msg = self::$log[$i];
                if (!self::$isDebug && $msg['type'] == 0) {
                    continue;
                }
                    
                $text = "\n\n#" . ($j++) . ' ';
                switch($msg['type'])
                {
                    case 0:
                        $text .= "MESSAGE\t- ";
                    break;
                        
                    case E_WARNING:
                    case E_USER_WARNING:
                        $text .= "<b>WARNING</b>\t- ";
                    break;
                        
                    case E_NOTICE:
                    case E_USER_NOTICE:
                    case E_DEPRECATED:
                    case E_USER_DEPRECATED:
                    case E_STRICT:
                        $text .= "<b>NOTICE</b> \t- ";
                    break;
                    
                    case E_USER_ERROR:
                    case E_RECOVERABLE_ERROR:
                    default:
                        $text .= "<b>ERROR</b>  \t- ";
                    break;
                }
                
                $text .= $msg['message'] . "\n\t\tStack trace:";
    
                for($k = 0; $k < count($msg['stack']); $k++)
                {
                    $stack = $msg['stack'][$k];
                    $text .= "\n\t\t#" . ($k + 1) . ' ' . $stack['file'] . '(' . $stack['line'] . '): ';
                    if (isset($stack['class'])) {
                        $text .= $stack['class'];
                    }
                    if (isset($stack['type'])) {
                        $text .= $stack['type'];
                    }
                    $text .= $stack['function'] . '()';
                }
                
                echo $text;

                // If set, append to standard php log, eventually output to XML [MUSTCHANGE]
                // Also utilize path.
                if (self::$isLogging) {
                    error_log($text, 1);
                }
            }
    
            echo (self::$isVerbose) ? "\n</pre>": "\n-->";
        }
    }

    /**
     * Custom Error Handler to log and output more human-friendly errors.
     * Converts catchable errors into exceptions so they can be handled by developers.
     * @todo Implement Error Context array to provide more detailed information for debugging. Maybe only output on Debug Log                
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
            
        // $err_context is an array including all variables that existed in the scope that the error was triggered
        // How best and when to implement? [MUSTCHANGE]
        
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($stack);
        $message = array('type' => $err_severity, 'stack' => $stack);
        $err_msg = htmlentities($err_msg);

        if (in_array($err_severity, $errors)) {
            $message['message'] = '<b>'. $err_file . '(' .$err_line . '):</b> ' . $err_msg;
        }
        else {
            $message['message'] = '<b>UNKNOWN ERROR TYPE</b> in <b>'. $err_file . '(' .$err_line . '):</b> ' . $err_msg;
        }
        
        self::$log[] = $message;
        
        if (self::$isStrict || $err_severity === E_USER_ERROR || $err_severity === E_RECOVERABLE_ERROR) {
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
            "</b>\n\twith message <b>'" . $e->getMessage() . "'</b>\n\tStack trace:\n\n" . $e->getTraceAsString() . "\n\n</pre>";
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
        
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        
        switch ($err_severity)
        {
            case E_WARNING:             throw new WarningException          ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_NOTICE:              throw new NoticeException           ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_ERROR:          throw new UserErrorException        ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_WARNING:        throw new UserWarningException      ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_NOTICE:         throw new UserNoticeException       ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_STRICT:              throw new StrictException           ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_RECOVERABLE_ERROR:   throw new RecoverableErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_DEPRECATED:          throw new DeprecatedException       ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_DEPRECATED:     throw new UserDeprecatedException   ($err_msg, 0, $err_severity, $err_file, $err_line);
            default:                    throw new UnknownErrorException     ($err_msg, 0, $err_severity, $err_file, $err_line);
        }
        /* Don't execute PHP internal error handler */
        //return true;
    }
}

/**
 * Wrapper for Exceptions caused by E_WARNING 
 * @package Debug\Exception\Warning
 */
class WarningException              extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_NOTICE
 * @package Debug\Exception\Notice
 */
class NoticeException               extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_USER_ERROR 
 * @package Debug\Exception\UserError
 */
class UserErrorException            extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_USER_WARNING 
 * @package Debug\Exception\UserWarning
 */
class UserWarningException          extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_USER_NOTICE 
 * @package Debug\Exception\UserNotice
 */
class UserNoticeException           extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_STRICT
 * @package Debug\Exception\Strict
 */
class StrictException               extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_RECOVERABLE_ERROR
 * @package Debug\Exception\Recoverable
 */
class RecoverableErrorException     extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_DEPRECATED
 * @package Debug\Exception\Deprecated
 */
class DeprecatedException           extends ErrorException {}

/**
 * Wrapper for Exceptions caused by E_USER_DEPRECATED
 * @package Debug\Exception\UserDeprecated
 */
class UserDeprecatedException       extends ErrorException {}

/**
 * Wrapper for Exceptions caused by unknown errors 
 * @package Debug\Exception\Unknown
 */
class UnknownErrorException         extends ErrorException {}

?>