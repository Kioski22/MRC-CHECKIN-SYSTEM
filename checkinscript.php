<?php
session_start();
header('Content-Type: application/json');

include('dbConnection.php');

function respond_json($payload, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit();
}

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond_json(['success' => false, 'message' => 'Invalid request method'], 405);
}

// Get the logged-in user ID
if (!isset($_SESSION['user_id'])) {
    respond_json(['success' => false, 'message' => 'User not authenticated'], 401);
}

$user_id = $_SESSION['user_id'];

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    respond_json(['success' => false, 'message' => 'Invalid JSON payload'], 400);
}

$email = strtolower(trim((string) ($data['email'] ?? '')));
$firstname = trim((string) ($data['firstname'] ?? ''));
$lastname = trim((string) ($data['lastname'] ?? ''));
$middlename = trim((string) ($data['middlename'] ?? ''));
$prclicensenumber = trim((string) ($data['prclicensenumber'] ?? ''));
$chapter = trim((string) ($data['chapter'] ?? ''));
$qrcode = trim((string) ($data['qrcode'] ?? ''));

$middlename = $middlename === '' ? null : $middlename;
$prclicensenumber = $prclicensenumber === '' ? null : $prclicensenumber;
$chapter = $chapter === '' ? null : $chapter;
$qrcode = $qrcode === '' ? null : $qrcode;

if ($email === '') {
    respond_json(['success' => false, 'message' => 'Email is required'], 400);
}

// Check if the user has already checked in
$check_query = "SELECT checkin_time FROM checkins WHERE LOWER(TRIM(email)) = ? AND checkin_time IS NOT NULL";
$stmt = $db->prepare($check_query);
$existing_checkin_time = null;

if (!$stmt) {
    respond_json(['success' => false, 'message' => 'Database error: failed to prepare check-in lookup'], 500);
}

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
    $update_query = "UPDATE checkins SET checkin_time = ?, checkin_by = ? WHERE LOWER(TRIM(email)) = ?";
    $stmt = $db->prepare($update_query);

    if (!$stmt) {
        respond_json(['success' => false, 'message' => 'Database error: failed to prepare check-in update'], 500);
    }

    $stmt->bind_param("sis", $checkin_time, $user_id, $email);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $response = ['success' => true, 'message' => 'Check-in updated successfully!'];
        } else {
            // Fallback path: create missing checkins row, then consider check-in successful.
            // This handles race conditions where check-in is clicked before pre-insert finishes.
            if ($firstname === '' || $lastname === '') {
                $response = ['success' => false, 'message' => 'No matching record found to update.'];
            } else {
                $insert_query = "INSERT INTO checkins (firstname, lastname, middlename, email, prclicensenumber, chapter, qrcode, scanned_time, checkin_time, scanned_by, checkin_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $insert_stmt = $db->prepare($insert_query);

                if (!$insert_stmt) {
                    $response = ['success' => false, 'message' => 'Database error: failed to prepare fallback insert'];
                } else {
                    $scanned_time = $checkin_time;
                    $insert_stmt->bind_param(
                        "sssssssssii",
                        $firstname,
                        $lastname,
                        $middlename,
                        $email,
                        $prclicensenumber,
                        $chapter,
                        $qrcode,
                        $scanned_time,
                        $checkin_time,
                        $user_id,
                        $user_id
                    );

                    if ($insert_stmt->execute()) {
                        $response = ['success' => true, 'message' => 'Check-in updated successfully!'];
                    } else {
                        $response = ['success' => false, 'message' => 'Check-in update failed: ' . $insert_stmt->error];
                    }

                    $insert_stmt->close();
                }
            }
        }
    } else {
        $response = ['success' => false, 'message' => 'Check-in update failed: ' . $stmt->error];
    }

    $stmt->close();
}

$db->close();

respond_json($response);
?>
