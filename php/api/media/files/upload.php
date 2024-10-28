<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";

require_once __DIR__ . "/common/truncate.php";
require_once __DIR__ . "/common/generate_thumbnail.php";
require_once __DIR__ . "/common/read_meta_image.php";
require_once __DIR__ . "/common/read_meta_av.php";
require_once __DIR__ . "/common/get_upload_path.php";
require_once __DIR__ . "/common/random_file_name.php";

require_once __DIR__ . "/common/db/save_file_in_db.php";
require_once __DIR__ . "/common/db/get_extension_id.php";
require_once __DIR__ . "/common/db/get_path_id.php";
require_once __DIR__ . "/common/db/get_lookup_id.php";
require_once __DIR__ . "/common/db/save_image_meta.php";
require_once __DIR__ . "/common/db/save_av_meta.php";
require_once __DIR__ . "/common/db/update_thumbnail.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'OPTIONS') {
        return;
    }
    if ($method !== 'POST') {
        Show::status(HTTP_STATUS_METHOD_NOT_ALLOWED);
        return;
    }
    $db = new DatabaseHelper();

    $uploadPath = dirname(__DIR__, 3) . '/uploads/';
    if (!isset($_FILES['file'])) {
        Show::error("File not specified", 200);
        return;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        Show::error("File error not ok: " . $_FILES['file']['error'], 200);
        return;
    }

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $displayName = $_FILES['file']['name'];

    $ext = pathinfo($displayName, PATHINFO_EXTENSION);
    $fileName = random_file_name($ext);
    $displayName = truncate($displayName, 64);
    $size = filesize($fileTmpPath);

    $filePath = get_upload_path($fileName);

    if (!move_uploaded_file($fileTmpPath, $filePath)) {
        Show::error('Unable to move uploaded file.', 200);
        exit;
    }

    $extId = get_extension_id($db, $ext);
    $pathId = get_path_id($db, 'uploads');

    $id = save_file_in_db($db, $pathId, $fileName, $displayName, $extId, $size, time());

    Show::data(['id' => $id]);

    $mediaType = 'unknown';

    // parse metadata
    $image = read_meta_image($filePath);
    if ($image !== false) {
        $fileFormatId = get_lookup_id($db, 'FileFormats', $image['format'] ?? null, 'format');
        save_image_meta($db, $id, $image['width'], $image['height'], $fileFormatId);
        $mediaType = 'image';
    }
    $duration = 0;
    $av = read_meta_av($filePath);
    if ($av !== false) {
        $mediaType = $av['mediaType'];
        $fileFormatId = get_lookup_id($db, 'FileFormats', $image['format'] ?? null, 'format');
        $duration = $av['duration'] ?? 0;
        save_av_meta($db, $id,
            $av['width'] ?? null,
            $av['height'] ?? null,
            $fileFormatId,
            $duration,
            $av['hasAudio'] ?? null
        );
    }

    $thumbnailPath = generate_thumbnail($filePath, $mediaType, 200, 100, $duration);
    if ($thumbnailPath !== null) {
        $thumbnail = basename($thumbnailPath);
        update_thumbnail($db, $id, $thumbnail);
    }

    // Update media type if previously unknown
    get_extension_id($db, $ext, $mediaType);

}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
