<?php
function get_root_url()
{
    $secure = $_SERVER['HTTPS'] ?? 'off';
    $protocol = $secure !== 'off' ? 'https' : 'http';
    $port = $_SERVER['SERVER_PORT'];
    $host = $_SERVER['HTTP_HOST'];

    if (
        // non-standard port
        ($port !== 80 && $port !== 443) ||
        // port mismatch
        ($protocol === 'http' && $port === 443) ||
        ($protocol === 'https' && $port === 80)
    ) {
        // non-standard port, or wrong port for protocol
        return "$protocol://$host:$port";
    }
    return "$protocol://$host";
}
