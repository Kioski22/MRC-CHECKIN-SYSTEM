<?php

/**
 * Send email using Gmail API with OAuth2 refresh token flow.
 *
 * Strict mode:
 * - Refresh token and credentials path must come from token_reference.php or config_gmail_reference.php
 * - Client credentials must come from credentials JSON
 * - Sending always uses Gmail API
 */

function gmail_api_base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function gmail_api_encode_header($text)
{
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}

function gmail_api_get_reference_config()
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $config = [];
    $configPath = __DIR__ . '/config_gmail_reference.php';
    if (is_file($configPath)) {
        $loaded = require $configPath;
        if (is_array($loaded)) {
            $config = $loaded;
        }
    }

    return $config;
}

function gmail_api_get_token_reference()
{
    static $tokenReference = null;
    if ($tokenReference !== null) {
        return $tokenReference;
    }

    $tokenReference = [];
    $tokenReferencePath = __DIR__ . '/token_reference.php';
    if (is_file($tokenReferencePath)) {
        $loaded = require $tokenReferencePath;
        if (is_array($loaded)) {
            $tokenReference = $loaded;
        }
    }

    return $tokenReference;
}

function gmail_api_credentials_from_json($credentialsPath, &$error)
{
    if (!$credentialsPath || !is_file($credentialsPath)) {
        $error = 'Gmail credentials file not found: ' . (string) $credentialsPath;
        return null;
    }

    $jsonText = file_get_contents($credentialsPath);
    if ($jsonText === false) {
        $error = 'Unable to read Gmail credentials file.';
        return null;
    }

    $json = json_decode($jsonText, true);
    if (!is_array($json)) {
        $error = 'Invalid Gmail credentials JSON.';
        return null;
    }

    $web = $json['web'] ?? null;
    if (!is_array($web)) {
        $error = 'Gmail credentials JSON missing web section.';
        return null;
    }

    if (empty($web['client_id']) || empty($web['client_secret'])) {
        $error = 'Gmail credentials JSON missing client_id or client_secret.';
        return null;
    }

    return [
        'client_id' => (string) $web['client_id'],
        'client_secret' => (string) $web['client_secret'],
        'token_uri' => !empty($web['token_uri']) ? (string) $web['token_uri'] : 'https://oauth2.googleapis.com/token',
    ];
}

function gmail_api_mail_defaults()
{
    $config = gmail_api_get_reference_config();
    $mail = isset($config['mail']) && is_array($config['mail']) ? $config['mail'] : [];

    $fromAddress = (string) ($mail['from_address'] ?? getenv('GMAIL_API_SENDER') ?: '');
    $fromName = (string) ($mail['from_name'] ?? '');

    return [
        'from_address' => $fromAddress,
        'from_name' => $fromName,
    ];
}

function gmail_api_get_access_token(&$error)
{
    $config = gmail_api_get_reference_config();
    $tokenReference = gmail_api_get_token_reference();
    $gmailApi = isset($config['gmail_api']) && is_array($config['gmail_api']) ? $config['gmail_api'] : [];

    $credentialsPath = (string) ($tokenReference['TICKET_GMAIL_CREDENTIALS_PATH'] ?? ($gmailApi['credentials_path'] ?? ''));
    $refreshToken = (string) ($tokenReference['TICKET_GMAIL_REFRESH_TOKEN'] ?? ($gmailApi['refresh_token'] ?? ''));

    if ($credentialsPath === '') {
        $error = 'Strict Gmail API mode: missing TICKET_GMAIL_CREDENTIALS_PATH in token_reference.php or gmail_api.credentials_path in config.';
        return null;
    }

    if ($refreshToken === '') {
        $error = 'Strict Gmail API mode: missing TICKET_GMAIL_REFRESH_TOKEN in token_reference.php or gmail_api.refresh_token in config.';
        return null;
    }

    $credentials = gmail_api_credentials_from_json($credentialsPath, $credentialsError);
    if (!is_array($credentials)) {
        $error = $credentialsError;
        return null;
    }

    $clientId = $credentials['client_id'];
    $clientSecret = $credentials['client_secret'];
    $tokenUri = $credentials['token_uri'];

    if (!$clientId || !$clientSecret || !$refreshToken) {
        $error = 'Missing Gmail API OAuth configuration (client_id, client_secret, or refresh_token).';
        return null;
    }

    if (!function_exists('curl_init')) {
        $error = 'cURL extension is required for Gmail API.';
        return null;
    }

    $ch = curl_init($tokenUri);
    $postFields = http_build_query([
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'refresh_token' => $refreshToken,
        'grant_type' => 'refresh_token',
    ]);

    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = 'OAuth request failed: ' . $curlError;
        return null;
    }

    $json = json_decode($response, true);
    if ($httpCode < 200 || $httpCode >= 300 || !is_array($json) || empty($json['access_token'])) {
        $error = 'OAuth token error: ' . $response;
        return null;
    }

    return $json['access_token'];
}

function send_via_gmail_api($toEmail, $toName, $subject, $htmlBody, $textBody, $fromEmail, $fromName, &$error)
{
    $accessToken = gmail_api_get_access_token($error);
    if (!$accessToken) {
        return false;
    }

    $defaults = gmail_api_mail_defaults();
    if (!$fromEmail) {
        $fromEmail = $defaults['from_address'];
    }
    if (!$fromName) {
        $fromName = $defaults['from_name'];
    }

    if (!$fromEmail) {
        $error = 'Missing sender email address in mail configuration.';
        return false;
    }

    $config = gmail_api_get_reference_config();
    $gmailApi = isset($config['gmail_api']) && is_array($config['gmail_api']) ? $config['gmail_api'] : [];
    $gmailUserId = (string) ($gmailApi['user_id'] ?? 'me');
    if ($gmailUserId === '') {
        $gmailUserId = 'me';
    }

    $boundary = '=_Part_' . bin2hex(random_bytes(12));

    $fromHeaderName = gmail_api_encode_header((string) $fromName);
    $toHeaderName = $toName ? gmail_api_encode_header((string) $toName) . ' ' : '';
    $subjectHeader = gmail_api_encode_header((string) $subject);

    $mime = '';
    $mime .= 'From: ' . $fromHeaderName . ' <' . $fromEmail . "\r\n";
    $mime .= 'To: ' . $toHeaderName . '<' . $toEmail . ">\r\n";
    $mime .= 'Subject: ' . $subjectHeader . "\r\n";
    $mime .= 'MIME-Version: 1.0' . "\r\n";
    $mime .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n\r\n";

    $mime .= '--' . $boundary . "\r\n";
    $mime .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
    $mime .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
    $mime .= chunk_split(base64_encode((string) $textBody), 76, "\r\n") . "\r\n";

    $mime .= '--' . $boundary . "\r\n";
    $mime .= 'Content-Type: text/html; charset=UTF-8' . "\r\n";
    $mime .= 'Content-Transfer-Encoding: base64' . "\r\n\r\n";
    $mime .= chunk_split(base64_encode((string) $htmlBody), 76, "\r\n") . "\r\n";

    $mime .= '--' . $boundary . '--';

    $payload = json_encode([
        'raw' => gmail_api_base64url_encode($mime),
    ]);

    $ch = curl_init('https://gmail.googleapis.com/gmail/v1/users/' . rawurlencode($gmailUserId) . '/messages/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT => 20,
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        $error = 'Gmail send request failed: ' . $curlError;
        return false;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
        $error = 'Gmail API send error: ' . $response;
        return false;
    }

    return true;
}
