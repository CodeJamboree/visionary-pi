<?php
require_once 'server_too_busy.php';

$supported_content_encodings = [
    'br', // Brotli: more efficiant than gzip & deflate focused on streaming
    'gzip', // widely used for balance of compression ratio & speed
    'deflate', // interchangably used with gzip
    'zstd', // Zstandard: higher ratio than gzip, deflate, brotli, but CPU intensive
    'compress', // older algorithm, less efficient than gzip/deflate
];

function negotiate_content_encoding()
{
    // TODO: Cache responses for a short duration (CPU load changes)

    if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
        return false;
    }

    $value = $_SERVER['HTTP_ACCEPT_ENCODING'];
    if (empty($value)) {
        return false;
    }

    if ($value === 'identity') {
        return false;
    }

    return get_preferred_encoding($value);
}
function get_preferred_encoding($value)
{
    global $supported_content_encodings;
    if (empty($value)) {
        return false;
    }

    if (count($supported_content_encodings) === 0) {
        return false;
    }

    $encodings = explode(', ', $value);

    usort($encodings, "sort_encoding_by_weight");

    foreach ($encodings as $encoding) {
        $encoding = remove_weight_from_encoding($encoding);
        if (is_supported_encoding($encoding)) {
            return $encoding;
        }
        if ($encoding === '*') {
            return get_preferred_encoding(
                build_preferred_encodings($supported_content_encodings)
            );
        }
    }

    return false;
}
function build_preferred_encodings($encodings)
{
    global $supported_content_encodings;
    $count = count($encodings);
    if ($count === 0) {
        return '';
    }

    $digits = strlen((string) $count);
    $weighted = [];
    foreach ($supported_content_encodings as $index => $value) {
        if ($value[0] !== '*') {
            $q = number_format(1 - ($index / $count), $digits);
            $weighted[] = "$value;q=$q";
        }
    }
    return implode(', ', $weighted);
}

function is_supported_encoding($encoding)
{
    global $supported_content_encodings;
    if (!in_array($encoding, $supported_content_encodings)) {
        return false;
    }
    switch ($encoding) {
        case 'gzip':
        case 'deflate':
        case 'compress':
            return extension_loaded('zlib') && !server_too_busy(0.6);
        case 'br':
            return extension_loaded('brotli') && !server_too_busy(0.4);
        case 'zstd':
            return extension_loaded('zstd') && !server_too_busy(0.3);
        case 'identity':
            return true;
        default:
            return false;
    }
}
function sort_encoding_by_weight($a, $b)
{
    $pattern = '/;q=(\d+(\.\d+)?)/';
    $weightA = preg_match($pattern, $a, $match) ? floatval($match[1]) : 1.0;
    $weightB = preg_match($pattern, $b, $match) ? floatval($match[1]) : 1.0;
    return $weightB - $weightA;
}
function remove_weight_from_encoding($encoding)
{
    return trim(preg_replace('/;q=\d+(\.\d+)?/', '', $encoding));
}
