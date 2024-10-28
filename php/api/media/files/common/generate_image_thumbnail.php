<?php
require_once __DIR__ . "/get_thumbnail_path.php";

function generate_image_thumbnail($imagePath, $name, $width = 200, $height = 100)
{
    if (!file_exists($imagePath)) {
        throw new Exception("File does not exist: $imagePath");
    }

    $thumbnailPath = get_thumbnail_path();
    if (!file_exists($thumbnailPath)) {
        throw new Exception("Thumbnail folder does not exist: $thumbnailPath");
    }
    $thumbnailPath .= "/$name";

    list($oWidth, $oHeight, $type) = getimagesize($imagePath);

    if ($height === -1) {
        $ratio = $oWidth / $oHeight;
        $height = (int) ($width * $ratio);
    }
    $resized = imagecreatetruecolor($width, $height);
    switch ($type) {
        case IMAGETYPE_JPEG:
            $image = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $image = imagecreatefrompng($imagePath);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            break;
        case IMAGETYPE_GIF:
            $image = imagecreatefromgif($imagePath);
            break;
        default:
            return false;
    }
    imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $oWidth, $oHeight);
    switch ($type) {
        case IMAGETYPE_JPEG:
            imagejpeg($resized, $thumbnailPath);
            break;
        case IMAGETYPE_PNG:
            imagepng($resized, $thumbnailPath);
            break;
        case IMAGETYPE_GIF:
            imagegif($resized, $thumbnailPath);
            break;
    }
    imagedestroy($image);
    imagedestroy($resized);

    return $thumbnailPath;
}
