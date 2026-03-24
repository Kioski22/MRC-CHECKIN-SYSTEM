<?php
session_start();
ob_start(); // Start output buffering to prevent any unexpected output

header('Content-Type: application/json');

include 'dbConnection.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Check if user is authenticated
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the check-in ID from POST request
$checkin_id = isset($_POST['checkin_id']) ? (int)$_POST['checkin_id'] : 0;

if ($checkin_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid check-in ID']);
    exit();
}

// Get the username based on user_id
$username_query = "SELECT username FROM users WHERE user_id = ?";
$username_stmt = $db->prepare($username_query);
$username_stmt->bind_param('i', $user_id);
$username_stmt->execute();
$username_result = $username_stmt->get_result();

if ($username_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit();
}

$username = $username_result->fetch_assoc()['username'];
$username_stmt->close();

// Get check-in details using email and check-in ID
$checkin_details_query = "SELECT firstname, middlename, lastname, email FROM checkins WHERE checkin_id = ?";
$checkin_details_stmt = $db->prepare($checkin_details_query);
$checkin_details_stmt->bind_param('i', $checkin_id);
$checkin_details_stmt->execute();
$checkin_details_result = $checkin_details_stmt->get_result();

if ($checkin_details_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Check-in record not found']);
    exit();
}

$checkin_details = $checkin_details_result->fetch_assoc();
$fullname = trim($checkin_details['firstname'] . ' ' . $checkin_details['middlename'] . ' ' . $checkin_details['lastname']);
$email = $checkin_details['email'];
$checkin_details_stmt->close();

// Delete the check-in record from the database
$delete_query = "DELETE FROM checkins WHERE checkin_id = ?";
$delete_stmt = $db->prepare($delete_query);
$delete_stmt->bind_param('i', $checkin_id);

if ($delete_stmt->execute()) {
    if ($delete_stmt->affected_rows > 0) {
        // Log the deletion action
        $timestamp = date('Y-m-d H:i:s');
        $description = "User '$username' deleted check-in record with ID $checkin_id (Name: $fullname, Email: $email)";

        $log_query = "INSERT INTO logs (description, timestamp) VALUES (?, ?)";
        $log_stmt = $db->prepare($log_query);
        $log_stmt->bind_param('ss', $description, $timestamp);
        $log_stmt->execute();
        $log_stmt->close();

        echo json_encode(['success' => true, 'message' => 'Check-in record deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Check-in record not found or already deleted.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete the check-in record: ' . $delete_stmt->error]);
}

$delete_stmt->close();
$db->close();

ob_end_flush(); // End output buffering
?>
