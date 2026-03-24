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

// Check if the user has already checked in
$check_query = "SELECT checkin_time FROM checkins WHERE email = ? AND checkin_time IS NOT NULL";
$stmt = $db->prepare($check_query);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->bind_result($existing_checkin_time);
$stmt->fetch();
$stmt->close();

if ($existing_checkin_time) {
    // If the user has already checked in, return a message with the check-in time
    $existing_checkin_time_formatted = date('F j, Y h:i A', strtotime($existing_checkin_time));
    $response = [
        'success' => false,
        'message' => 'Already checked in on ' . $existing_checkin_time_formatted
    ];
} else {
    // Update the check-in time and check-in by user
    $checkin_time = date('Y-m-d H:i:s');  // Current time in Asia/Manila timezone
    $update_query = "UPDATE checkins SET checkin_time = ?, checkin_by = ? WHERE email = ?";
    $stmt = $db->prepare($update_query);
    $stmt->bind_param("sis", $checkin_time, $user_id, $email);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['success' => true, 'message' => 'Check-in updated successfully!'];
        } else {
            $response = ['success' => false, 'message' => 'No matching record found to update.'];
        }
    } else {
        $response = ['success' => false, 'message' => 'Check-in update failed: ' . $stmt->error];
    }

    $stmt->close();
}

$db->close();

echo json_encode($response);
?>
