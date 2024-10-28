<?php
require_once __DIR__ . "/duration_to_ms.php";

function read_meta_av($path)
{
    $meta = [];
    $output = shell_exec("ffmpeg -i " . escapeshellarg($path) . " 2>&1");
    if (preg_match("/Duration: (\d+:\d+:\d+\.\d+)/", $output, $matches)) {
        $meta['duration'] = duration_to_ms($matches[1]);
    } else {
        return false;
    }
    if (preg_match("/Audio: ([^, ]+)/", $output, $matches)) {
        $meta['format'] = $matches[1];
        $meta['mediaType'] = 'audio';
        $meta['hasAudio'] = true;
    }
    if (preg_match("/Video: ([^, ]+),? [^:]+?, (\d+)x(\d+)[ ,]/", $output, $matches)) {
        $meta['format'] = $matches[1];
        $meta['width'] = $matches[2];
        $meta['height'] = $matches[3];
        $meta['mediaType'] = 'video';
    }
    if (!isset($meta['mediaType'])) {
        return false;
    }

    return $meta;
}
