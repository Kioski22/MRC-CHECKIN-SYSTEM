<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/auth.php';
require __DIR__ . '/smartpayAuth.php';
require __DIR__ . '/eventConfig.php';

// Check authentication
requireAuthenticatedUser(true);

include 'dbConnection.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['search']) || empty(trim($input['search']))) {
    echo json_encode(['success' => false, 'message' => 'Search term is required']);
    exit();
}

$searchTerm = trim($input['search']);

try {
    $results = [];
    
    // First, search in internal food database
    $internalResults = searchInternalFoodDatabase($searchTerm, $db);
    
    // Then search in external PSME API
    $externalResults = searchExternalPSMEAPI($searchTerm);
    
    // Merge and deduplicate results
    $combinedResults = mergeSearchResults($internalResults, $externalResults);
    
    echo json_encode([
        'success' => true,
        'results' => $combinedResults,
        'total_found' => count($combinedResults),
        'sources' => [
            'internal_found' => count($internalResults),
            'external_found' => count($externalResults)
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Food search error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Search failed: ' . $e->getMessage()
    ]);
}

function searchInternalFoodDatabase($searchTerm, $db) {
    $results = [];
    
    // Escape search term
    $searchEscaped = $db->real_escape_string($searchTerm);
    
    // Search in food_release_status view for comprehensive information
    $query = "
        SELECT 
            frs.id as food_id,
            frs.email_address,
            frs.first_name,
            frs.middle_name,
            frs.last_name,
            frs.suffix,
            frs.mapping,
            frs.day1_released,
            frs.day2_released,
            frs.day3_released,
            frs.total_releases,
            frs.can_release_today,
            'internal' as source
        FROM food_release_status frs
        WHERE (
            frs.first_name LIKE '%$searchEscaped%' OR
            frs.last_name LIKE '%$searchEscaped%' OR
            frs.email_address LIKE '%$searchEscaped%' OR
            frs.middle_name LIKE '%$searchEscaped%' OR
            frs.suffix LIKE '%$searchEscaped%' OR
            frs.mapping LIKE '%$searchEscaped%'
        )
        ORDER BY frs.last_name, frs.first_name
        LIMIT 50
    ";
    
    $result = $db->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Add release status information
            $row['release_status'] = [
                'day1_released' => !is_null($row['day1_released']),
                'day2_released' => !is_null($row['day2_released']),
                'day3_released' => !is_null($row['day3_released']),
                'total_releases' => intval($row['total_releases']),
                'can_release_today' => boolval($row['can_release_today'])
            ];
            
            $results[] = $row;
        }
    }
    
    return $results;
}

function searchExternalPSMEAPI($searchTerm) {
    try {
        global $db;
        $startedAt = microtime(true);

        $tokenError = null;
        $accessToken = smartpayEnsureSessionToken($tokenError);
        if ($accessToken === null) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            $meta = smartpayBuildTokenFailureMeta('foodPostSearch', $tokenError ?: 'Food registrant search could not authenticate with Smartpay.', $durationMs);
            smartpayRecordHealth('foodPostSearch', $meta['failure_type'], $meta['message'], $meta['http_code'], $durationMs, 'upstream');
            throw new Exception($meta['message']);
        }

        $eventId = getActiveEventId($db);
        
        $apiUrl = SMARTPAY_API_BASE_URL . "/postSearch?search=" . urlencode($searchTerm) . "&event_id=" . intval($eventId);
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Bearer ' . $accessToken,
                    'Content-Type: application/json'
                ],
                'timeout' => 10
            ]
        ]);
        
        $response = file_get_contents($apiUrl, false, $context);
        
        if ($response === false) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            smartpayRecordHealth('foodPostSearch', 'network_failure', 'Food registrant search could not reach Smartpay.', null, $durationMs, 'upstream', ['event_id' => $eventId]);
            throw new Exception('API request failed');
        }
        
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            smartpayRecordHealth('foodPostSearch', 'invalid_response', 'Food registrant search received invalid JSON from Smartpay.', 200, $durationMs, 'upstream', ['event_id' => $eventId]);
            return [];
        }
        
        if (!$data || $data['resultKey'] !== 1) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
            smartpayRecordHealth('foodPostSearch', 'no_data', 'Food registrant search returned no matching records.', 200, $durationMs, 'upstream', ['event_id' => $eventId]);
            return []; // No results or error
        }
        
        $results = [];
        foreach ($data['resultValue'] as $person) {
            // Check if this person has food registration in our internal database
            $foodInfo = checkInternalFoodRegistration($person['email']);
            
            $result = [
                'email' => $person['email'],
                'first_name' => $person['firstname'],
                'middle_name' => $person['middlename'],
                'last_name' => $person['lastname'],
                'prclicencenumber' => $person['prclicencenumber'],
                'chapter' => $person['chapter'],
                'qrcode' => $person['qrcode'],
                'source' => 'external'
            ];
            
            // Add food registration info if found
            if ($foodInfo) {
                $result['food_id'] = $foodInfo['id'];
                $result['email_address'] = $foodInfo['email_address'];
                $result['suffix'] = $foodInfo['suffix'];
                $result['mapping'] = $foodInfo['mapping'];
                $result['release_status'] = [
                    'day1_released' => !is_null($foodInfo['day1_released']),
                    'day2_released' => !is_null($foodInfo['day2_released']),
                    'day3_released' => !is_null($foodInfo['day3_released']),
                    'total_releases' => intval($foodInfo['total_releases']),
                    'can_release_today' => boolval($foodInfo['can_release_today'])
                ];
            } else {
                $result['release_status'] = null; // Not registered for food
            }
            
            $results[] = $result;
        }

        $durationMs = (int) round((microtime(true) - $startedAt) * 1000);
        smartpayRecordHealth('foodPostSearch', 'ok', 'Food registrant search succeeded.', 200, $durationMs, 'upstream', ['event_id' => $eventId, 'result_count' => count($results)]);
        
        return $results;
        
    } catch (Exception $e) {
        error_log("External API search error: " . $e->getMessage());
        return [];
    }
}

function checkInternalFoodRegistration($email) {
    global $db;
    
    if (!$email) return null;
    
    $emailEscaped = $db->real_escape_string($email);
    
    $query = "
        SELECT 
            id,
            email_address,
            suffix,
            mapping,
            day1_released,
            day2_released,
            day3_released,
            total_releases,
            can_release_today
        FROM food_release_status 
        WHERE email_address = '$emailEscaped' OR mapping = '$emailEscaped'
        LIMIT 1
    ";
    
    $result = $db->query($query);
    if ($result && $result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

function mergeSearchResults($internalResults, $externalResults) {
    $merged = [];
    $seenEmails = [];
    
    // Add internal results first (they have priority)
    foreach ($internalResults as $result) {
        $email = strtolower($result['email_address']);
        if (!isset($seenEmails[$email])) {
            $merged[] = $result;
            $seenEmails[$email] = true;
        }
    }
    
    // Add external results that don't exist in internal results
    foreach ($externalResults as $result) {
        $email = strtolower($result['email']);
        if (!isset($seenEmails[$email])) {
            $merged[] = $result;
            $seenEmails[$email] = true;
        }
    }
    
    return $merged;
}

$db->close();
?>