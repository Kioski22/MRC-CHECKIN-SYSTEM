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
include 'test_config.php';

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_GET['id']) && !isset($_GET['mapping'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Food ID or mapping is required']);
    exit();
}

// Determine lookup method
$lookup_by_mapping = isset($_GET['mapping']);
$lookup_value = $lookup_by_mapping ? $_GET['mapping'] : intval($_GET['id']);

try {
    // Get release status for this registrant using the view - supports both id and mapping lookup
    $lookup_value_escaped = $db->real_escape_string($lookup_value);
    
    if ($lookup_by_mapping) {
        $where_clause = "mapping = '$lookup_value_escaped'";
    } else {
        $where_clause = "id = $lookup_value_escaped";
    }
    
    $query = "
        SELECT 
            id,
            email_address,
            first_name,
            middle_name,
            last_name,
            suffix,
            mapping,
            day1_released,
            day2_released,
            day3_released,
            total_releases,
            can_release_today
        FROM food_release_status 
        WHERE $where_clause
    ";
    
    $result = $db->query($query);
    if (!$result) {
        throw new Exception('Query failed: ' . $db->error);
    }
    
    $release_status = $result->fetch_assoc();
    
    if (!$release_status) {
        echo json_encode(['success' => false, 'message' => 'Food registration not found']);
        exit();
    }
    
    // Get detailed release history - simplified query
    $food_id = $release_status['id']; // Get the actual food_id from the result
    $food_id_escaped = $db->real_escape_string($food_id);
    
    $history_query = "
        SELECT 
            release_id,
            release_date,
            release_time,
            released_by_username,
            convention_day,
            notes
        FROM food_releases_view 
        WHERE food_id = $food_id_escaped
        ORDER BY release_date ASC
    ";
    
    $history_result = $db->query($history_query);
    $release_history = [];
    
    if ($history_result) {
        while ($row = $history_result->fetch_assoc()) {
            $release_history[] = $row;
        }
    }
    
    // Prepare release status for each day
    $current_date = getCurrentDate(); // Use test mode aware date function
    $daily_status = [
        'day1' => [
            'date' => '2025-10-16',
            'released' => !is_null($release_status['day1_released']),
            'release_time' => $release_status['day1_released'],
            'can_release' => ($current_date === '2025-10-16' && is_null($release_status['day1_released']))
        ],
        'day2' => [
            'date' => '2025-10-17',
            'released' => !is_null($release_status['day2_released']),
            'release_time' => $release_status['day2_released'],
            'can_release' => ($current_date === '2025-10-17' && is_null($release_status['day2_released']))
        ],
        'day3' => [
            'date' => '2025-10-18',
            'released' => !is_null($release_status['day3_released']),
            'release_time' => $release_status['day3_released'],
            'can_release' => ($current_date === '2025-10-18' && is_null($release_status['day3_released']))
        ]
    ];
    
    // Override can_release_today with test mode aware logic
    $can_release_today = false;
    if (isConventionDay($current_date)) {
        $today_key = null;
        if ($current_date === '2025-10-16') $today_key = 'day1';
        elseif ($current_date === '2025-10-17') $today_key = 'day2';
        elseif ($current_date === '2025-10-18') $today_key = 'day3';
        
        if ($today_key && !$daily_status[$today_key]['released']) {
            $can_release_today = true;
        }
    }
    
    // Debug logging for test mode
    if (isTestMode()) {
        error_log("getFoodReleaseStatus debug - Test Mode Active:");
        error_log("Current Date: " . $current_date);
        error_log("Is Convention Day: " . (isConventionDay($current_date) ? 'Yes' : 'No'));
        error_log("Today Key: " . ($today_key ?? 'null'));
        error_log("Can Release Today: " . ($can_release_today ? 'Yes' : 'No'));
        if ($today_key) {
            error_log("Today's status - Released: " . ($daily_status[$today_key]['released'] ? 'Yes' : 'No'));
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => [
            'registrant' => [
                'id' => $release_status['id'],
                'email_address' => $release_status['email_address'],
                'first_name' => $release_status['first_name'],
                'middle_name' => $release_status['middle_name'],
                'last_name' => $release_status['last_name'],
                'suffix' => $release_status['suffix'],
                'mapping' => $release_status['mapping'],
                'full_name' => trim($release_status['first_name'] . ' ' . 
                              ($release_status['middle_name'] ? $release_status['middle_name'] . ' ' : '') . 
                              $release_status['last_name'] . 
                              ($release_status['suffix'] ? ' ' . $release_status['suffix'] : ''))
            ],
            'release_summary' => [
                'total_releases' => intval($release_status['total_releases']),
                'can_release_today' => $can_release_today, // Use our test mode aware logic
                'current_date' => getCurrentDate(),
                'convention_days_remaining' => max(0, 3 - intval($release_status['total_releases']))
            ],
            'daily_status' => $daily_status,
            'release_history' => $release_history
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Get food release status error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching release status'
    ]);
}

$db->close();
?>