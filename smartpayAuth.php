<?php

require_once __DIR__ . '/dbConnection.php';
require_once __DIR__ . '/apiHealth.php';

const SMARTPAY_API_BASE_URL = 'https://psme-api.smartpay.ph';
const SMARTPAY_ADMIN_USERNAME = 'psme';
const SMARTPAY_ADMIN_PASSWORD = '9hbpRSKahp@T';

function smartpaySetLastTokenAttempt($failureType = null, $httpCode = null, $message = null, $durationMs = null)
{
    $GLOBALS['smartpay_last_token_attempt'] = [
        'failure_type' => $failureType,
        'http_code' => $httpCode,
        'message' => $message,
        'duration_ms' => $durationMs,
    ];
}

function smartpayGetLastTokenAttempt()
{
    return isset($GLOBALS['smartpay_last_token_attempt']) && is_array($GLOBALS['smartpay_last_token_attempt'])
        ? $GLOBALS['smartpay_last_token_attempt']
        : null;
}

function smartpayBuildTokenFailureMeta($endpointName, $fallbackMessage, $durationMs = null)
{
    $lastAttempt = smartpayGetLastTokenAttempt();
    $failureType = $lastAttempt['failure_type'] ?? 'auth_failure';
    $httpCode = $lastAttempt['http_code'] ?? null;
    $message = $lastAttempt['message'] ?? $fallbackMessage;
    $resolvedDuration = $durationMs !== null ? $durationMs : ($lastAttempt['duration_ms'] ?? null);

    return apiHealthBuildMeta('smartpay', $endpointName, $failureType, 'upstream', $httpCode, $resolvedDuration, $message);
}

function smartpayDescribeUnexpectedResponse($httpCode, $contentType, $responseBody)
{
    $normalizedContentType = trim((string) $contentType);
    $summary = 'Smartpay admin login returned an unexpected response.';

    if (is_int($httpCode) && $httpCode > 0 && $httpCode !== 200) {
        $summary = 'Smartpay admin login endpoint returned HTTP ' . $httpCode . '.';
    }

    if ($normalizedContentType !== '') {
        $summary .= ' Content-Type: ' . $normalizedContentType . '.';
    }

    $trimmedBody = trim((string) $responseBody);
    if ($trimmedBody !== '') {
        $plainBody = preg_replace('/\s+/', ' ', strip_tags($trimmedBody));
        $plainBody = trim((string) $plainBody);
        if ($plainBody !== '') {
            $summary .= ' Response preview: ' . substr($plainBody, 0, 120) . '.';
        }
    }

    return $summary;
}

function smartpayRecordHealth($endpointName, $failureType, $message, $httpCode = null, $durationMs = null, $statusSource = 'upstream', $context = null)
{
    global $db;
    if (!isset($db)) {
        return false;
    }

    return apiHealthRecordEvent($db, 'smartpay', $endpointName, $failureType, $statusSource, $message, $httpCode, $durationMs, $context);
}

function smartpayRequestAdminToken(&$errorMessage = null)
{
    smartpaySetLastTokenAttempt();
    $startedAt = microtime(true);
    $ch = curl_init(SMARTPAY_API_BASE_URL . '/adminlogin');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode([
            'username' => SMARTPAY_ADMIN_USERNAME,
            'password' => SMARTPAY_ADMIN_PASSWORD,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $curlError = curl_error($ch);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $failureType = apiHealthInferFailureType(null, $curlError);
        $errorMessage = 'Failed to connect to API: ' . $curlError;
        smartpaySetLastTokenAttempt($failureType, null, $errorMessage, $durationMs);
        smartpayRecordHealth('adminlogin', $failureType, $errorMessage, null, $durationMs, 'upstream');
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

    $data = json_decode($response, true);
    $jsonError = json_last_error();
    curl_close($ch);

    if ($httpCode !== 200) {
        $failureType = apiHealthInferFailureType($httpCode, '', $response);
        $errorMessage = is_array($data) && isset($data['message'])
            ? $data['message']
            : smartpayDescribeUnexpectedResponse($httpCode, $contentType, $response);
        smartpaySetLastTokenAttempt($failureType, $httpCode, $errorMessage, $durationMs);
        smartpayRecordHealth('adminlogin', $failureType, $errorMessage, $httpCode, $durationMs, 'upstream');
        return null;
    }

    if ($jsonError !== JSON_ERROR_NONE) {
        $errorMessage = smartpayDescribeUnexpectedResponse($httpCode, $contentType, $response);
        smartpaySetLastTokenAttempt('invalid_response', $httpCode, $errorMessage, $durationMs);
        smartpayRecordHealth('adminlogin', 'invalid_response', $errorMessage, $httpCode, $durationMs, 'upstream');
        return null;
    }

    if (!isset($data['access_token'])) {
        $failureType = 'invalid_response';
        $errorMessage = $data['message'] ?? 'Smartpay admin login response did not include an access token.';
        smartpaySetLastTokenAttempt($failureType, $httpCode, $errorMessage, $durationMs);
        smartpayRecordHealth('adminlogin', $failureType, $errorMessage, $httpCode, $durationMs, 'upstream');
        return null;
    }

    smartpaySetLastTokenAttempt('ok', $httpCode, 'Smartpay admin token refreshed successfully.', $durationMs);
    smartpayRecordHealth('adminlogin', 'ok', 'Smartpay admin token refreshed successfully.', $httpCode, $durationMs, 'upstream');

    return $data['access_token'];
}

function smartpayEnsureSessionToken(&$errorMessage = null, $forceRefresh = false)
{
    if (!$forceRefresh && !empty($_SESSION['access_token'])) {
        return $_SESSION['access_token'];
    }

    $token = smartpayRequestAdminToken($errorMessage);
    if ($token === null) {
        return null;
    }

    $_SESSION['access_token'] = $token;
    return $token;
}