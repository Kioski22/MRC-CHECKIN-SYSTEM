<?php
session_start();
include 'dbConnection.php';

header('Content-Type: application/json');

// Check if user is authenticated
if (!isset($_SESSION['access_token']) || !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Handle GET method for compatibility with Python HTTP server
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $params = $_GET;
    
    // Try manual parsing if GET is empty
    if (empty($params) && isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
        parse_str($_SERVER['QUERY_STRING'], $params);
    }
    
    if (isset($params['id']) && isset($params['mapping'])) {
        $id = (int) $params['id'];
        $mapping = trim($params['mapping']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing GET parameters']);
        exit();
    }
} else {
    // Handle POST/JSON input
    $raw_input = file_get_contents('php://input');
    $input = json_decode($raw_input, true);
    
    // Fallback to POST data if JSON failed
    if (!$input && !empty($_POST)) {
        $input = $_POST;
    }
    
    if (!$input || !isset($input['id']) || !isset($input['mapping'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid input data']);
        exit();
    }
    
    $id = (int) $input['id'];
    $mapping = trim($input['mapping']);
}

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit();
}

try {
    // Update the mapping field for the specified food record
    $sql = "UPDATE food SET mapping = ? WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('si', $mapping, $id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Mapping updated successfully',
                'id' => $id,
                'mapping' => $mapping
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No record found with the specified ID'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update mapping'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$stmt->close();
$db->close();
?>