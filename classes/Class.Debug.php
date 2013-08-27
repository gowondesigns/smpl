<?php
/* SMPL Debug Class
// Error Handling functions and Debug Messaging and Logging
//
//*/

class Debug
{
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
    }
    
    public static function Message($msg = null)
    {
        if(self::$debugMode)
        {
            $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($stack);
            $message = array(
                'type' => 0,
                'message' => $msg,
                'stack' => $stack
                );           
            array_push(self::$log, $message);
        }
        
        return;
    }
    
    public static function EndOfExecution() {
        // The following error types cannot be handled with a user defined function:
        $criticalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING);
        $error = error_get_last();
        if (in_array($error['type'], $criticalErrors)) {
            print_r($error);
        }
        
        //[MUSTCHANGE] This whole section is lazy
        echo (self::$isVerbose) ? "\n\n<pre>\n": "\n\n<!--\n";
        echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")\n";
        print_r(Debug::$log); //[MUSTCHANGE] lazy
        echo (self::$isVerbose) ? "\n</pre>": "\n-->";
        
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
            $message['message'] = "<b>WARNING</b> in <b>{$err_file}({$err_line}):</b> $err_msg<br />\n";
            $message['stack'] = $stack;            
            array_push(self::$log, $message);
            break;
            
        case E_NOTICE:
        case E_USER_NOTICE:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
        case E_STRICT:
            $message['message'] = "<b>NOTICE</b> in <b>{$err_file}({$err_line}):</b> $err_msg<br />\n";
            $message['stack'] = $stack;            
            array_push(self::$log, $message);
            break;
            
        default:
            echo "<b>UNKOWN ERROR TYPE</b> in <b>{$err_file}({$err_line}):</b> $err_msg<br />\n";
            break;
        }
        
        //debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //Debug::ThrowException($err_severity, $err_msg, $err_file, $err_line, $err_context);
        // Don't execute PHP internal error handler
        return true;
    }

    // error handler to throw exceptions
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