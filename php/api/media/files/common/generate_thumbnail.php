<?php
require_once __DIR__ . "/random_file_name.php";
require_once __DIR__ . "/generate_image_thumbnail.php";
require_once __DIR__ . "/generate_audio_thumbnail.php";
require_once __DIR__ . "/generate_video_thumbnail.php";

function generate_thumbnail($filePath, $mediaType, $duration, $width = 200, $height = 100)
{
    $ext = pathinfo($filePath, PATHINFO_EXTENSION);

    switch ($mediaType) {
        case 'image':
            $thumbnail = random_file_name($ext);
            return generate_image_thumbnail(
                $filePath,
                $thumbnail,
                $width,
                -1
            );
        case 'audio':
            $thumbnail = random_file_name('png');
            return generate_audio_thumbnail(
                $filePath,
                $thumbnail,
                $width,
                $height
            );
        case 'video':
            $thumbnail = random_file_name('jpg');
            $halfTime = ($duration / 1000) * 0.5;
            return generate_video_thumbnail(
                $filePath,
                $thumbnail,
                $width,
                $height,
                $halfTime
            );
            break;
        default:
            return false;
            break;
    }
}
