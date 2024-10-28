<?php
require_once __DIR__ . "/common/random_file_name.php";
require_once __DIR__ . "/common/get_upload_path.php";
require_once __DIR__ . "/common/generate_audio_thumbnail.php";
require_once __DIR__ . "/common/get_query_color.php";
require_once __DIR__ . "/common/get_query_width.php";
require_once __DIR__ . "/common/get_query_height.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $file = $_GET['file'] ?? 'stereo-tested.mp3';
    $filePath = get_upload_path($file);

    if (!is_file($filePath)) {
        echo "Unable to generate thumbnail";
        return;
    }

    $width = get_query_width(200);
    $height = get_query_height(100);
    $color = get_query_color('0000ff');

    $thumbnail = random_file_name("png");

    $thumbnailPath = generate_audio_thumbnail($filePath, $thumbnail, $width, $height, $color);

    if ($thumbnailPath == null) {
        echo "Unable to generate thumbnail";
        return;
    }

    header('Content-Type: image/png');
    header("Content-Disposition: inline; filename=\"$thumbnail\"");
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($thumbnailPath));

    readfile($thumbnailPath);
    unlink($thumbnailPath);
}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
