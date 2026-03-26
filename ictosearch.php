<?php
session_start();
header('Content-Type: application/json');
require('dbConnection.php');  // Include your database connection file

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
        $externalResults = searchExternalRegistrations($searchTerm);
        $mergedResults = mergeRegistrationResults($internalResults, $externalResults);

        if (!empty($mergedResults)) {
            $response['resultKey'] = 1;
            $response['resultValue'] = $mergedResults;
            if (!empty($externalResults) && empty($internalResults)) {
                $response['errorMsg'] = 'Results loaded from external registration data.';
            }
        } else {
            $response['errorCode'] = 1;
            $response['errorMsg'] = 'No record found for the provided search term.';
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

function searchExternalRegistrations($searchTerm) {
    if (!isset($_SESSION['access_token']) || empty($_SESSION['access_token'])) {
        return [];
    }

    $apiUrl = 'https://psme-api.smartpay.ph/public/index.php/api/postSearch?search=' . urlencode($searchTerm) . '&event_id=65';
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $_SESSION['access_token'],
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
        error_log('ictosearch external search failed. HTTP code: ' . $httpCode . ' Error: ' . $curlError);
        return [];
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

// Output the response in JSON format
echo json_encode($response);
