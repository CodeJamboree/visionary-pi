<?php
error_reporting(E_ALL);
ini_set('display_error', 1);

echo "<p>Hello World!</p>";

$conn = new mysqli(
    "mariadb",
    "{{db username}}",
    "{{db password}",
    "{{db name}}"
);

if ($conn->connect_error) {
    die("Connect Error: " . $conn->connect_error);
}

$version = $conn->server_info;

echo "<p>DB Version: $version</p>";

$conn->close();
