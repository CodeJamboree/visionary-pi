<?php
function random_file_name($extension)
{
    $id = bin2hex(random_bytes(16));
    return "$id.$extension";
}
