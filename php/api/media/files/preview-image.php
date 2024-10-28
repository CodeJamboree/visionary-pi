<?php
require_once __DIR__ . "/common/random_file_name.php";
require_once __DIR__ . "/common/get_upload_path.php";
require_once __DIR__ . "/common/generate_image_thumbnail.php";
require_once __DIR__ . "/common/get_query_width.php";
require_once __DIR__ . "/common/get_query_height.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $file = $_GET['file'] ?? 'Screenshot_2024-10-25_at_10.18.09___PM.png';
    $filePath = get_upload_path($file);

    if (!is_file($filePath)) {
        echo "Unable to generate thumbnail";
        return;
    }

    $width = get_query_width(200);
    $height = get_query_height(100);

    $ext = pathinfo($file, PATHINFO_EXTENSION);
    $thumbnail = random_file_name($ext);

    $thumbnailPath = generate_image_thumbnail($filePath, $thumbnail, $width, $height);

    if ($thumbnailPath == null) {
        echo "Unable to generate thumbnail";
        return;
    }

    header('Content-Type: image/jpg');
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
