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
if (!$input || !isset($input['id']) || !isset($input['email_address']) || !isset($input['first_name']) || !isset($input['last_name'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: id, email_address, first_name, last_name'
    ]);
    exit();
}

// Extract and validate data
$id = (int) $input['id'];
$email_address = trim($input['email_address']);
$first_name = trim($input['first_name']);
$middle_name = trim($input['middle_name'] ?? '');
$last_name = trim($input['last_name']);
$suffix = trim($input['suffix'] ?? '');
$mapping = trim($input['mapping'] ?? '');

// Basic validation
if ($id <= 0 || empty($email_address) || empty($first_name) || empty($last_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID or required fields are empty'
    ]);
    exit();
}

// Validate email format
if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address format'
    ]);
    exit();
}

// Check if email already exists for a different record
try {
    $check_sql = "SELECT id FROM food WHERE email_address = ? AND id != ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param('si', $email_address, $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'A registration with this email address already exists'
        ]);
        $check_stmt->close();
        exit();
    }
    $check_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error while checking email: ' . $e->getMessage()
    ]);
    exit();
}

// Update food registration
try {
    $update_sql = "UPDATE food SET email_address = ?, first_name = ?, middle_name = ?, last_name = ?, suffix = ?, mapping = ? WHERE id = ?";
    $update_stmt = $db->prepare($update_sql);
    
    // Handle empty mapping
    $mapping_value = !empty($mapping) ? $mapping : null;
    
    $update_stmt->bind_param('ssssssi', $email_address, $first_name, $middle_name, $last_name, $suffix, $mapping_value, $id);
    
    if ($update_stmt->execute()) {
        if ($update_stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Food registration updated successfully',
                'data' => [
                    'id' => $id,
                    'email_address' => $email_address,
                    'first_name' => $first_name,
                    'middle_name' => $middle_name,
                    'last_name' => $last_name,
                    'suffix' => $suffix,
                    'mapping' => $mapping_value
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes were made or record not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update food registration'
        ]);
    }
    
    $update_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$db->close();
?>