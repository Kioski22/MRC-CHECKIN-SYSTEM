<?php
// Database connection settings (env vars override hardcoded defaults)
$host = getenv('DB_HOST') ?: 'localhost';
$db_name = getenv('DB_NAME') ?: 'mrc';
$db_user = getenv('DB_USER') ?: 'icto_randy4';
$db_password = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '091902Rands@';

mysqli_report(MYSQLI_REPORT_OFF);

// Try primary credentials first, then common local XAMPP fallback.
$connectionCandidates = [
    [$host, $db_user, $db_password, $db_name],
];

$normalizedHost = strtolower((string) $host);
if (($normalizedHost === 'localhost' || $normalizedHost === '127.0.0.1') && $db_user !== 'root') {
    $connectionCandidates[] = [$host, 'root', '', $db_name];
}

$db = null;
$lastError = 'Unknown database connection error.';

foreach ($connectionCandidates as $candidate) {
    $candidateConnection = @new mysqli($candidateHost, $candidateUser, $candidatePassword, $candidateDb);

    if (!$candidateConnection->connect_errno) {
        $db = $candidateConnection;
        break;
    }

    $lastError = $candidateConnection->connect_error;
}

if (!$db) {
    die('Connection failed: ' . $lastError);
}

$db->set_charset('utf8mb4');
?>