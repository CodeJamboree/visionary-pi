<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";
require_once __DIR__ . "/common/get_query_pagination.php";
require_once __DIR__ . "/common/get_root_url.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $pagination = get_query_pagination();
    $db = new DatabaseHelper();
    $total = get_total($db);
    if ($total > 0) {
        $rows = select_rows($db, $pagination);
    }
    Show::data(['total' => $total, 'rows' => $rows]);
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
  FilePaths.path,
  CONCAT(?, '/', FilePaths.path, '/', MediaFiles.fileName) AS url,
  CONCAT(?, '/thumbnails/', MediaFiles.thumbnailFile) AS thumbnailUrl,
  MediaFiles.displayName,
  CONCAT(MediaFiles.width, 'x', MediaFiles.height) AS dimensions,
  MediaFiles.duration,
  MediaFiles.createdAt,
  FileExtensions.mediaType,
  MediaFiles.hasAudio
FROM
  MediaFiles
  LEFT OUTER JOIN FileExtensions
    ON MediaFiles.fileExtensionId = FileExtensions.id
  LEFT OUTER JOIN FilePaths
    ON MediaFiles.filePathId = FilePaths.id
ORDER BY
  MediaFiles.displayName ASC,
  MediaFiles.createdAt,
  MediaFiles.id
LIMIT ? OFFSET ?
";
    $rootUrl = get_root_url();
    $rows = $db->selectRows($sql, 'ssii',
        $rootUrl,
        $rootUrl,
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
