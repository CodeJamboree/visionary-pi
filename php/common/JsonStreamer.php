<?php
/*
Builds up a single JSON object in small parts, allowing feedback
to the client that progress is being made before the page script
execution completes.

An object is returned allowing a common interface for an
optional error key to be added after the stream begins.

A standardized stream object appears as so:
{"stream": ..., "error": "reason"}

WARNING: Server may have its own optimizations causing conflicts.
Many attempts were made to disable various settings, control
output buffering, set custom headers, and manually compressing
content.

ie. "Accept-Encoding: gzip" resulted in our chunk headers being
removed that were placed to support "Transfer-Encoding: chunked"
 */

class JsonStreamer
{
    // Cache data unless we have N characters or more
    private static $chunk_size = 4096;
    // Cache data unless N seconds pass
    private static $interval_seconds = 5;

    private static $sent = false;
    private static $initialized = false;
    private static $cache = '';
    private static $stream_open = false;

    private static $states = [];
    private static $ROOT_OPEN = 'root-open';
    private static $ROOT_CLOSED = 'root-closed';
    private static $ARRAY_OPEN = 'array-open';
    private static $ARRAY_CONTINUE = 'array-continue';
    private static $OBJECT_OPEN = 'object-open';
    private static $OBJECT_CONTINUE = 'object-continue';
    private static $KEY_OPEN = 'key-open';
    private static $KEY_CLOSED = 'key-closed';

    public static function initialize()
    {
        if (self::$initialized === false) {
            self::$initialized = time();
        }

        while (ob_get_level()) {
            ob_end_flush();
        }

        ob_implicit_flush(true);
    }
    public static function setChunkSize($size)
    {
        self::$chunk_size = $size;
    }
    public static function setIntervalSeconds($seconds)
    {
        self::$interval_seconds = $seconds;
    }
    public static function close_all()
    {
        $last_count = count(self::$states);
        while (!empty(self::$states)) {
            switch (end(self::$states)) {
                case self::$ARRAY_OPEN:
                case self::$ARRAY_CONTINUE:
                    self::closeArray();
                    break;
                case self::$OBJECT_OPEN:
                case self::$OBJECT_CONTINUE:
                    self::closeObject();
                    break;
                case self::$KEY_OPEN:
                    self::addValue(null);
                    self::close();
                    break;
                case self::$KEY_CLOSED:
                    self::closeKey();
                    break;
                case self::$ROOT_OPEN:
                    self::addValue(null);
                    self::close();
                    return;
                case self::$ROOT_CLOSED:
                    return;
                default:
                    self::close();
                    break;
            }

            $count = count(self::$states);
            if ($last_count === $count) {
                return;
            }
            $last_count = $count;
        }
    }
    public static function fail($error)
    {
        if (!self::$stream_open) {
            self::open_stream();
            self::addValue(null);
        }
        self::close_all();
        self::done($error);
    }
    public static function done($error = null)
    {
        if (!self::$stream_open) {
            self::open_stream();
            self::addValue(null);
            $subError = "Nothing to send.";
            if (empty($error)) {
                $error = $subError;
            } else {
                $error = "$error\r\n$subError";
            }
        } else {
            $count = count(self::$states);
            if ($count > 1) {
                $state = self::$states[count(self::$states) - 1];
                $subError = "Stream prematurely closed. Stream-state: $state";
                if (empty($error)) {
                    $error = $subError;
                } else {
                    $error = "$error\r\n$subError";
                }
                self::close_all();
            }
        }
        self::send_footer($error);
    }

    public static function add_without_formatting($text)
    {
        self::append($text);
    }
    private static function append($text)
    {
        if (!self::$stream_open) {
            self::open_stream();
        }

        self::$cache .= $text;
        self::stream();
    }
    public static function setTimeout($seconds)
    {
        set_time_limit($seconds);
    }
    private static function prepare_add()
    {
        if (empty(self::$states)) {
            self::open(self::$ROOT_CLOSED);
            return;
        }

        switch (end(self::$states)) {
            case self::$ARRAY_OPEN:
                self::change(self::$ARRAY_CONTINUE);
                return;
            case self::$OBJECT_OPEN:
                self::change(self::$OBJECT_CONTINUE);
                return;
            case self::$ARRAY_CONTINUE:
            case self::$OBJECT_CONTINUE:
                self::append(',');
                return;
            case self::$KEY_OPEN:
                self::change(self::$KEY_CLOSED);
                return;
            case self::$ROOT_OPEN:
                self::change(self::$ROOT_CLOSED);
                return;
            case self::$KEY_CLOSED:
            case self::$ROOT_CLOSED:
                $state = end(self::$states);
                self::fail("Unable to add value to $state");
                return;
        }
    }
    public static function addValue($value)
    {
        self::expectstates('addValue', self::$ROOT_OPEN, self::$KEY_OPEN, self::$ARRAY_OPEN, self::$ARRAY_CONTINUE);
        self::prepare_add();
        self::append(json_encode($value));
    }
    public static function openArray()
    {
        self::expectstates("openArray", self::$ROOT_OPEN, self::$KEY_OPEN, self::$ARRAY_OPEN, self::$ARRAY_CONTINUE);
        self::prepare_add();
        self::open(self::$ARRAY_OPEN);
        self::append('[');
    }
    public static function closeArray()
    {
        self::expectstates('closeArray', self::$ARRAY_OPEN, self::$ARRAY_CONTINUE);
        self::close();
        self::append(']');
    }
    public static function openObject()
    {
        self::expectstates('openObject', self::$ROOT_OPEN, self::$KEY_OPEN, self::$ARRAY_OPEN, self::$ARRAY_CONTINUE);
        self::prepare_add();
        self::open(self::$OBJECT_OPEN);
        self::append('{');
    }
    public static function addObject($value)
    {
        self::addValue($value);
    }
    public static function closeObject()
    {
        self::expectstates('closeObject', self::$OBJECT_OPEN, self::$OBJECT_CONTINUE);
        self::append('}');
        self::close();
    }
    private static function close()
    {
        array_pop(self::$states);
    }
    private static function open($state)
    {
        array_push(self::$states, $state);
    }
    private static function change($state)
    {
        self::$states[count(self::$states) - 1] = $state;
    }
    public static function openKey($key)
    {
        self::expectstates('openKey', self::$OBJECT_OPEN, self::$OBJECT_CONTINUE);
        self::prepare_add();
        self::open(self::$KEY_OPEN);
        self::append(json_encode("$key") . ":");
    }
    public static function addKeyValuePair($key, $value)
    {
        self::openKey($key);
        self::addValue($value);
        self::closeKey();
    }
    public static function closeKey()
    {
        self::expectstates('closeKey', self::$KEY_CLOSED);
        self::close();
    }
    private static function expectstates($fn, ...$expected)
    {
        $count = count(self::$states);
        if ($count === 0) {
            $count++;
            self::open(self::$ROOT_OPEN);
        }
        $state = end(self::$states);
        if (in_array($state, (array) $expected)) {
            return;
        }
        self::fail("$fn expected state: $expected. Actual: $state");
    }
    private static function open_stream()
    {
        if (self::$stream_open) {
            return;
        }

        self::$stream_open = true;
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Transfer-Encoding: chunked');

        self::$cache .= "{\"stream\":";
        self::flush();
    }
    public static function flush()
    {
        self::stream(true);
    }
    private static function stream($flush = false)
    {
        if (self::$cache === '') {
            return;
        }

        if (!$flush) {
            if (self::$sent) {
                // too long since last chunk sent?
                $flush = (time() - self::$sent) > self::$interval_seconds;
            } else {
                // Too long since script started?
                $flush = (time() - self::$initialized) > self::$interval_seconds;
            }
        }
        if (!$flush) {
            // cache is full
            $flush = strlen(self::$cache) >= self::$chunk_size;
        }
        if (!$flush) {
            return;
        }

        self::send_chunk(self::$cache);
        self::$cache = '';
    }
    private static function send_chunk($chunk)
    {
        $size = dechex(strlen($chunk));
        if ($size === "0") {
            return;
        }
        echo "$size\r\n$chunk\r\n";
        self::$sent = time();
    }
    private static function send_footer($error)
    {
        self::flush();

        if (!empty($error)) {
            self::append(",\"error\":" . json_encode($error));
        }

        self::append("}");
        self::flush();

        self::send_eof();
    }
    private static function send_eof()
    {
        if (self::$sent === false) {
            send_chunk("{\"error\":\"No chunks sent prior to end.\"}");
        }
        echo "0\r\n\r\n";
        while (ob_get_level()) {
            ob_end_flush();
        }
        exit;
    }
}
JsonStreamer::initialize();
