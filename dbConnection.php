<?php
// Database connection settings (env vars override hardcoded defaults)
$host        = getenv('DB_HOST') ?: 'localhost';
$db_name     = getenv('DB_NAME') ?: 'mrc';
$db_user     = getenv('DB_USER') ?: 'icto_randy4';

// FIX: getenv() returns empty string '' (not false) when var is unset on some servers.
// Use ?: so that an empty string also falls back to the hardcoded password.
$db_password = getenv('DB_PASSWORD') ?: '091902Rands@';

mysqli_report(MYSQLI_REPORT_OFF);

$db = @new mysqli($host, $db_user, $db_password, $db_name);

if ($db->connect_errno) {
    die('Connection failed: ' . $db->connect_error);
}

$db->set_charset('utf8mb4');
?>