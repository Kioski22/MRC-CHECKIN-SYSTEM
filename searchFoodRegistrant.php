<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

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
        // Get access token from session
        if (!isset($_SESSION['access_token'])) {
            throw new Exception('No access token available');
        }
        
        $apiUrl = "https://psme-api.smartpay.ph/public/index.php/api/postSearch?search=" . urlencode($searchTerm) . "&event_id=65";
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Authorization: Bearer ' . $_SESSION['access_token'],
                    'Content-Type: application/json'
                ],
                'timeout' => 10
            ]
        ]);
        
        $response = file_get_contents($apiUrl, false, $context);
        
        if ($response === false) {
            throw new Exception('API request failed');
        }
        
        $data = json_decode($response, true);
        
        if (!$data || $data['resultKey'] !== 1) {
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