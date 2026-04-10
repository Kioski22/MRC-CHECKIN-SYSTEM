<?php
session_start();
header('Content-Type: application/json');
require __DIR__ . '/dbConnection.php';
require __DIR__ . '/smartpayAuth.php';
require __DIR__ . '/eventConfig.php';

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
$eventId = getActiveEventId($db);
$startedAt = microtime(true);

if (isset($_POST['event_id']) && intval($_POST['event_id']) > 0) {
    $eventId = intval($_POST['event_id']);
}

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
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $meta = smartpayBuildTokenFailureMeta('getRegisterMember', $tokenError ?? 'External Smartpay API authentication failed. This hosted app is still online.', $durationMs);
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $meta['message'],
        'technical_error' => $tokenError ?? 'Unable to retrieve access token',
        'api_health' => $meta,
        'status_source' => 'upstream',
        'failure_type' => $meta['failure_type']
    ]);
    exit();
}

$response = fetchRegistrationList($eventId, $accessToken, $httpCode, $curlError);
if ($httpCode === 401) {
    $accessToken = smartpayEnsureSessionToken($tokenError, true);
    if ($accessToken === null) {
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $meta = smartpayBuildTokenFailureMeta('getRegisterMember', $tokenError ?? 'External Smartpay API authentication failed. This hosted app is still online.', $durationMs);
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $meta['message'],
            'technical_error' => $tokenError ?? 'Unauthorized',
            'api_health' => $meta,
            'status_source' => 'upstream',
            'failure_type' => $meta['failure_type']
        ]);
        exit();
    }

    $response = fetchRegistrationList($eventId, $accessToken, $httpCode, $curlError);
}

if ($response === null) {
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $failureType = apiHealthInferFailureType(isset($httpCode) ? intval($httpCode) : null, isset($curlError) ? $curlError : '', null);
    $meta = apiHealthBuildMeta('smartpay', 'getRegisterMember', $failureType, 'upstream', isset($httpCode) ? intval($httpCode) : null, $durationMs, apiHealthDefaultUserMessage($failureType));
    smartpayRecordHealth('getRegisterMember', $failureType, 'Registration order lookup failed.', isset($httpCode) ? intval($httpCode) : null, $durationMs, 'upstream', ['event_id' => $eventId]);
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => $meta['message'],
        'technical_error' => 'Failed to fetch registration list: ' . ($curlError ?: 'Unknown network error'),
        'api_health' => $meta,
        'status_source' => 'upstream',
        'failure_type' => $failureType
    ]);
    exit();
}

if ($httpCode !== 200) {
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $failureType = apiHealthInferFailureType($httpCode, '', $response);
    $meta = apiHealthBuildMeta('smartpay', 'getRegisterMember', $failureType, 'upstream', $httpCode, $durationMs, apiHealthDefaultUserMessage($failureType));
    smartpayRecordHealth('getRegisterMember', $failureType, 'Registration lookup failed with HTTP code ' . $httpCode, $httpCode, $durationMs, 'upstream', ['event_id' => $eventId]);
    http_response_code($httpCode > 0 ? $httpCode : 502);
    echo json_encode([
        'success' => false,
        'message' => $meta['message'],
        'technical_error' => 'Registration lookup failed with HTTP code ' . $httpCode,
        'api_health' => $meta,
        'status_source' => 'upstream',
        'failure_type' => $failureType
    ]);
    exit();
}

$data = json_decode($response, true);
if (!is_array($data) || !isset($data['resultKey']) || intval($data['resultKey']) !== 1 || !isset($data['resultValue']) || !is_array($data['resultValue'])) {
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $meta = apiHealthBuildMeta('smartpay', 'getRegisterMember', 'invalid_response', 'upstream', $httpCode, $durationMs, apiHealthDefaultUserMessage('invalid_response'));
    smartpayRecordHealth('getRegisterMember', 'invalid_response', 'Registration lookup returned invalid response structure.', $httpCode, $durationMs, 'upstream', ['event_id' => $eventId]);
    echo json_encode([
        'success' => false,
        'order' => null,
        'total' => 0,
        'display' => null,
        'message' => $meta['message'],
        'api_health' => $meta,
        'status_source' => 'upstream',
        'failure_type' => 'invalid_response'
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

$durationMs = (int) round((microtime(true) - $startedAt) * 1000);
$failureType = $order === null ? 'no_data' : 'ok';
$meta = apiHealthBuildMeta(
    'smartpay',
    'getRegisterMember',
    $failureType,
    'upstream',
    $httpCode,
    $durationMs,
    $order === null
        ? 'Smartpay API responded normally, but the email was not found in the registration list.'
        : 'Smartpay API returned registration order data successfully.'
);
smartpayRecordHealth(
    'getRegisterMember',
    $failureType,
    $order === null ? 'Registration order lookup returned no matching email.' : 'Registration order lookup succeeded.',
    $httpCode,
    $durationMs,
    'upstream',
    ['event_id' => $eventId]
);

echo json_encode([
    'success' => true,
    'order' => $order,
    'total' => $total,
    'display' => $display,
    'api_health' => $meta,
    'status_source' => 'upstream',
    'failure_type' => $failureType
]);
?>