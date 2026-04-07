<?php

require_once __DIR__ . '/dbConnection.php';
require_once __DIR__ . '/apiHealth.php';

const SMARTPAY_API_BASE_URL = 'https://psme-api.smartpay.ph/public/index.php/api';
const SMARTPAY_ADMIN_USERNAME = 'psme';
const SMARTPAY_ADMIN_PASSWORD = '9hbpRSKahp@T';

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
        smartpayRecordHealth('adminlogin', $failureType, $errorMessage, null, $durationMs, 'upstream');
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
    curl_close($ch);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $errorMessage = 'Failed to decode token response from Smartpay API.';
        smartpayRecordHealth('adminlogin', 'invalid_response', $errorMessage, $httpCode, $durationMs, 'upstream');
        return null;
    }

    if ($httpCode !== 200 || !isset($data['access_token'])) {
        $failureType = apiHealthInferFailureType($httpCode, '', $response);
        if (!isset($data['access_token']) && $failureType === 'unknown') {
            $failureType = 'invalid_response';
        }
        $errorMessage = $data['message'] ?? ('Failed to retrieve access token. HTTP ' . $httpCode);
        smartpayRecordHealth('adminlogin', $failureType, $errorMessage, $httpCode, $durationMs, 'upstream');
        return null;
    }

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