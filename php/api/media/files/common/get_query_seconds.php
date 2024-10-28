<?php
function get_query_seconds($defaultseconds)
{
    $seconds = $_GET['seconds'] ?? $defaultseconds;

    if (preg_match('/^\\d{1,13}(\.\\d{1,3})?$/', $seconds)) {
        $seconds = floatval($seconds);
        if ($seconds < 0) {
            return $defaultseconds;
        }
        return $seconds;
    }

    return $defaultseconds;
}
