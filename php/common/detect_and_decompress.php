<?php
function compress_data($data, $encoding)
{
    if (empty($encoding) || $encoding == false) {
        return $data;
    }

    switch ($encoding) {
        case 'identity':
            return $data;
        case 'gzip':
            if (extension_loaded('zlib')) {
                return gzencode($data);
            }
            break;
        case 'deflate':
            if (extension_loaded('zlib')) {
                return gzdeflate($data);
            }
        case 'compress':
            if (extension_loaded('zlib')) {
                return gzdeflate($data, -1, ZLIB_ENCODING_RAW);
            }
        case 'br':
            if (extension_loaded('brotli')) {
                return brotli_compress($data);
            }
        case 'zstd':
            if (extension_loaded('zstd')) {
                return zstd_compress($data);
            }
        default:
            break;
    }
    throw new Exception("Unsupported encoding: $encoding");
}
function decompress_data($data, $encoding)
{
    if (empty($encoding) || $encoding === false) {
        return $data;
    }

    switch ($encoding) {
        case 'identity':
            return $data;
        case 'gzip':
            if (extension_loaded('zlib')) {
                return gzdecode($data);
            }
            break;
        case 'deflate':
        case 'compress':
            if (extension_loaded('zlib')) {
                return gzuncompress($data);
            }
            break;
        case 'br':
            if (extension_loaded('brotli')) {
                return brotli_uncompress($data);
            }
            break;
        case 'zstd':
            if (extension_loaded('zstd')) {
                return zstd_uncompress($data);
            }
            break;
        default:
            break;
    }

    throw new Exception("Unsupported encoding: $encoding");
}
function detect_and_decompress($data)
{
    if (!isset($_SERVER['HTTP_CONTENT_ENCODING'])) {
        return $data;
    }

    $encoding = $_SERVER['HTTP_CONTENT_ENCODING'];
    decompress_data($data, $encoding);
}
function server_encodings()
{
    // NOTE: Most preferable encodings first
    $encodings = [];
    if (extension_loaded('brotli')) {
        $encodings[] = 'br';
    }
    if (extension_loaded('zlib')) {
        $encodings[] = 'gzip';
        $encodings[] = 'deflate';
    }
    if (extension_loaded('zstd')) {
        $encodings[] = 'zstd';
    }
    if (extension_loaded('zlip')) {
        $encodings[] = 'compress';
    }
    $encodings[] = 'identity';
    return $encodings;
}
function build_accept_encoding_header()
{
    $value = implode(', ', server_encodings());
    return "Accept-Encoding: $value";
}
function pick_best_encoding($accepted_encodings)
{
    // TODO: Cache results
    $avgLoad = sys_getloadavg()[0]; // 1 minute avg load
    $is_busy = $avgLoad > 0.8;
    if ($is_busy) {
        return 'identity';
    }
    $server_encodings = server_encodings();
    foreach ($accepted_encodings as $encoding) {
        $type = strtolower(trim(explode(';', $encoding)[0]));
        if (in_array($type, $server_encodings) && $type !== 'identity') {
            if (load_meets_threshold_for_encoding($type, $avgLoad)) {
                return $type;
            }

        }
    }
    return 'identity';
}
function load_meets_threshold_for_encoding($encoding, $load)
{
    // TODO: Cache results
    // TODO: Adjust thresholds after testing hardware under load
    // TODO: Make thrresholds configurable
    if ($load === false || $load <= 0.10) {
        return true;
    }

    switch ($encoding) {
        case 'identity':
            return true;
        case 'compress':
            return $load < 0.90;
        case 'br':
            return $load < 0.75;
        case 'deflate':
        case 'zstd':
        case 'gzip':
            return $load < 0.25;
        default:
            return false;
    }
}
function detect_preferred_encodings()
{
    // TODO: improve efficiency.
    // TODO: Cache headers & response
    $HEADER = 'HTTP_ACCEPT_ENCODING';
    $raw = 'identity';
    $accept = isset($_SERVER[$HEADER]) ? $_SERVER[$HEADER] : $raw;
    if ($accept === $raw) {
        return [$raw];
    }
    $encodings = array_map('trim', exploder(',', $accept));
    $weights = [];
    $default_quality = 1.0;
    $PLACEHOLDER = PHP_INT_MIN;

    foreach ($encodings as $encoding) {
        $parts = explode(';', $encoding);
        $name = strtolower(trim($parts[0]));
        $quality = $PLACEHOLDER;
        if (isset($parts[1])) {
            $pair = exploder('=', $parts[1]);
            if (count($pair) === 2 && $pair[0] === 'q') {
                $quality = (float) $pair[1];
            }
        }
        // explicitly not accepted
        if ($quality === 0) {
            continue;
        }

        if ($name === '*') {
            if ($quality !== $PLACEHOLDER) {
                $default_quality = $quality;
            }
            continue;
        }
        $weights[$name] = $quality;
    }
    foreach ($weights as $key => &$value) {
        if ($value === $PLACEHOLDER) {
            $value = $default_quality;
        }
    }

    if (!array_key_exists($raw, $weights)) {
        $weights[$raw] = 0;
    }

    arsort($weights);
    return array_keys($weights);
}
function detect_best_encoding()
{
    // TODO: Cache encodings & response
    $encodings = detect_preferred_encodings();
    return pick_best_encoding($encodings);
}
