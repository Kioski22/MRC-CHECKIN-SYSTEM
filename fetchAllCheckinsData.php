<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Prepare the base query to retrieve all data without pagination
$sql = "SELECT 
            c.checkin_id,
            c.firstname,
            c.lastname,
            c.middlename,
            c.email,
            c.prclicensenumber,
            c.chapter,
            c.qrcode,
            c.scanned_time,
            c.checkin_time,
            c.kit_release_time,
            u1.username AS scanned_by_username,
            u2.username AS checkin_by_username,
            u3.username AS released_by_username
        FROM checkins c
        LEFT JOIN users u1 ON c.scanned_by = u1.user_id
        LEFT JOIN users u2 ON c.checkin_by = u2.user_id
        LEFT JOIN users u3 ON c.releasedkit_by = u3.user_id";

// Execute the query to fetch all records
$stmt = $db->prepare($sql);
$stmt->execute();
$data_result = $stmt->get_result();

// Prepare the data to send back
$data = [];
while ($row = $data_result->fetch_assoc()) {
    $row['scanned_time'] = $row['scanned_time'] ? date('F j, Y h:i A', strtotime($row['scanned_time'])) : 'N/A';
    $row['checkin_time'] = $row['checkin_time'] ? date('F j, Y h:i A', strtotime($row['checkin_time'])) : 'N/A';
    $row['kit_release_time'] = $row['kit_release_time'] ? date('F j, Y h:i A', strtotime($row['kit_release_time'])) : 'N/A';
    $row['middlename'] = $row['middlename'] ?? 'N/A';
    $row['chapter'] = $row['chapter'] ?? 'N/A';
    $row['scanned_by_username'] = $row['scanned_by_username'] ?? 'N/A';
    $row['checkin_by_username'] = $row['checkin_by_username'] ?? 'N/A';
    $row['released_by_username'] = $row['released_by_username'] ?? 'N/A';
    $data[] = $row;
}

// Return JSON response
$response = [
    "data" => $data
];

echo json_encode($response);

$stmt->close();
$db->close();
?>
