<?php
function get_lookup_id($db, $table, $value, $column = 'name', $type = 's')
{
    $sql = "SELECT id FROM $table WHERE $column = ?";
    $id = $db->selectScalar($sql, $type, $value);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO $table ($column) values (?)";
        $result = $db->affectOne($sql, $type, $value);
        if ($db->has_error()) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    return $id;
}
