<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

requireAuthenticatedUser(true);

$username = $_SESSION['username'] ?? '';
require_once __DIR__ . '/functions.php';

if (!hasRole('admin')) {
    http_response_code(403);
    echo json_encode(['data' => [], 'message' => 'Forbidden']);
    exit();
}

$sql = "SELECT qrcode, first_name, middle_name, last_name, name_suffix, personal_email, mobile_number, membership_type, registration_fee, payment_method, chapter, `timestamp`
        FROM registrations
        ORDER BY `timestamp` DESC";

$result = $db->query($sql);
if (!$result) {
    http_response_code(500);
    echo json_encode(['data' => [], 'message' => 'Failed to fetch registrations.']);
    exit();
}

$membershipMap = [
    1 => 'Life / Associate',
    2 => 'Delegate',
    3 => 'Regular Member',
];

$data = [];
while ($row = $result->fetch_assoc()) {
    $middle = trim((string) ($row['middle_name'] ?? ''));
    $suffix = trim((string) ($row['name_suffix'] ?? ''));

    if (strcasecmp($middle, 'N/A') === 0) {
        $middle = '';
    }
    if (strcasecmp($suffix, 'N/A') === 0) {
        $suffix = '';
    }

    $fullName = trim(($row['first_name'] ?? '') . ' ' . $middle . ' ' . ($row['last_name'] ?? '') . ' ' . $suffix);
    $membershipType = (int) ($row['membership_type'] ?? 0);

    $data[] = [
        'timestamp' => (string) ($row['timestamp'] ?? ''),
        'qrcode' => (string) ($row['qrcode'] ?? ''),
        'name' => $fullName,
        'email' => (string) ($row['personal_email'] ?? ''),
        'mobile' => (string) ($row['mobile_number'] ?? ''),
        'membership' => $membershipMap[$membershipType] ?? 'Unknown',
        'registration_fee' => (string) ($row['registration_fee'] ?? '0.00'),
        'payment_method' => (string) ($row['payment_method'] ?? ''),
        'chapter' => (string) ($row['chapter'] ?? ''),
    ];
}

echo json_encode(['data' => $data]);
