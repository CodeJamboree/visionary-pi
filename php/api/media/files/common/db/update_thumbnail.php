<?php
function update_thumbnail($db, $id, $thumbnail)
{
    $sql = "UPDATE MediaFiles SET thumbnailFile = ? WHERE id = ?";
    $db->affectOne($sql, 'si', $thumbnail, $id);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
}
