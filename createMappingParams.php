<?php
header('Content-Type: application/json');

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['mapping'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

// Create temporary parameters file
$params_file = 'temp_mapping_params.json';
$success = file_put_contents($params_file, json_encode($input));

if ($success !== false) {
    echo json_encode(['success' => true, 'message' => 'Parameters file created']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create parameters file']);
}
?>