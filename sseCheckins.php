<?php
session_start();
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');

// Database connection
include 'dbConnection.php';
date_default_timezone_set('Asia/Manila');

// Keep the connection open and send data at regular intervals
while (true) {
    // Query the latest check-ins data
    $sql = "SELECT checkin_id, firstname, lastname, middlename, email, prclicensenumber, chapter, qrcode, scanned_time, checkin_time, kit_release_time, scanned_by, checkin_by, releasedkit_by FROM checkins ORDER BY checkin_id DESC LIMIT 10";
    $result = $db->query($sql);

    $checkinsData = [];
    while ($row = $result->fetch_assoc()) {
        $checkinsData[] = $row;
    }

    // Send the data as JSON
    echo "data: " . json_encode($checkinsData) . "\n\n";
    ob_flush();
    flush();

    // Wait for 5 seconds before sending the next update
    sleep(5);
}
?>
