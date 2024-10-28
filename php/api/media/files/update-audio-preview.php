<?php
require_once __DIR__ . "/common/random_file_name.php";
require_once __DIR__ . "/common/get_upload_path.php";
require_once __DIR__ . "/common/generate_audio_thumbnail.php";
require_once __DIR__ . "/common/get_query_color.php";
require_once __DIR__ . "/common/get_query_width.php";
require_once __DIR__ . "/common/get_query_height.php";
require_once __DIR__ . "/common/db/update_thumbnail.php";
require_once __DIR__ . "/common/db/get_path_id.php";
require_once __DIR__ . "/common/db/get_path_id.php";
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $file = $_GET['file'] ?? 'stereo-tested.mp3';
    $filePath = get_upload_path($file);

    if (!is_file($filePath)) {
        Show::error("Unable to generate thumbnail");
        return;
    }

    $width = get_query_width(200);
    $height = get_query_height(100);
    $color = get_query_color('0000ff');

    $thumbnail = random_file_name("png");

    $thumbnailPath = generate_audio_thumbnail($filePath, $thumbnail, $width, $height, $color);

    if ($thumbnailPath == null) {
        Show::error("Unable to generate thumbnail");
        return;
    }

    $db = new DatabaseHelper();
    $filePathId = get_path_id($db, 'uploads');
    $id = get_file_id($db, $filePathId, $name);
    update_thumbnail($db, $id, $thumbnail);

    Show::data(['name' => $thumbnail]);
}
try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
