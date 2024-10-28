<?php
require_once __DIR__ . "/get_thumbnail_path.php";

function generate_audio_thumbnail($audioPath, $name, $width = 200, $height = 100, $color = 'red')
{
    if (!file_exists($audioPath)) {
        throw new Exception("File does not exist: $audioPath");
    }

    $thumbnailPath = get_thumbnail_path();
    if (!file_exists($thumbnailPath)) {
        throw new Exception("Thumbnail folder does not exist: $thumbnailPath");
    }
    $thumbnailPath .= "/$name";

    $compand = "compand=" .
        "attacks=0.3" .
        ":decays=0.5" .
        ":points=-80/-80|-50/-25|-30/-10|0/-5|20/0" .
        ":gain=20";

    $size = $width . 'x' . $height;
    $show = "showwavespic=s=$size:colors=$color,format=rgba";
    $filter = "-filter_complex \"$compand,$show\"";

    $in = escapeshellarg($audioPath);
    $out = escapeshellarg($thumbnailPath);

    $cmd = "ffmpeg -i $in $filter -frames:v 1 $out 2>&1";
    exec($cmd, $output, $returnVar);
    if ($returnVar === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    } else {
        return null;
    }
}
