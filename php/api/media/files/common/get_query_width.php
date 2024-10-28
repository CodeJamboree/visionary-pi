<?php
function get_query_width($defaultWidth)
{
    $width = $_GET['width'] ?? $defaultWidth;

    if (preg_match('/^\\d{1,13}$/', $width)) {
        $width = intval($width);
        if ($width < 1) {
            return $defaultWidth;
        }
        return $width;
    }

    return $defaultWidth;
}
