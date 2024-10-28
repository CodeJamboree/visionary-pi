<?php
function get_query_color($defaultColor = '#000000')
{
    $color = $_GET['color'] ?? $defaultColor;

    if (preg_match('/^#?[\\da-f]{6}$/i', $color)) {
        return $color;
    }

    return $defaultColor;
}
