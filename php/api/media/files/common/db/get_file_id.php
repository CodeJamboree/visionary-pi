<?php
function get_file_id($db, $filePathId, $name)
{
    $sql = 'SELECT id FROM MediaFiles WHERE filePathId = ? AND fileName = ?';
    $id = $db->selectScalar($sql, 'is', $filePathId, $name);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    return $id;
}
