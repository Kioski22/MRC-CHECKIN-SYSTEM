<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Get the logged-in user ID
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the JSON input from the request
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields (prclicensenumber is optional)
if (!isset($input['firstname']) || !isset($input['lastname']) || !isset($input['email']) || !isset($input['qrcode'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$firstname = $input['firstname'];
$lastname = $input['lastname'];
$middlename = $input['middlename'] ?? null;
$email = $input['email'];
$prclicensenumber = $input['prclicensenumber'] ?? null; // Allow NULL
$chapter = $input['chapter'] ?? null;
$qrcode = $input['qrcode'];

// Set the scanned_time to the current time in Asia/Manila timezone
$scanned_time = date('Y-m-d H:i:s');

// Prepare the SQL statement to insert the check-in data
$sql = "INSERT INTO checkins (firstname, lastname, middlename, email, prclicensenumber, chapter, qrcode, scanned_time, checkin_time, kit_release_time, scanned_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, ?)";

// Prepare statement
$stmt = $db->prepare($sql);

// Bind parameters, ensuring prclicensenumber can be null
$stmt->bind_param('ssssssssi', $firstname, $lastname, $middlename, $email, $prclicensenumber, $chapter, $qrcode, $scanned_time, $user_id);

try {
    $stmt->execute();
    echo json_encode(['success' => true]);
} catch (mysqli_sql_exception $e) {
    // Handle duplicate entry gracefully - just return success
    if ($e->getCode() === 1062) {
        echo json_encode(['success' => true, 'message' => 'Already exists, skipping']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

$stmt->close();
$db->close();
