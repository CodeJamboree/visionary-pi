<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";
error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $pagination = get_pagination_params();

    $db = new DatabaseHelper();
    $total = get_total($db);
    if ($total > 0) {
        $rows = select_rows($db, $pagination);
    }
    Show::data(['total' => $total, 'rows' => $rows]);
}
function get_pagination_params($defaultLimit = 20, $defaultOffset = 0)
{
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : $defaultLimit;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : $defaultOffset;
    if ($limit < 1) {
        $limit = 1;
    } elseif ($limit > 500) {
        $limit = 500;
    }
    if ($offset < 0) {
        $offset = 0;
    }
    return ['limit' => $limit, 'offset' => $offset];
}
function get_total($db)
{
    $sql = 'SELECT COUNT(0) FROM MediaFiles';
    $total = $db->selectScalar($sql);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    return $total;
}
function select_rows($db, $pagination)
{
    $sql = "SELECT
  MediaFiles.id,
  CONCAT('http://kiosk.local/uploads/', MediaFiles.fileName) AS url,
  MediaFiles.displayName,
  MediaFiles.width,
  MediaFiles.height,
  MediaFiles.duration,
  MediaFiles.createdAt,
  FileFormats.name AS fileFormat,
  AudioFormats.name AS audioFormat,
  VideoFormats.name AS videoFormat
FROM
  MediaFiles
  LEFT OUTER JOIN FileFormats
    ON MediaFiles.fileFormatId = FileFormats.id
  LEFT OUTER JOIN AudioFormats
    ON MediaFiles.audioFormatId = AudioFormats.id
  LEFT OUTER JOIN VideoFormats
    ON MediaFiles.videoFormatId = VideoFormats.id
ORDER BY
  MediaFiles.displayName ASC,
  MediaFiles.createdAt,
  MediaFiles.id
LIMIT ? OFFSET ?
";
    $rows = $db->selectRows($sql, 'ii',
        $pagination['limit'],
        $pagination['offset']
    );
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    return $rows;
}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
