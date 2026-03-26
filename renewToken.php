<?php
session_start();
require __DIR__ . '/smartpayAuth.php';

header('Content-Type: application/json');

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Make API request to renew access token
$errorMessage = null;
$accessToken = smartpayEnsureSessionToken($errorMessage, true);

if ($accessToken === null) {
    echo json_encode([
        'success' => false,
        'message' => $errorMessage ?? 'Failed to retrieve access token'
    ]);
    exit();
}

echo json_encode([
    'success' => true,
    'access_token' => $accessToken
]);
?>
