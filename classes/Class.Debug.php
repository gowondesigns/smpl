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
     * @var string $logPath                                              
     */
    private static $logPath = null;
    
    /**
     * Float containing the time of execution in microseconds
     * @var float $executionStartTime                                            
     */
    private static $executionStartTime = null;

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
        self::$executionStartTime = gettimeofday(true);
        self::$isDebug = $setDebugMode;
        self::$isStrict = $setStrict;
        self::$isVerbose = $setVerbose;
        self::$isLogging = $setLogging;
        self::$logPath = $logPath;
        
        if (self::$isInitialized) {
            self::Message('Debugger was previously initialized. Overriding values.');
            return false;
        }
        else {
            error_reporting(0); //Completely disable PHP messaging
            set_error_handler(array('Debug', 'ErrorHandler'));
            set_exception_handler(array('Debug', 'ExceptionHandler'));
            register_shutdown_function(array('Debug', 'ExecutionEnd'));
            self::$isInitialized = true;
            return true;
        }
    }
    
    /**
     * Send message to Debugger
     * @param string $msg Message stored in the Debug Log
     */
    public static function Message($msg = null) {
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

        $text = (self::$isVerbose) ? "\n\n<pre style=\"background-color: #ffffff; color: #000000;\">\n": "\n\n<!--\n";
        // Should this include information about the database? Is so, need to make interface
        $text .= $lastError . "\nServer Specs: PHP " . PHP_VERSION . ' (' . PHP_OS .
            '); PEAK MEM USAGE: ' . (memory_get_peak_usage() / 1024) .
            'kb; SCRIPT EXECUTION TIME: ' . ($executionEndTime - self::$executionStartTime) . "s\n" .
            'CLIENT: ' . $_SERVER['HTTP_USER_AGENT'] . "\n";

        $exportXML = array(
            '@attributes' => array(
                'version' => '0.1.0',
                'datetime' => Date::Now()->ToString(),
                'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                'xsi:noNamespaceSchemaLocation' => 'logsetSchema.xsd'
            ),
            'log' => array()
        );

        $j = 1;
        for($i = 0; $i < count(self::$log); $i++)
        {
            $msg = self::$log[$i];
            if (!self::$isDebug && $msg['type'] == 0) {
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

            for($k = 0, $length = count($msg['stack']); $k < $length; $k++)
            {
                $stack = $msg['stack'][$k];
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

            $exportXML['log'][] = $log;
        }

        /* Output error and debug messages */
        if ($showMessages)
        {
            echo $text;
            echo (self::$isVerbose) ? "\n</pre>": "\n-->";
        }

        if (self::$isLogging) {
            try {
                self::$logPath .= '\log-' . Date::Now()->ToString() . '.xml';
                $xml = XML::createXML('logset', $exportXML);
                $xml->save(self::$logPath);
            }
            catch(Exception $e) {
                echo "<pre>\n\n#". $j. "<b style=\"color: #cd0000\">ERROR</b>\t- Could not export log to XML file '" . self::$logPath . "'. Saving log to the PHP error log.</pre>";
                error_log($text, 1);
            }
        }
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

        self::$log[] = $message;

        if (self::$isStrict || $err_severity == E_USER_ERROR || $err_severity == E_RECOVERABLE_ERROR) {
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
            echo "'</b>\n\n\tError Context:\n\n" . $e->getErrorContextAsString();
        }

        echo "\n</pre>";
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
 * @package Debug\Exception\Warning
 */
class ErrorExceptionWithContext extends ErrorException
{
    protected $errorContext;

    public function __construct($message, $code, $severity, $filename, $lineno, $err_context = null) {
        //public __construct ([ string $message = "" [, int $code = 0 [, int $severity = 1 [, string $filename = __FILE__ [, int $lineno = __LINE__ [, Exception $previous = NULL ]]]]]] )
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
            $context .= '$' . $name . ': ';
            if ($value === null) {
                $context .= "NULL;\n";
            }
            else {
                $context .= serialize($value) . "\n";
            }
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