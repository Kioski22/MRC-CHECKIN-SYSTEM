<?php

const SMARTPAY_API_BASE_URL = 'https://psme-api.smartpay.ph/public/index.php/api';
const SMARTPAY_ADMIN_USERNAME = 'psme';
const SMARTPAY_ADMIN_PASSWORD = '9hbpRSKahp@T';

function smartpayRequestAdminToken(&$errorMessage = null)
{
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
        $errorMessage = 'Failed to connect to API: ' . curl_error($ch);
        curl_close($ch);
        return null;
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($httpCode !== 200 || !isset($data['access_token'])) {
        $errorMessage = $data['message'] ?? ('Failed to retrieve access token. HTTP ' . $httpCode);
        return null;
    }

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