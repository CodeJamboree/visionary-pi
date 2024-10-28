<?php
function save_av_meta($db, $id, $width, $height, $fileFormatId, $duration, $hasAudio)
{
    $sql = 'UPDATE MediaFiles SET
            width = ?,
            height = ?,
            fileFormatId = ?,
            duration = ?,
            hasAudio = ?
        WHERE id = ?';

    $db->affectOne($sql, 'iiiiii', $width, $height, $fileFormatId, $duration, $hasAudio, $id);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
}
