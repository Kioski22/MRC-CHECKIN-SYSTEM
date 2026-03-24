<?php
session_start();
header('Content-Type: application/json');

include('dbConnection.php');

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Get the logged-in user ID
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'];

// Check if a row with the provided email exists
$sql = "SELECT kit_release_time FROM checkins WHERE email = ?";
$stmt = $db->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result(); // Store the result to check if the row exists

if ($stmt->num_rows === 0) {
    // If no row is found with the provided email, return a message and abort the operation
    echo json_encode(['success' => false, 'message' => 'Kit release failed: No record found with the provided email']);
    $stmt->close();
    $db->close();
    exit();
}

$stmt->bind_result($kit_release_time);
$stmt->fetch();
$stmt->close();

// Check if the kit is already released
if ($kit_release_time !== null) {
    // If kit_release_time is not NULL, the kit has already been released
    $response = [
        'success' => false,
        'message' => 'Kit release failed: Kit already released',
        'releaseTime' => $kit_release_time
    ];
} else {
    // Update the record to mark the kit as released and store releasedkit_by
    $release_time = date('Y-m-d H:i:s');  // Current time in Asia/Manila timezone
    $sql = "UPDATE checkins SET kit_release_time = ?, releasedkit_by = ? WHERE email = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("sis", $release_time, $user_id, $email);

    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Kit released successfully!',
            'releaseTime' => $release_time
        ];
    } else {
        $response = ['success' => false, 'message' => 'Kit release failed: ' . $stmt->error];
    }

    $stmt->close();
}

$db->close();

echo json_encode($response);
?>
