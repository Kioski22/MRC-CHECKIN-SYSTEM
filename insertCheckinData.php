<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

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

// Get the JSON input from the request
$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    respond_json(['success' => false, 'message' => 'Invalid JSON payload'], 400);
}

// Validate required fields (prclicensenumber is optional)
if (empty(trim((string) ($input['firstname'] ?? ''))) || empty(trim((string) ($input['lastname'] ?? ''))) || empty(trim((string) ($input['email'] ?? ''))) || empty(trim((string) ($input['qrcode'] ?? '')))) {
    respond_json(['success' => false, 'message' => 'Missing required fields'], 400);
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

if (!$stmt) {
    respond_json(['success' => false, 'message' => 'Database error: failed to prepare insert'], 500);
}

// Bind parameters, ensuring prclicensenumber can be null
$stmt->bind_param('ssssssssi', $firstname, $lastname, $middlename, $email, $prclicensenumber, $chapter, $qrcode, $scanned_time, $user_id);

try {
    $stmt->execute();
    respond_json(['success' => true]);
} catch (mysqli_sql_exception $e) {
    // Handle duplicate entry gracefully - just return success
    if ($e->getCode() === 1062) {
        respond_json(['success' => true, 'message' => 'Already exists, skipping']);
    } else {
        respond_json(['success' => false, 'message' => 'Database error: ' . $e->getMessage()], 500);
    }
}

$stmt->close();
$db->close();
