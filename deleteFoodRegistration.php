<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Check if user is authenticated
requireAuthenticatedUser(true);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
    exit();
}

// Get JSON input
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

// Fallback to POST data if JSON failed
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

// Validate required fields
if (!$input || !isset($input['id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required field: id'
    ]);
    exit();
}

// Extract and validate data
$id = (int) $input['id'];

// Basic validation
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID'
    ]);
    exit();
}

// Check if record exists before deleting
try {
    $check_sql = "SELECT id, email_address, first_name, last_name FROM food WHERE id = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param('i', $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Food registration not found'
        ]);
        $check_stmt->close();
        exit();
    }
    
    $registration = $result->fetch_assoc();
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error while checking record: ' . $e->getMessage()
    ]);
    exit();
}

// Delete food registration
try {
    $delete_sql = "DELETE FROM food WHERE id = ?";
    $delete_stmt = $db->prepare($delete_sql);
    $delete_stmt->bind_param('i', $id);
    
    if ($delete_stmt->execute()) {
        if ($delete_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Food registration deleted successfully',
                'deleted_record' => [
                    'id' => $id,
                    'email_address' => $registration['email_address'],
                    'name' => trim($registration['first_name'] . ' ' . $registration['last_name'])
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No record was deleted'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete food registration'
        ]);
    }
    
    $delete_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$db->close();
?>