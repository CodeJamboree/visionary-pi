<?php
function duration_to_ms($duration)
{
    $parts = explode(':', $duration);
    if (count($parts) === 3) {
        list($hours, $minutes, $seconds) = $parts;
        return ($hours * 3600 + $minutes * 60 + $seconds) * 1000;
    }
    if (count($parts) === 2) {
        list($minutes, $seconds) = $parts;
        return ($minutes * 60 + $seconds) * 1000;
    }
    return null;
}
