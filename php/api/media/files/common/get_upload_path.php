<?php
function get_upload_path($file = null)
{
    if ($file == null) {
        return dirname(__DIR__, 4) . '/uploads';
    }
    return dirname(__DIR__, 4) . "/uploads/$file";
}
