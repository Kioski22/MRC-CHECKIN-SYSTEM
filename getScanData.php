<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/auth.php';
require __DIR__ . '/dbConnection.php';
require __DIR__ . '/smartpayAuth.php';
require __DIR__ . '/eventConfig.php';

// Check authentication
requireAuthenticatedUser(true);

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['qrcode'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'QR code is required']);
    exit();
}

$qrcode = $_POST['qrcode'];
$activeEventId = getActiveEventId($db);
$startedAt = microtime(true);

function executeScanLookup($qrcode, $accessToken, $eventId, &$httpCode, &$curlError)
{
    $api_url = SMARTPAY_API_BASE_URL . '/getScanData?' . http_build_query([
        'scan' => $qrcode,
        'event_id' => $eventId
    ]);

    error_log("getScanData request URL: " . $api_url);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'PSME-ICTO/1.0'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError) {
        throw new Exception('Network error: ' . ($curlError ?: 'Unknown cURL failure'));
    }

    return $response;
}

try {
    $tokenError = null;
    $access_token = smartpayEnsureSessionToken($tokenError);
    if ($access_token === null) {
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $meta = smartpayBuildTokenFailureMeta('getScanData', $tokenError ?? 'Unable to retrieve access token', $durationMs);
        echo json_encode([
            'resultKey' => 0,
            'resultValue' => [],
            'errorCode' => 1,
            'errorMsg' => $meta['message'],
            'api_health' => $meta,
        ]);
        exit();
    }

    $response = executeScanLookup($qrcode, $access_token, $activeEventId, $http_code, $curl_error);

    if ($http_code === 401) {
        $access_token = smartpayEnsureSessionToken($tokenError, true);
        if ($access_token === null) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $meta = smartpayBuildTokenFailureMeta('getScanData', $tokenError ?? 'Unauthorized', $durationMs);
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $meta['message'], 'api_health' => $meta]);
            exit();
        }

        $response = executeScanLookup($qrcode, $access_token, $activeEventId, $http_code, $curl_error);
    }

    if ($http_code !== 200) {
        throw new Exception('API request failed with HTTP code: ' . $http_code);
    }
    
    // Decode the JSON response
    $api_response = json_decode($response, true);
    
    // Log the response for debugging
    error_log("getScanData response: " . $response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API');
    }

    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    
    // Check if the API response indicates success
    if (!isset($api_response['resultKey']) || $api_response['resultKey'] !== 1) {
        // Handle API-specific error responses
        $error_message = isset($api_response['errorMsg']) && !empty($api_response['errorMsg']) 
                        ? $api_response['errorMsg'] 
                        : 'QR code not found in external system';

        $meta = apiHealthBuildMeta('smartpay', 'getScanData', 'no_data', 'upstream', $http_code, $durationMs, 'Smartpay API responded normally, but no registration matched that QR code.');
        smartpayRecordHealth('getScanData', 'no_data', $error_message, $http_code, $durationMs, 'upstream', ['event_id' => $activeEventId]);
        
        echo json_encode([
            'resultKey' => 0,
            'resultValue' => [],
            'errorCode' => isset($api_response['errorCode']) ? $api_response['errorCode'] : 1,
            'errorMsg' => $error_message,
            'api_health' => $meta,
            'status_source' => 'upstream',
            'failure_type' => 'no_data'
        ]);
        exit();
    }

    $meta = apiHealthBuildMeta('smartpay', 'getScanData', 'ok', 'upstream', $http_code, $durationMs, 'Smartpay API returned scan data successfully.');
    smartpayRecordHealth('getScanData', 'ok', 'Smartpay scan lookup succeeded.', $http_code, $durationMs, 'upstream', ['event_id' => $activeEventId]);
    $api_response['api_health'] = $meta;
    $api_response['status_source'] = 'upstream';
    $api_response['failure_type'] = 'ok';
    
    // Success - return the API response as-is
    echo json_encode($api_response);
    
} catch (Exception $e) {
    error_log("getScanData error: " . $e->getMessage());
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    $failureType = apiHealthInferFailureType(isset($http_code) ? intval($http_code) : null, isset($curl_error) ? $curl_error : '', null);
    if ($failureType === 'unknown' && strpos(strtolower($e->getMessage()), 'invalid json') !== false) {
        $failureType = 'invalid_response';
    }

    $meta = apiHealthBuildMeta(
        'smartpay',
        'getScanData',
        $failureType,
        'upstream',
        isset($http_code) ? intval($http_code) : null,
        $durationMs,
        apiHealthDefaultUserMessage($failureType)
    );
    smartpayRecordHealth('getScanData', $failureType, $e->getMessage(), isset($http_code) ? intval($http_code) : null, $durationMs, 'upstream', ['event_id' => $activeEventId]);
    
    echo json_encode([
        'resultKey' => 0,
        'resultValue' => [],
        'errorCode' => 1,
        'errorMsg' => $meta['message'],
        'technical_error' => $e->getMessage(),
        'api_health' => $meta,
        'status_source' => 'upstream',
        'failure_type' => $failureType
    ]);
}
?>