<?php
require_once "Show.php";
require_once "DatabaseHelper.php";

function ignore_error($message)
{
    $messages = [
        // We don't have control over this extension
        'Creation of dynamic property Memcache::$connection is deprecated',
    ];
    foreach ($messages as $needle) {
        if (strpos($message, $needle) !== false) {
            return true;
        }
    }
    return false;
}
function log_error($type, $message, $path, $line, $stack_trace = null)
{
    if (ignore_error($message)) {
        return true;
    }
    try {
        $credentials = false; // Secrets::revealAs("ERROR_DATABASE", 'array');
        if ($credentials === false) {
            return false;
        }
        $db = new DatabaseHelper($credentials);

        $log_id = $db->selectScalar(
            'CALL sp_log(?, ?, ?, ?, ?, ?)',
            'sisssi',
            $_SERVER['SERVER_NAME'],
            time(),
            $type,
            $message,
            $path,
            $line
        );

        if ($log_id === false) {
            return false;
        }

        if ($stack_trace === null) {
            return true;
        }

        $db->affectAny(
            "CALL sp_log_details(?, ?, ?)",
            'iss',
            $log_id,
            'Stack Trace',
            $stack_trace
        );

        return true;
    } catch (Exception $e) {
        return false;
    }

}
function error_number_as_type($errno)
{
    switch ($errno) {
        case E_DEPRECATED: return 'PHP Deprecated';
        case E_ERROR: return 'PHP Error';
        case E_WARNING: return 'PHP Warning';
        case E_PARSE: return 'PHP Parse';
        case E_NOTICE: return 'PHP Notice';
        case E_CORE_ERROR: return 'PHP Core Error';
        case E_CORE_WARNING: return 'PHP Core Warning';
        case E_USER_ERROR: return 'PHP User Error';
        case E_USER_WARNING: return 'PHP User Warning';
        case E_USER_NOTICE: return 'PHP User Notice';
        case E_STRICT: return 'PHP Strict';
        case E_RECOVERABLE_ERROR: return 'PHP Recoverable Error';
        case E_DEPRECATED: return 'PHP Deprecated';
        case E_USER_DEPRECATED: return 'PHP User Deprecated';
        case E_ALL: return 'PHP All Errors, Warnings, and Notices';
        default:return $errno;
    }
}
function continue_after_error_number($errno)
{
    switch ($errno) {
        case E_DEPRECATED:
        case E_WARNING:
        case E_PARSE:
        case E_NOTICE:
        case E_USER_WARNING:
        case E_USER_NOTICE:
        case E_CORE_WARNING:
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return true;
        default:
            return false;
    }
}
function custom_error_handler($errno, $errstr, $errfile, $errline)
{
    $type = error_number_as_type($errno);
    if (log_error($type, $errstr, $errfile, $errline)) {
        if (continue_after_error_number($errno)) {
            return true;
        }
        Show::error("An error ($type) was reported. $errstr");
    } else {
        Show::error($errstr);
    }
}

function custom_exception_handler($exception)
{
    $class = get_class($exception);
    $code = $exception->getCode();
    $message = $exception->getMessage();

    if (ignore_error($message)) {
        Show::error($exception, $code);
        return true;
    }

    $file = '';
    try {
        $file = $exception->getFile();
    } catch (Exception) {
    }
    $line = $exception->getLine();

    if ($code !== null && $code !== 0) {
        $message = "[$code] " . $message;
    }

    $stack_trace = compile_stack(debug_backtrace());

    log_error($class, $message, $file, $line, $stack_trace);
    Show::error($exception, $code);
}

function compile_stack($trace)
{
    array_shift($trace);
    $stack = "";
    foreach ($trace as $frame) {
        $file = isset($frame['file']) ? $frame['file'] : '';
        $line = isset($frame['line']) ? $frame['line'] : '';
        $func = isset($frame['function']) ? $frame['function'] : '';
        $stack .= "File: $file, Line: $line, function: $function\n";
    }
    return $stack;
}

set_error_handler('custom_error_handler', E_ALL);

set_exception_handler('custom_exception_handler');
