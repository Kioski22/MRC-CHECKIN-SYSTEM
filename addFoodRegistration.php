<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

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
if (!$input || !isset($input['email_address']) || !isset($input['first_name']) || !isset($input['last_name'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Missing required fields: email_address, first_name, last_name'
    ]);
    exit();
}

// Extract and validate data
$email_address = trim($input['email_address']);
$first_name = trim($input['first_name']);
$middle_name = trim($input['middle_name'] ?? '');
$last_name = trim($input['last_name']);
$suffix = trim($input['suffix'] ?? '');
$mapping = trim($input['mapping'] ?? '');

// Basic validation
if (empty($email_address) || empty($first_name) || empty($last_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Email address, first name, and last name are required'
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

// Check if email already exists
try {
    $check_sql = "SELECT id FROM food WHERE email_address = ?";
    $check_stmt = $db->prepare($check_sql);
    $check_stmt->bind_param('s', $email_address);
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

// Insert new food registration
try {
    $insert_sql = "INSERT INTO food (email_address, first_name, middle_name, last_name, suffix, mapping) VALUES (?, ?, ?, ?, ?, ?)";
    $insert_stmt = $db->prepare($insert_sql);
    
    // Handle empty mapping
    $mapping_value = !empty($mapping) ? $mapping : null;
    
    $insert_stmt->bind_param('ssssss', $email_address, $first_name, $middle_name, $last_name, $suffix, $mapping_value);
    
    if ($insert_stmt->execute()) {
        $new_id = $db->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Food registration added successfully',
            'data' => [
                'id' => $new_id,
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
            'message' => 'Failed to insert food registration'
        ]);
    }
    
    $insert_stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$db->close();
?>