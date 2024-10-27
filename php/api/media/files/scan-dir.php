<?php
require_once dirname(__DIR__, 3) . "/common/Show.php";
require_once dirname(__DIR__, 3) . "/common/DatabaseHelper.php";
error_reporting(E_ALL);
ini_set('display_errors', 'On');

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
function get_lookup_id($db, $table, $ext)
{
    $sql = "SELECT id FROM $table WHERE name = ?";
    $id = $db->selectScalar($sql, 's', $ext);
    if ($id == false && $db->errorMessage) {
        throw $db->get_last_exception();
    }
    if ($id == null) {
        $sql = "INSERT INTO $table (name) values (?)";
        $result = $db->affectOne($sql, 's', $ext);
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

    $filePathId = get_lookup_id($db, 'FilePaths', $file['path']);
    $fileExtensionId = get_lookup_id($db, 'FileExtensions', $file['extension']);

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
            createdAt
        ) VALUES (?, ?, ?, ?, ?, ?)';
        $db->affectOne($sql, 'issiii',
            $filePathId,
            $file['name'],
            display_name($file['name'], 64),
            $fileExtensionId,
            $file['size'],
            $file['created']
        );
    } else {
        $sql = 'UPDATE MediaFiles SET
            missing = 0,
            filePathId = ?,
            fileName = ?,
            fileExtensionId = ?,
            fileSize = ?,
            createdAt = ?
        WHERE id = ?';
        $db->affectAny($sql, 'isiiii',
            $filePathId,
            $file['name'],
            $fileExtensionId,
            $file['size'],
            $file['created'],
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
            $files[] = [
                'path' => $file->getPath(),
                'name' => $file->getFilename(),
                'extension' => $file->getExtension(),
                'size' => $file->getSize(),
                'created' => $file->getCTime(),
            ];
        }
    }
    return $files;
}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
