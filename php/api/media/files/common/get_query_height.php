<?php
function get_query_height($defaultHeight)
{
    $height = $_GET['height'] ?? $defaultHeight;

    if (preg_match('/^\\d{1,13}$/', $height)) {
        $height = intval($height);
        if ($height < 1) {
            return $defaultHeight;
        }
        return $height;
    }
    return $defaultHeight;
}
