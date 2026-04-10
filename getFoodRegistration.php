<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json');

// Check if user is authenticated
requireAuthenticatedUser(true);

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Only GET requests allowed']);
    exit();
}

// Get ID from query parameters
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Basic validation
if ($id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing ID parameter'
    ]);
    exit();
}

// Get food registration
try {
    $sql = "SELECT id, email_address, first_name, middle_name, last_name, suffix, mapping FROM food WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Food registration not found'
        ]);
        $stmt->close();
        exit();
    }
    
    $registration = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $registration
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$db->close();
?>