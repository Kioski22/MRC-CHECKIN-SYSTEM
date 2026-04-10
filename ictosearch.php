<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');
require('dbConnection.php');  // Include your database connection file
require __DIR__ . '/smartpayAuth.php';
require __DIR__ . '/eventConfig.php';

// Initialize response array
$response = [
    "resultKey" => 0,
    "resultValue" => [],
    "errorCode" => 0,
    "errorMsg" => ""
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['errorCode'] = 1;
    $response['errorMsg'] = 'Invalid request method.';
    echo json_encode($response);
    exit();
}

// Check if 'search_term' is provided in the POST request
if (isset($_POST['search_term'])) {
    $searchTerm = trim($_POST['search_term']);

    if ($searchTerm === '') {
        $response['errorCode'] = 1;
        $response['errorMsg'] = "Missing 'search_term' parameter.";
    } else {
        $internalResults = searchLocalRegistrations($db, $searchTerm);
        $externalSearch = searchExternalRegistrations($searchTerm, getActiveEventId($db));
        $externalResults = $externalSearch['items'];
        $mergedResults = mergeRegistrationResults($internalResults, $externalResults);

        if (!empty($mergedResults)) {
            $response['resultKey'] = 1;
            $response['resultValue'] = $mergedResults;
            if (!empty($externalResults) && empty($internalResults)) {
                $response['errorMsg'] = 'Results loaded from external registration data.';
            }

            if (isset($externalSearch['api_health'])) {
                $response['api_health'] = $externalSearch['api_health'];
                $response['failure_type'] = $externalSearch['api_health']['failure_type'];
                $response['status_source'] = $externalSearch['api_health']['status_source'];
            }
        } else {
            if (!empty($internalResults)) {
                $response['resultKey'] = 1;
                $response['resultValue'] = $internalResults;
            } else {
                $response['errorCode'] = 1;
                if (isset($externalSearch['api_health']) && !in_array($externalSearch['api_health']['failure_type'], ['ok', 'no_data'], true)) {
                    $response['errorMsg'] = $externalSearch['api_health']['message'];
                    $response['failure_type'] = $externalSearch['api_health']['failure_type'];
                    $response['status_source'] = $externalSearch['api_health']['status_source'];
                } else {
                    $response['errorMsg'] = 'No record found for the provided search term.';
                }
            }

            if (isset($externalSearch['api_health'])) {
                $response['api_health'] = $externalSearch['api_health'];
            }
        }
    }
} else {
    // Missing 'search_term' parameter
    $response['errorCode'] = 1;
    $response['errorMsg'] = "Missing 'search_term' parameter.";
}

function searchLocalRegistrations($db, $searchTerm) {
    $results = [];

    $sql = "SELECT first_name, last_name, middle_name, personal_email, prc_license_number, name_suffix, chapter, qrcode_url
            FROM registrations
            WHERE first_name LIKE ? OR personal_email LIKE ? OR prc_license_number LIKE ? OR last_name LIKE ?";

    if ($stmt = $db->prepare($sql)) {
        $likeTerm = "%" . $searchTerm . "%";
        $stmt->bind_param("ssss", $likeTerm, $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($data = $result->fetch_assoc()) {
            $results[] = [
                'firstname' => $data['first_name'],
                'lastname' => $data['last_name'],
                'middlename' => $data['middle_name'],
                'email' => $data['personal_email'],
                'prclicencenumber' => $data['prc_license_number'],
                'chapter' => $data['chapter'],
                'suffix' => $data['name_suffix'],
                'qrcode' => $data['qrcode_url'],
            ];
        }

        $stmt->close();
    }

    return $results;
}

function searchExternalRegistrations($searchTerm, $eventId) {
    $startedAt = microtime(true);
    $tokenError = null;
    $accessToken = smartpayEnsureSessionToken($tokenError);
    if ($accessToken === null) {
        error_log('ictosearch could not get access token: ' . $tokenError);
        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        $meta = smartpayBuildTokenFailureMeta('postSearch', $tokenError ?: 'External Smartpay API authentication failed. This hosted app is still online.', $durationMs);
        return [
            'items' => [],
            'api_health' => $meta,
        ];
    }

    $externalResults = executeExternalSearchRequest($searchTerm, $eventId, $accessToken, $httpCode, $curlError);

    if ($httpCode === 401) {
        $accessToken = smartpayEnsureSessionToken($tokenError, true);
        if ($accessToken === null) {
            error_log('ictosearch token refresh failed: ' . $tokenError);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $meta = smartpayBuildTokenFailureMeta('postSearch', $tokenError ?: 'External Smartpay API authentication failed. This hosted app is still online.', $durationMs);
            return [
                'items' => [],
                'api_health' => $meta,
            ];
        }

        $externalResults = executeExternalSearchRequest($searchTerm, $eventId, $accessToken, $httpCode, $curlError);
    }

    $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

    if ($externalResults === null) {
        error_log('ictosearch external search failed. HTTP code: ' . $httpCode . ' Error: ' . $curlError);
        $failureType = apiHealthInferFailureType($httpCode, $curlError, null);
        $meta = apiHealthBuildMeta('smartpay', 'postSearch', $failureType, 'upstream', $httpCode, $durationMs, apiHealthDefaultUserMessage($failureType));
        smartpayRecordHealth('postSearch', $failureType, 'Search request failed.', $httpCode, $durationMs, 'upstream', ['event_id' => $eventId]);
        return [
            'items' => [],
            'api_health' => $meta,
        ];
    }

    if (empty($externalResults)) {
        $meta = apiHealthBuildMeta('smartpay', 'postSearch', 'no_data', 'upstream', $httpCode, $durationMs, 'Smartpay API responded normally, but found no matching registration records.');
        smartpayRecordHealth('postSearch', 'no_data', 'Search completed with no matching records.', $httpCode, $durationMs, 'upstream', ['event_id' => $eventId]);
        return [
            'items' => [],
            'api_health' => $meta,
        ];
    }

    $meta = apiHealthBuildMeta('smartpay', 'postSearch', 'ok', 'upstream', $httpCode, $durationMs, 'Smartpay API returned registration search results successfully.');
    smartpayRecordHealth('postSearch', 'ok', 'Search request succeeded.', $httpCode, $durationMs, 'upstream', ['event_id' => $eventId, 'result_count' => count($externalResults)]);

    return [
        'items' => $externalResults,
        'api_health' => $meta,
    ];
}

function executeExternalSearchRequest($searchTerm, $eventId, $accessToken, &$httpCode, &$curlError) {
    $apiUrl = SMARTPAY_API_BASE_URL . '/postSearch?search=' . urlencode($searchTerm) . '&event_id=' . intval($eventId);
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'MRC-Checkin/1.0'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError || $httpCode !== 200) {
        return null;
    }

    $decoded = json_decode($response, true);
    if (!isset($decoded['resultKey']) || intval($decoded['resultKey']) !== 1 || empty($decoded['resultValue'])) {
        return [];
    }

    $results = [];
    foreach ($decoded['resultValue'] as $person) {
        $results[] = [
            'firstname' => $person['firstname'] ?? '',
            'lastname' => $person['lastname'] ?? '',
            'middlename' => $person['middlename'] ?? '',
            'email' => $person['email'] ?? '',
            'prclicencenumber' => $person['prclicencenumber'] ?? '',
            'chapter' => $person['chapter'] ?? '',
            'suffix' => $person['suffix'] ?? '',
            'qrcode' => $person['qrcode'] ?? '',
            'RegistrationType' => $person['RegistrationType'] ?? ''
        ];
    }

    return $results;
}

function mergeRegistrationResults($internalResults, $externalResults) {
    $merged = [];
    $seenKeys = [];

    foreach (array_merge($externalResults, $internalResults) as $result) {
        $key = strtolower(trim(($result['email'] ?? '') . '|' . ($result['firstname'] ?? '') . '|' . ($result['lastname'] ?? '')));
        if ($key === '||') {
            $key = strtolower(trim(($result['prclicencenumber'] ?? '') . '|' . ($result['qrcode'] ?? '')));
        }

        if (!isset($seenKeys[$key])) {
            $merged[] = $result;
            $seenKeys[$key] = true;
        }
    }

    return $merged;
}

// Close the database connection
$db->close();
ob_clean(); // CLEAN ANY ACCIDENTAL OUTPUT
// Output the response in JSON format
echo json_encode($response);
exit();