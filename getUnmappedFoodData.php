<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Check if user is authenticated (skip for GET method testing with Python HTTP server)
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    // Allow GET method for testing with Python HTTP server
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }
}

try {
    // Query to get all food records with no mapping (NULL or empty)
    $sql = "SELECT id, email_address FROM food WHERE mapping IS NULL OR mapping = '' ORDER BY id";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $unmappedRecords = [];
    while ($row = $result->fetch_assoc()) {
        $unmappedRecords[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $unmappedRecords,
        'count' => count($unmappedRecords)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$db->close();
?>