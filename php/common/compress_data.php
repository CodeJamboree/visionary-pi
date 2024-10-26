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
