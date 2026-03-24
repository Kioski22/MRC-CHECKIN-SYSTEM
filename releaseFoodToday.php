<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

include 'dbConnection.php';
include 'test_config.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['food_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Food ID is required']);
    exit();
}

$food_id = intval($input['food_id']);
$user_id = intval($_SESSION['user_id']);
$release_date = getCurrentDate(); // Use test-aware current date

// Validate that today is within the convention dates
$convention_dates = ['2025-10-16', '2025-10-17', '2025-10-18'];
if (!in_array($release_date, $convention_dates)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Food releases are only allowed on convention days (October 16-18, 2025)',
        'current_date' => $release_date,
        'test_mode' => isTestMode()
    ]);
    exit();
}

try {
    // Check if food registration exists
    $food_id_escaped = $db->real_escape_string($food_id);
    $query = "SELECT id, email_address, first_name, last_name FROM food WHERE id = $food_id_escaped";
    $result = $db->query($query);
    
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Food registration not found']);
        exit();
    }
    
    $food_registration = $result->fetch_assoc();
    
    // Check if food has already been released today
    $release_date_escaped = $db->real_escape_string($release_date);
    $check_query = "SELECT id FROM food_releases WHERE food_id = $food_id_escaped AND release_date = '$release_date_escaped'";
    $check_result = $db->query($check_query);
    
    if ($check_result && $check_result->num_rows > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Food has already been released to this registrant today',
            'release_date' => $release_date
        ]);
        exit();
    }
    
    // Insert the food release record
    $user_id_escaped = $db->real_escape_string($user_id);
    $release_time = getCurrentDateTime();
    $release_time_escaped = $db->real_escape_string($release_time);
    
    $notes = sprintf(
        "Food released on %s to %s %s (%s) via search interface",
        $release_date,
        $food_registration['first_name'],
        $food_registration['last_name'],
        $food_registration['email_address']
    );
    $notes_escaped = $db->real_escape_string($notes);
    
    $insert_query = "
        INSERT INTO food_releases (food_id, release_date, release_time, released_by, notes) 
        VALUES ($food_id_escaped, '$release_date_escaped', '$release_time_escaped', $user_id_escaped, '$notes_escaped')
    ";
    
    if (!$db->query($insert_query)) {
        throw new Exception('Failed to insert food release record: ' . $db->error);
    }
    
    $release_id = $db->insert_id;
    
    // Get the convention day number
    $day_number = array_search($release_date, $convention_dates) + 1;
    
    echo json_encode([
        'success' => true,
        'message' => "Food successfully released for Day $day_number" . (isTestMode() ? " [TEST MODE]" : ""),
        'data' => [
            'release_id' => $release_id,
            'food_id' => $food_id,
            'release_date' => $release_date,
            'convention_day' => "Day $day_number",
            'registrant_name' => $food_registration['first_name'] . ' ' . $food_registration['last_name'],
            'registrant_email' => $food_registration['email_address'],
            'released_by' => $user_id,
            'release_time' => $release_time,
            'test_mode' => isTestMode()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Food release error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while releasing food: ' . $e->getMessage()
    ]);
}

$db->close();
?>