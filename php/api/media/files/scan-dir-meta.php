<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";
error_reporting(E_ALL);
ini_set('display_errors', 'On');

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
    $uploadPath = dirname(__DIR__, 3) . '/uploads/';
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
function update_file_in_db($db, $file)
{
    // DB can't retain long file names
    if (strlen($file['name']) > 128) {
        return;
    }

    $fileFormatId = get_lookup_id($db, 'FileFormats', $file['format'] ?? null);
    $filePathId = get_lookup_id($db, 'FilePaths', $file['path']);
    $fileExtensionId = get_lookup_id($db, 'FileExtensions', $file['extension']);
    $audioFormatId = get_lookup_id($db, 'AudioFormats', $file['audio_format'] ?? null);
    $audioFrequencyId = get_lookup_id($db, 'AudioFrequencies', $file['audio_hz'] ?? null, 'hz', 'i');
    $audioChannelId = get_lookup_id($db, 'AudioChannels', $file['audio_channels'] ?? null);
    $videoFormatId = get_lookup_id($db, 'VideoFormats', $file['video_format'] ?? null);

    $sql = 'SELECT id FROM MediaFiles WHERE filePathId = ? AND fileName = ?';
    $id = $db->selectScalar($sql, 'is', $filePathId, $file['name']);
    if ($db->has_error()) {
        throw $db->get_last_exception();
    }
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
            audioFormatId,
            audioFrequencyId,
            audioChannelId,
            videoFormatId
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $db->affectOne($sql, 'issiiiiiiiiiii',
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
            $audioFormatId,
            $audioFrequencyId,
            $audioChannelId,
            $videoFormatId
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
            audioFormatId = ?,
            audioFrequencyId = ?,
            audioChannelId = ?,
            videoFormatId = ?
        WHERE id = ?';
        $db->affectAny($sql, 'isiiiiiiiiiiii',
            $filePathId,
            $file['name'],
            $fileExtensionId,
            $file['size'],
            $file['created'],
            $file['width'] ?? null,
            $file['height'] ?? null,
            $fileFormatId,
            $file['duration'] ?? null,
            $audioFormatId,
            $audioFrequencyId,
            $audioChannelId,
            $videoFormatId,
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
    if (!is_dir($path)) {
        throw new InvalidArgumentException("$path is not a valid directory");
    }
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $pathname = $file->getPathname();
            $info = [
                'path' => $file->getPath(),
                'name' => $file->getFilename(),
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
                'created' => $file->getCTime(),
            ];
            list($width, $height, $type, $attr) = getimagesize($pathname);
            if ($width !== null) {
                $info['width'] = $width;
            }

            if ($height !== null) {
                $info['height'] = $height;
            }

            if ($type !== null) {
                $info['format'] = $imageTypeArray[$type];
            }
            $files[] = $info + get_audio_video_info($pathname);
        }
    }
    return $files;
}

function get_audio_video_info($path)
{
    $info = [];
    $output = shell_exec("ffmpeg -i " . escapeshellarg($path) . " 2>&1");
    if (preg_match("/Duration: (\d+:\d+:\d+\.\d+)/", $output, $matches)) {
        $info['duration'] = duration_to_ms($matches[1]);
    }
    if (preg_match("/Audio: ([^, ]+),? [^:]*?(\d+) Hz, (mono|stereo)/", $output, $matches)) {
        $info['audio_format'] = $matches[1];
        $info['audio_hz'] = $matches[2];
        $info['audio_channels'] = $matches[3];
    }

    if (preg_match("/Video: ([^, ]+),? [^:]+, (\d{2,4})x(\d{2,4}), /", $output, $matches)) {
        $info['video_format'] = $matches[1];
        $info['width'] = $matches[2];
        $info['height'] = $matches[3];
    }
    return $info;
}
function duration_to_ms($duration)
{
    $parts = explode(':', $duration);
    if (count($parts) === 3) {
        list($hours, $minutes, $seconds) = $parts;
        return ($hours * 3600 + $minutes * 60 + $seconds) * 1000;
    }
    if (count($parts) === 2) {
        list($minutes, $seconds) = $parts;
        return ($minutes * 60 + $seconds) * 1000;
    }
    return null;
}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
