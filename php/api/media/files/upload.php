<?php
require_once dirname(__DIR__) . "../../../common/Show.php";
error_reporting(E_ALL);
ini_set('display_errors', 'On');

function main()
{
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method === 'OPTIONS') {
        return;
    }
    if ($method !== 'POST') {
        Show::status(HTTP_STATUS_METHOD_NOT_ALLOWED);
        return;
    }

    $uploadPath = dirname(__DIR__, 3) . '/uploads/';
    echo "Upload Path: $uploadPath ";
    if (!is_dir($uploadPath)) {
        mkdir($uploadPath, 0777, true);
    }
    if (!isset($_FILES['file'])) {

        echo '<pre>';
        var_dump($_POST);
        var_dump($_FILES);
        echo '</pre>';
        exit;

        Show::error("File not specified", 200);
        return;
    }

    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        Show::error("File error not ok: " . $_FILES['file']['error'], 200);
        return;
    }

    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];

    $fileName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fileName);

    $filePath = $uploadPath . $fileName;

    if (move_uploaded_file($fileTmpPath, $filePath)) {
        Show::data(['name' => $fileName]);
    } else {
        Show::error('Unable to move uploaded file.', 200);
    }
}

try {
    main();
} catch (Exception $e) {
    Show::error($e);
}
