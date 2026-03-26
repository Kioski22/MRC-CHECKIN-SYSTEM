<?php
session_start();

header('Content-Type: application/json');

$apiUsername = 'psme';
$apiPassword = 'psm3@2024';

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Make API request to renew access token
$ch = curl_init('https://psme-api.smartpay.ph/public/index.php/api/adminlogin');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $apiUsername, 'password' => $apiPassword]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
$response = curl_exec($ch);

if ($response === false) {
    $errorMessage = curl_error($ch);
    curl_close($ch);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to connect to API: ' . $errorMessage
    ]);
    exit();
}

curl_close($ch);

$data = json_decode($response, true);

if (isset($data['access_token'])) {
    $_SESSION['access_token'] = $data['access_token'];
    echo json_encode([
        'success' => true,
        'access_token' => $data['access_token']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to retrieve access token',
        'error' => isset($data['error']) ? $data['error'] : 'Unknown error'
    ]);
}
?>
