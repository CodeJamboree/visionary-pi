<?php
function save_image_meta($db, $id, $width, $height, $fileFormatId)
{
    $sql = 'UPDATE MediaFiles SET
            width = ?,
            height = ?,
            fileFormatId = ?
        WHERE id = ?';

    $db->affectOne($sql, 'iiii', $width, $height, $fileFormatId, $id);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
}
