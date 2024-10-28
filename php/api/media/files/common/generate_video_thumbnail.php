<?php
require_once __DIR__ . "/get_thumbnail_path.php";

function generate_video_thumbnail($videoPath, $name, $width = 200, $height = 100, $seconds = 0)
{
    if (!file_exists($videoPath)) {
        throw new Exception("File does not exist: $videoPath");
    }

    $thumbnailPath = get_thumbnail_path();
    if (!file_exists($thumbnailPath)) {
        throw new Exception("Thumbnail folder does not exist: $thumbnailPath");
    }
    $thumbnailPath .= "/$name";

    $in = escapeshellarg($videoPath);
    $out = escapeshellarg($thumbnailPath);
    $ss = escapeshellarg($seconds);
    $vf = "\"scale=$width:$height\"";

    $cmd = "ffmpeg -i $in -ss $ss -vframes 1 -vf $vf -q:v 2 $out 2>&1";
    exec($cmd, $output, $returnVar);

    if ($returnVar === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    } else {
        return null;
    }
}
