<?php
function save_file_in_db($db, $pathId, $name, $displayName, $extId, $size, $created)
{
    $sql = 'INSERT INTO MediaFiles (
    filePathId,
    fileName,
    displayName,
    fileExtensionId,
    fileSize,
    createdAt
) VALUES (?, ?, ?, ?, ?, ?)';
    $db->affectOne($sql, 'issiii',
        $pathId,
        $name,
        $displayName,
        $extId,
        $size,
        $created
    );
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    return $db->insert_id();
}
