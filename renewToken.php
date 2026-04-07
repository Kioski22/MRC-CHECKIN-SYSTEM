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
    $meta = apiHealthBuildMeta('smartpay', 'adminlogin', 'auth_failure', 'upstream', 401, null, $errorMessage ?? 'Failed to retrieve access token');
    echo json_encode([
        'success' => false,
        'message' => $errorMessage ?? 'Failed to retrieve access token',
        'api_health' => $meta
    ]);
    exit();
}

$meta = apiHealthBuildMeta('smartpay', 'adminlogin', 'ok', 'upstream', 200, null, 'Smartpay admin token refreshed successfully.');

echo json_encode([
    'success' => true,
    'access_token' => $accessToken,
    'api_health' => $meta
]);
?>
