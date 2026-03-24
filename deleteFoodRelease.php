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

if (!isset($input['release_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Release ID is required']);
    exit();
}

$release_id = intval($input['release_id']);
$user_id = intval($_SESSION['user_id']);

try {
    // First, get details of the release record to be deleted for logging
    $release_id_escaped = $db->real_escape_string($release_id);
    $query = "
        SELECT 
            fr.id,
            fr.food_id,
            fr.release_date,
            fr.release_time,
            fr.released_by,
            fr.notes,
            f.email_address,
            f.first_name,
            f.last_name,
            u.username as released_by_username
        FROM food_releases fr
        JOIN food f ON fr.food_id = f.id
        LEFT JOIN users u ON fr.released_by = u.user_id
        WHERE fr.id = $release_id_escaped
    ";
    
    $result = $db->query($query);
    if (!$result || $result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Food release record not found']);
        exit();
    }
    
    $release_record = $result->fetch_assoc();
    
    // Check if the current user has permission to delete this record
    // For now, allow any authenticated user to delete, but log who did it
    
    // Delete the release record
    $delete_query = "DELETE FROM food_releases WHERE id = $release_id_escaped";
    
    if (!$db->query($delete_query)) {
        throw new Exception('Failed to delete food release record: ' . $db->error);
    }
    
    // Check if the deletion was successful
    if ($db->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No records were deleted. Release may have already been removed.']);
        exit();
    }
    
    // Log the deletion for audit purposes
    error_log(sprintf(
        "Food release deleted: ID=%d, Food ID=%d, Date=%s, Registrant=%s %s (%s), Deleted by User ID=%d at %s",
        $release_record['id'],
        $release_record['food_id'],
        $release_record['release_date'],
        $release_record['first_name'],
        $release_record['last_name'],
        $release_record['email_address'],
        $user_id,
        getCurrentDateTime()
    ));
    
    // Get the convention day number for response
    $convention_dates = ['2025-10-16', '2025-10-17', '2025-10-18'];
    $day_number = array_search($release_record['release_date'], $convention_dates) + 1;
    
    echo json_encode([
        'success' => true,
        'message' => "Food release record for Day $day_number has been successfully deleted" . (isTestMode() ? " [TEST MODE]" : ""),
        'data' => [
            'deleted_release_id' => $release_id,
            'food_id' => $release_record['food_id'],
            'release_date' => $release_record['release_date'],
            'convention_day' => "Day $day_number",
            'registrant_name' => $release_record['first_name'] . ' ' . $release_record['last_name'],
            'registrant_email' => $release_record['email_address'],
            'deleted_by' => $user_id,
            'deletion_time' => getCurrentDateTime(),
            'test_mode' => isTestMode()
        ]
    ]);
    
} catch (Exception $e) {
    error_log("Food release deletion error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while deleting the food release record: ' . $e->getMessage()
    ]);
}

$db->close();
?>