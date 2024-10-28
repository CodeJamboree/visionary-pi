<?php
function get_extension_id($db, $ext, $mediaType = 'unknown')
{
    if ($ext === null || $ext === '') {
        return null;
    }

    $sql = "SELECT id, mediaType FROM FileExtensions WHERE ext = ?";
    $row = $db->selectRow($sql, 's', $ext);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    if ($row == null) {
        $sql = "INSERT INTO FileExtensions (ext, mediaType) values (?, ?)";
        $result = $db->affectOne($sql, 'ss', $ext, $mediaType);
        if ($db->has_error()) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    $id = $row['id'];
    if ($mediaType === 'unknown' || $row['mediaType'] !== 'unknown') {
        return $id;
    }

    $sql = "UPDATE FileExtensions SET mediaType = ? WHERE id = ?";
    $result = $db->affectOne($sql, 'si', $mediaType, $id);
    if ($db->has_error()) {
        throw $db->get_last_exception();

    }
    return $id;
}
