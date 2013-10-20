<?php
/**
 * Class.Debug
 * @package SMPL\Debug
 */

/**
 * Debug Class
 * Procedure: All issues throw a PHP standard error type. Debug error handler turns Errors into Exceptions that can be caught.
 * Warnings and Notices only throw exceptions in Strict Mode. 
 * @package Debug
 */
class Debug
{
    /* Setting Constants */
    const DEBUG_ON = true;
    const DEBUG_OFF = false;
    const STRICT_ON = true;
    const STRICT_OFF = false;
    const VERBOSE_ON = true;
    const VERBOSE_OFF = false;
    const LOGGING_ON = true;
    const LOGGING_OFF = false;
    
    private static $debugMode = false;       // Debug messages are logged
    private static $isStrict = false;        // TRUE: All errors pass as exceptions | FALSE: Notices and Warnings passed as messages
    private static $isVerbose = false;       // Debug errors are printed to screeen (on false, debug errors are passed as HTML5 comments)
    private static $isLogging = false;       // Debug messages and errors are stored in text file (XML Format)
    private static $logPath = null;          // Path to error log directory
    
    private static $log = array();     // Debug Messages
    private static $exceptions = array();   // Debug errors and exceptions
    
    public static function Set($setDebugMode, $setStrict, $setVerbose, $setLogging, $logPath = null)
    {
        self::$debugMode = $setDebugMode;
        self::$isStrict = $setStrict;
        self::$isVerbose = $setVerbose;
        self::$isLogging = $setLogging;
        self::$logPath = $logPath;
        
        error_reporting(-1);
        set_error_handler(array('Debug', 'ErrorHandler'));
        register_shutdown_function(array('Debug', 'EndOfExecution'));
    }
    
    // Send a message to the debug log
    public static function Message($msg = null)
    {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($stack); // Remove top level of stack, redundant info
            $message = array(
                'type' => 0,
                'message' => $msg,
                'stack' => $stack
                );           
            array_push(self::$log, $message);
        
        return;
    }
    
    public static function EndOfExecution() {
        // The following error types cannot be handled with a user defined function:
        $criticalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        $error = error_get_last(); // Check if a fatal error killed the process
        $lastError = null;
        if (in_array($error['type'], $criticalErrors))
        {
            $lastError = "<b>EXECUTION ENDED BY FATAL ERROR</b> in <b>{$error['file']}({$error['line']}):</b> {$error['message']}\n";
            self::$isVerbose = true;
            self::$debugMode = true;
        }
        
        // Show messages if a warning occurs or log is set in debug mode
        $showMessages = false;
        if(self::$debugMode && isset(self::$log))
        {
            $showMessages = true;
        }
        else
        {
            foreach(self::$log as $msg)
            {
                if($msg['type'] > 0)
                {
                    $showMessages = true;
                    break;
                }
            }
        }
        
        /* Output error and debug messages */
        if(self::$debugMode)
        {
            echo (self::$isVerbose) ? "\n\n<pre>\n": "\n\n<!--\n";
            echo $lastError;
            echo "Server Specs: PHP " . PHP_VERSION . " (" . PHP_OS . "); PEAK MEM USAGE: ".(memory_get_peak_usage() / 1024)."kb\n"; // Should this include information about the database? Is so, need to make interface
            $idx = 1;        
            
            for($i = 0; $i < count(self::$log); $i++)
            {
                $msg = self::$log[$i];
                if(!self::$debugMode && $msg['type'] == 0)
                    continue;
                    
                $text = "\n\n#".($idx++).' ';
                switch($msg['type'])
                {
                    case 0:
                        $text .= "MESSAGE\t- ";
                        break;
                    case E_WARNING:
                        $text .= "WARNING\t- ";
                        break;
                    case E_ERROR:                
                    case E_PARSE:
                    case E_NOTICE:
                    case E_CORE_ERROR:
                    case E_CORE_WARNING:
                    case E_COMPILE_ERROR:
                    case E_COMPILE_WARNING:
                    case E_USER_ERROR:
                    case E_USER_WARNING:
                    case E_USER_NOTICE:
                    case E_STRICT:
                    case E_RECOVERABLE_ERROR:
                    case E_DEPRECATED:
                    case E_USER_DEPRECATED:
                        $text .= "ERROR\t- ";
                        break;
                }
                
                $text .= $msg['message']."\n\t\tStack trace:";
    
                for($j = 0; $j < count($msg['stack']); $j++)
                {
                    $stack = $msg['stack'][$j];
                    $text .= "\n\t\t#".($j + 1)." {$stack['file']}({$stack['line']}): ";
                    if(isset($stack['class']))
                        $text .= $stack['class'];
                    if(isset($stack['type']))
                        $text .= $stack['type'];
                    $text .= $stack['function'].'()';
                }
                
                echo $text;
            }
    
            echo (self::$isVerbose) ? "\n</pre>": "\n-->";
        }
        
        
        // If Log is to be stored, do that.
    }

    // error handler function
    public static function ErrorHandler($err_severity, $err_msg, $err_file, $err_line, array $err_context)
    {
        // The following error types cannot be handled with a user defined function:
        // E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_STRICT(?)
        
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($stack);
        $message = array('type' => $err_severity);
        
        switch ($err_severity) {
        case E_USER_ERROR:
            echo "<b>FATAL ERROR</b> in <b>{$err_file}({$err_line}):</b> $err_msg<br />\n";
            echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
            echo "Aborting...<br />\n";
            exit(1);
            break;
    
        case E_WARNING:
        case E_USER_WARNING:
            $message['message'] = "<b>WARNING</b> in <b>{$err_file}({$err_line}):</b> $err_msg";
            $message['stack'] = $stack;            
            array_push(self::$log, $message);
            break;
            
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
        case E_STRICT:
            $message['message'] = "<b>NOTICE</b> in <b>{$err_file}({$err_line}):</b> $err_msg";
            $message['stack'] = $stack;            
            array_push(self::$log, $message);
            break;
            
        default:
            echo "<b>UNKOWN ERROR TYPE</b> in <b>{$err_file}({$err_line}):</b> $err_msg";
            break;
        }
        
        if(self::$isStrict)
            Debug::ThrowException($err_severity, $err_msg, $err_file, $err_line, $err_context);

        // Don't execute PHP internal error handler
        return true;
    }

    // Throw Exceptions based on error type
    public static function ThrowException($err_severity, $err_msg, $err_file, $err_line, array $err_context)
    {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) { return false; }
        switch($err_severity)
        {
            case E_ERROR:               throw new ErrorException            ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_WARNING:             throw new WarningException          ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_PARSE:               throw new ParseException            ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_NOTICE:              throw new NoticeException           ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_CORE_ERROR:          throw new CoreErrorException        ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_CORE_WARNING:        throw new CoreWarningException      ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_COMPILE_ERROR:       throw new CompileErrorException     ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_COMPILE_WARNING:     throw new CoreWarningException      ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_ERROR:          throw new UserErrorException        ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_WARNING:        throw new UserWarningException      ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_NOTICE:         throw new UserNoticeException       ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_STRICT:              throw new StrictException           ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_RECOVERABLE_ERROR:   throw new RecoverableErrorException ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_DEPRECATED:          throw new DeprecatedException       ($err_msg, 0, $err_severity, $err_file, $err_line);
            case E_USER_DEPRECATED:     throw new UserDeprecatedException   ($err_msg, 0, $err_severity, $err_file, $err_line);
        }
        /* Don't execute PHP internal error handler */
        return true;
    }
}

// Create exception classes for every type of error
class WarningException              extends ErrorException {}
class ParseException                extends ErrorException {}
class NoticeException               extends ErrorException {}
class CoreErrorException            extends ErrorException {}
class CoreWarningException          extends ErrorException {}
class CompileErrorException         extends ErrorException {}
class CompileWarningException       extends ErrorException {}
class UserErrorException            extends ErrorException {}
class UserWarningException          extends ErrorException {}
class UserNoticeException           extends ErrorException {}
class StrictException               extends ErrorException {}
class RecoverableErrorException     extends ErrorException {}
class DeprecatedException           extends ErrorException {}
class UserDeprecatedException       extends ErrorException {}

?>