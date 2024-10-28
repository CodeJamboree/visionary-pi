<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";
require_once __DIR__ . "/common/duration_to_ms.php";

error_reporting(E_ALL);
ini_set('display_errors', 'On');

$uploadPath = dirname(__DIR__, 3) . '/uploads';
$thumbnailPath = dirname(__DIR__, 3) . '/thumbnails';

$imageTypeArray = array
    (
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

function main()
{
    global $uploadPath;
    $files = find_files_and_sizes($uploadPath);

    $db = new DatabaseHelper();

    mark_all_files_missing($db);
    foreach ($files as $file) {
        update_file_in_db($db, $file);
    }
    Show::data(['files' => $files]);
}

function mark_all_files_missing($db)
{
    $sql = "UPDATE MediaFiles SET missing = 1";
    $result = $db->affectAny($sql);
    if ($result === false) {
        throw $db->get_last_exception();
    }
}
function get_lookup_id($db, $table, $value, $column = 'name', $type = 's')
{
    if ($value === null || $value === '') {
        return null;
    }

    $sql = "SELECT id FROM $table WHERE $column = ?";
    $id = $db->selectScalar($sql, $type, $value);
    if ($id == false && $db->errorMessage) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO $table ($column) values (?)";
        $result = $db->affectOne($sql, $type, $value);
        if ($result == false) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    return $id;
}
function get_path_id($db, $path)
{
    $sql = "SELECT id FROM FilePaths WHERE path = ?";
    $id = $db->selectScalar($sql, 's', $path);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO FilePaths (path) values (?)";
        $result = $db->affectOne($sql, 's', $path);
        if ($db->has_error()) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    return $id;
}
function get_extension_id($db, $ext, $mediaType)
{
    if ($ext === null || $ext === '') {
        return null;
    }

    $sql = "SELECT id FROM FileExtensions WHERE ext = ?";
    $id = $db->selectScalar($sql, 's', $ext);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO FileExtensions (ext, mediaType) values (?, ?)";
        $result = $db->affectOne($sql, 'ss', $ext, $mediaType);
        if ($db->has_error()) {
            throw $db->get_last_exception();
        }
        return $db->insert_id();
    }
    return $id;
}
function get_file_id($db, $filePathId, $name)
{
    $sql = 'SELECT id FROM MediaFiles WHERE filePathId = ? AND fileName = ?';
    $id = $db->selectScalar($sql, 'is', $filePathId, $name);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
    return $id;
}
function update_file_in_db($db, $file)
{
    // DB can't retain long file names
    if (strlen($file['name']) > 128) {
        return;
    }

    $fileFormatId = get_lookup_id($db, 'FileFormats', $file['format'] ?? null, 'format');
    $filePathId = get_path_id($db, $file['path']);
    $fileExtensionId = get_extension_id($db, $file['ext'], $file['mediaType']);

    $id = get_file_id($db, $filePathId, $file['name']);

    if ($id == null) {
        $sql = 'INSERT INTO MediaFiles (
            filePathId,
            fileName,
            displayName,
            fileExtensionId,
            fileSize,
            createdAt,
            width,
            height,
            fileFormatId,
            duration,
            hasAudio,
            thumbnailFile
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $db->affectOne($sql, 'issiiiiiiiis',
            $filePathId,
            $file['name'],
            display_name($file['name'], 64),
            $fileExtensionId,
            $file['size'],
            $file['created'],
            $file['width'] ?? null,
            $file['height'] ?? null,
            $fileFormatId,
            $file['duration'] ?? null,
            $file['hasAudio'] ?? false,
            $file['thumbnailFile'] ?? null
        );

    } else {
        $sql = 'UPDATE MediaFiles SET
            missing = 0,
            filePathId = ?,
            fileName = ?,
            fileExtensionId = ?,
            fileSize = ?,
            createdAt = ?,
            width = ?,
            height = ?,
            fileFormatId = ?,
            duration = ?,
            hasAudio = ?,
            thumbnailFile = ?
        WHERE id = ?';
        $db->affectAny($sql, 'isiiiiiiiisi',
            $filePathId,
            $file['name'],
            $fileExtensionId,
            $file['size'],
            $file['created'],
            $file['width'] ?? null,
            $file['height'] ?? null,
            $fileFormatId,
            $file['duration'] ?? null,
            $file['hasAudio'] ?? false,
            $file['thumbnailFile'] ?? null,
            $id
        );
    }
}
function display_name($name, $length)
{
    $name = pathinfo($name, PATHINFO_FILENAME);
    $name = str_replace('_', ' ', $name);
    $name = preg_replace('/\s+/', ' ', $name);
    if (strlen($name) > $length) {
        $name = substr($name, 0, $length);
    }
    return $name;
}
function find_files_and_sizes($path)
{
    global $imageTypeArray;
    global $thumbnailPath;

    if (!is_dir($path)) {
        throw new InvalidArgumentException("$path is not a valid directory");
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    $root = dirname(__DIR__, 3);
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $pathname = $file->getPathname();

            $relative_path = remove_root_path($root, $file->getPath());

            $info = [
                'path' => $relative_path,
                'name' => $file->getFilename(),
                'ext' => $file->getExtension(),
                'format' => $file->getExtension(),
                'size' => $file->getSize(),
                'created' => $file->getCTime(),
            ];
            list($width, $height, $type, $attr) = getimagesize($pathname);
            if ($width !== null) {
                $info['width'] = $width;
                $info['mediaType'] = 'image';
            }

            if ($height !== null) {
                $info['height'] = $height;
            }

            if ($type !== null) {
                $info['format'] = $imageTypeArray[$type];
            }
            $av = get_audio_video_info($pathname);
            $info = $info + $av;

            if (!isset($info['mediaType'])) {
                $info['mediaType'] = 'other';
            }
            if ($info['mediaType'] === 'video') {
                $totalMs = $info['duration'] ?? 0;
                $secondsHalfWay = ($totalMs / 1000) * .5;
                $thumbId = bin2hex(random_bytes(16));
                $thumbFullPath = "$thumbnailPath/$thumbId.jpg";
                $img = generate_video_thumbnail($pathname, $thumbFullPath, $secondsHalfWay);
                if ($img !== null) {
                    $info['thumbnailFile'] = "$thumbId.jpg";
                }
            }
            if ($info['mediaType'] === 'audio') {
                $thumbId = bin2hex(random_bytes(16));
                $thumbFullPath = "$thumbnailPath/$thumbId.png";
                $img = generate_audio_thumbnail($pathname, $thumbFullPath);
                if ($img !== null) {
                    $info['thumbnailFile'] = "$thumbId.png";
                }
            }
            $files[] = $info;

        }
    }
    return $files;
}

function remove_root_path($rootPath, $path)
{
    $path = realpath($path);
    $rootPath = realpath($rootPath);
    if (strpos($path, $rootPath) === 0) {
        return substr($path, strlen($rootPath) + 1);
    }
    return $path;
}

function get_audio_video_info($path)
{
    $info = [];
    $output = shell_exec("ffmpeg -i " . escapeshellarg($path) . " 2>&1");
    if (preg_match("/Duration: (\d+:\d+:\d+\.\d+)/", $output, $matches)) {
        $info['duration'] = duration_to_ms($matches[1]);
    }
    if (preg_match("/Audio: ([^, ]+)/", $output, $matches)) {
        $info['format'] = $matches[1];
        $info['mediaType'] = 'audio';
        $info['hasAudio'] = true;
    }
    if (preg_match("/Video: ([^, ]+),? [^:]+?, (\d+)x(\d+)[ ,]/", $output, $matches)) {
        $info['format'] = $matches[1];
        $info['width'] = $matches[2];
        $info['height'] = $matches[3];
        $info['mediaType'] = 'video';
    }
    return $info;
}
function generate_audio_thumbnail($audioPath, $thumbnailPath)
{
    if (!file_exists($audioPath)) {
        throw new Exception("Video file does not exist: $audioPath");
    }

    $thumbnailDir = dirname($thumbnailPath);
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0777, true);
    }

    $cmd = "ffmpeg -i " . escapeshellarg($audioPath) .
    // showspectrumpic
    // showwavespic
    // color=c=white:s=200x100,
    // -lavfi "showwavespic=s=200x100:colors=blue"
    // " -f lavfi -i color=c=white:s=200x200" .
    " -filter_complex \"compand=gain=-6,showwavespic=s=200x100\" -frames:v 1 " . escapeshellarg($thumbnailPath) . " 2>&1";
    exec($cmd, $output, $returnVar);
    if ($returnVar === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    } else {
        return null;
    }
}
function generate_video_thumbnail($videoPath, $thumbnailPath, $timeInSeconds = 1)
{
    if (!file_exists($videoPath)) {
        throw new Exception("Video file does not exist: $videoPath");
    }

    $thumbnailDir = dirname($thumbnailPath);
    if (!file_exists($thumbnailDir)) {
        mkdir($thumbnailDir, 0777, true);
    }

    $cmd = "ffmpeg -i " . escapeshellarg($videoPath) .
    " -ss " . escapeshellarg($timeInSeconds) .
    " -vframes 1 -vf \"scale=200:-1\" -q:v 2 " . escapeshellarg($thumbnailPath) . " 2>&1";
    exec($cmd, $output, $returnVar);
    if ($returnVar === 0 && file_exists($thumbnailPath)) {
        return $thumbnailPath;
    } else {
        return null;
    }
}
try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
