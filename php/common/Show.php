<?php
require_once 'HTTP_STATUS.php';

class Show
{
    private static $NO_CACHE =
        'Cache-Control: no-cache, no-store, must-revalidate';

    public static function message(
        $message,
        $code = HTTP_STATUS_OK
    ) {
        self::data(['message' => $message], $code);
    }
    public static function error(
        $error,
        $code = HTTP_STATUS_INTERNAL_SERVER_ERROR
    ) {
        if ($code === 0) {
            $code = HTTP_STATUS_INTERNAL_SERVER_ERROR;
        }
        if (class_exists('JsonStreamer')) {
            JsonStreamer::fail($error);
            return;
        }

        if (
            is_object($error) && (
                $error instanceof Exception ||
                $error instanceof Error
            )
        ) {
            self::data([
                'error' => $error->getMessage(),
                'stack' => $error->getTrace(),
            ], $code);
        } else {

            $backtrace = debug_backtrace();
            $backtrace_frames = array_map(
                function ($frame) {
                    if (is_array($frame)) {
                        return sprintf(
                            "%s:%d  %s()",
                            isset($frame['file']) ? $frame['file'] : '',
                            isset($frame['line']) ? $frame['line'] : '',
                            isset($frame['function']) ? $frame['function'] : ''
                        );
                    }
                    return 'frame not not an array';
                }, $backtrace);

            self::data([
                'error' => $error,
                'not exception' => $error,
                'code' => $code,
                'class' => is_object($error) ? get_class($error) : null,
                'stack' => $backtrace_frames,
            ], $code);
        }
        exit;
    }
    public static function status($code)
    {
        $message = http_status_message($code);
        $key = $code < 300 ? 'message' : 'error';
        $json = json_encode([$key => $message], JSON_PRETTY_PRINT);
        header("HTTP/1.1 $code $message");
        header(self::$NO_CACHE);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($json));
        echo $json;
    }
    public static function data(
        $data,
        $code = HTTP_STATUS_OK,
        $secondsCached = -1
    ) {
        $json = json_encode($data, JSON_PRETTY_PRINT);

        http_response_code($code);
        header('Content-Type: application/json');
        // if ($secondsCached > 0) {
        //     header("Cache-Control: max-age=$secondsCached");
        // } else if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        header(self::$NO_CACHE);
        // }
        header('Content-Length: ' . strlen($json));
        echo $json;
    }
}
