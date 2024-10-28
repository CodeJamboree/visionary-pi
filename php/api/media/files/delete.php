<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/PostedJson.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";

function main()
{

    $posted = new PostedJson(3, 'POST');
    if (!$posted->keysExist(
        'ids'
    )) {
        Show::error($posted->lastError(), $posted->lastErrorCode());
        exit;
    }
    $ids = $posted->getValue('ids');

    $db = new DatabaseHelper();
    db_delete_media($db, $ids);

    Show::status(HTTP_STATUS_OK);
}
function db_delete_media($db, $ids)
{
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $paramTypes = str_repeat('i', count($ids));

    $sql = "DELETE FROM MediaFiles WHERE id IN($placeholders)";

    $total = $db->affectAny($sql, $paramTypes, ...$ids);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
}
try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
