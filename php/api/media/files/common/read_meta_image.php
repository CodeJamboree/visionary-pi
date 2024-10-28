<?php
function read_meta_image($path)
{
    $imageType = array(
        0 => 'UNKNOWN',
        1 => 'GIF',
        2 => 'JPEG',
        3 => 'PNG',
        4 => 'SWF',
        5 => 'PSD',
        6 => 'BMP',
        7 => 'TIFF_II',
        8 => 'TIFF_MM',
        9 => 'JPC',
        10 => 'JP2',
        11 => 'JPX',
        12 => 'JB2',
        13 => 'SWC',
        14 => 'IFF',
        15 => 'WBMP',
        16 => 'XBM',
        17 => 'ICO',
        18 => 'COUNT',
    );

    list($width, $height, $type) = getimagesize($path);

    if ($width == null || $height == null) {
        return false;
    }

    $meta = [];
    $meta['width'] = $width;
    $meta['height'] = $height;
    $meta['mediaType'] = 'image';
    if ($type !== null) {
        $meta['format'] = $imageType[$type];
    }
    return $meta;
}
