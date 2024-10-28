<?php
require_once __DIR__ . "/common/random_file_name.php";
require_once __DIR__ . "/common/get_upload_path.php";
require_once __DIR__ . "/common/generate_video_thumbnail.php";
require_once __DIR__ . "/common/get_query_width.php";
require_once __DIR__ . "/common/get_query_height.php";
require_once __DIR__ . "/common/get_query_seconds.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $file = $_GET['file'] ?? 'warrencountyva-2022-10-18-Board_of_Supervisors_Meeting-3036-3363.mp4';
    $filePath = get_upload_path($file);

    if (!is_file($filePath)) {
        echo "Unable to generate thumbnail";
        return;
    }

    $width = get_query_width(200);
    $height = get_query_height(-1);
    $seconds = get_query_seconds(0);

    $thumbnail = random_file_name("jpg");

    $thumbnailPath = generate_video_thumbnail(
        $filePath,
        $thumbnail,
        $width,
        $height,
        $seconds
    );

    if ($thumbnailPath == null) {
        echo "Unable to generate thumbnail";
        return;
    }

    header('Content-Type: image/jpeg');
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
