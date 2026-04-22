<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/registration_confirmation_mail.php';

header('Content-Type: application/json');

requireAuthenticatedUser(true);

$username = $_SESSION['username'] ?? '';
if (!hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$qrcode = trim((string) ($input['qrcode'] ?? ''));
if ($qrcode === '') {
    echo json_encode(['success' => false, 'message' => 'Missing required field: qrcode']);
    exit();
}

$sql = "SELECT first_name, middle_name, last_name, name_suffix, personal_email, mobile_number, qrcode, qrcode_url, `timestamp`
        FROM registrations
        WHERE qrcode = ?
        ORDER BY `timestamp` DESC
        LIMIT 1";
$stmt = $db->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Failed to prepare query']);
    exit();
}

$stmt->bind_param('s', $qrcode);
$stmt->execute();
$result = $stmt->get_result();
$registration = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$registration) {
    echo json_encode(['success' => false, 'message' => 'Registration not found']);
    exit();
}

$mailError = '';
if (!send_registration_confirmation_email($registration, $mailError)) {
    echo json_encode(['success' => false, 'message' => 'Failed to resend confirmation email: ' . $mailError]);
    exit();
}

echo json_encode(['success' => true, 'message' => 'Confirmation email resent successfully.']);
