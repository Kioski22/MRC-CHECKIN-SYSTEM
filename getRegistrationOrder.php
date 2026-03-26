<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/smartpayAuth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit();
}

$email = trim((string) ($_POST['email'] ?? ''));
$eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 16;

if ($email === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Email is required'
    ]);
    exit();
}

if ($eventId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid event ID'
    ]);
    exit();
}

function fetchRegistrationList($eventId, $accessToken, &$httpCode, &$curlError)
{
    $apiUrl = SMARTPAY_API_BASE_URL . '/getRegisterMember?event_id=' . urlencode((string) $eventId);
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'PSME-ICTO/1.0'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError) {
        return null;
    }

    return $response;
}

$tokenError = null;
$accessToken = smartpayEnsureSessionToken($tokenError);
if ($accessToken === null) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $tokenError ?? 'Unable to retrieve access token'
    ]);
    exit();
}

$response = fetchRegistrationList($eventId, $accessToken, $httpCode, $curlError);
if ($httpCode === 401) {
    $accessToken = smartpayEnsureSessionToken($tokenError, true);
    if ($accessToken === null) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $tokenError ?? 'Unauthorized'
        ]);
        exit();
    }

    $response = fetchRegistrationList($eventId, $accessToken, $httpCode, $curlError);
}

if ($response === null) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch registration list: ' . ($curlError ?: 'Unknown network error')
    ]);
    exit();
}

if ($httpCode !== 200) {
    http_response_code($httpCode > 0 ? $httpCode : 502);
    echo json_encode([
        'success' => false,
        'message' => 'Registration lookup failed with HTTP code ' . $httpCode
    ]);
    exit();
}

$data = json_decode($response, true);
if (!is_array($data) || !isset($data['resultKey']) || intval($data['resultKey']) !== 1 || !isset($data['resultValue']) || !is_array($data['resultValue'])) {
    echo json_encode([
        'success' => true,
        'order' => null,
        'total' => 0,
        'display' => null,
        'message' => 'Registration data unavailable'
    ]);
    exit();
}

$registrants = $data['resultValue'];
$total = count($registrants);
$lowerEmail = strtolower($email);
$order = null;

foreach ($registrants as $index => $registrant) {
    $registrantEmail = strtolower(trim((string) ($registrant['email'] ?? '')));
    if ($registrantEmail === $lowerEmail) {
        $order = $index + 1;
        break;
    }
}

$display = null;
if ($order !== null) {
    $display = $order . ' Registrant';
}

echo json_encode([
    'success' => true,
    'order' => $order,
    'total' => $total,
    'display' => $display
]);
?>