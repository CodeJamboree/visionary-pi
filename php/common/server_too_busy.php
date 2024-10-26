<?php
$system_load = false;

function server_too_busy($threshold = 0.8)
{
    global $system_load;
    if (!$system_load) {
        $system_load = sys_getloadavg();
    }
    if ($system_load && $system_load[0] > $threshold) {
        return true;
    }
    return false;
}
