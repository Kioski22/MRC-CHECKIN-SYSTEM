<?php
session_start();
header('Content-Type: application/json');

// Check authentication
if (!isset($_SESSION['access_token'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_POST['qrcode'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'QR code is required']);
    exit();
}

$qrcode = $_POST['qrcode'];

try {
    // Get access token for external API
    $access_token = $_SESSION['access_token'];
    
    // External API endpoint for QR code lookup with query parameters
    $api_url = 'https://psme-api.smartpay.ph/public/index.php/api/getScanData?' . http_build_query([
        'scan' => $qrcode,
        'event_id' => 65
    ]);
    
    // Log the request for debugging
    error_log("getScanData request URL: " . $api_url);
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options for GET request
    curl_setopt_array($ch, [
        CURLOPT_URL => $api_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'PSME-ICTO/1.0'
    ]);
    
    // Execute the request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    // Check for cURL errors
    if ($curl_error) {
        throw new Exception('Network error: ' . $curl_error);
    }
    
    // Check HTTP response code
    if ($http_code !== 200) {
        throw new Exception('API request failed with HTTP code: ' . $http_code);
    }
    
    // Decode the JSON response
    $api_response = json_decode($response, true);
    
    // Log the response for debugging
    error_log("getScanData response: " . $response);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response from API');
    }
    
    // Check if the API response indicates success
    if (!isset($api_response['resultKey']) || $api_response['resultKey'] !== 1) {
        // Handle API-specific error responses
        $error_message = isset($api_response['errorMsg']) && !empty($api_response['errorMsg']) 
                        ? $api_response['errorMsg'] 
                        : 'QR code not found in external system';
        
        echo json_encode([
            'resultKey' => 0,
            'resultValue' => [],
            'errorCode' => isset($api_response['errorCode']) ? $api_response['errorCode'] : 1,
            'errorMsg' => $error_message
        ]);
        exit();
    }
    
    // Success - return the API response as-is
    echo json_encode($api_response);
    
} catch (Exception $e) {
    error_log("getScanData error: " . $e->getMessage());
    
    echo json_encode([
        'resultKey' => 0,
        'resultValue' => [],
        'errorCode' => 1,
        'errorMsg' => 'An error occurred while processing the QR code: ' . $e->getMessage()
    ]);
}
?>