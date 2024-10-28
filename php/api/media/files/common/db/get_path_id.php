<?php
function get_path_id($db, $path)
{
    $sql = "SELECT id FROM FilePaths WHERE path = ?";
    $id = $db->selectScalar($sql, 's', $path);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO FilePaths (path) values (?)";
        $result = $db->affectOne($sql, 's', $path);
        if ($db->has_error()) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    return $id;
}
