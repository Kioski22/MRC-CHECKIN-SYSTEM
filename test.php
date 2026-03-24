<?php
// URL of the submit_registration.php script
$url = 'http://localhost/natcon2/submit_registration.php'; // Update the path to match your environment

// Prepare test data (mimicking form data)
$data = array(
    'membership_type' => '3', // Regular Member
    'first_name' => 'John',
    'middle_name' => 'Doe',
    'middle_name_na' => false,
    'last_name' => 'Smith',
    'name_suffix' => 'Jr.',
    'personal_email' => 'john.doe@example.com',
    'mobile_number' => '+639171234567',
    'birthday' => '1985-10-25',
    
    // Other details checkboxes
    'other_details' => array('New Board Passer'),
    'free' => false,
    'freeReason' => '',
    
    // Professional details
    'prc_type' => 'registered_mechanical_engineer',
    'prc_license_number' => '123456',
    'prc_expiration_date' => '2025-05-12',
    'has_prc_license' => true,
    'sequence_number' => '',
    'region' => 'Luzon',
    'chapter' => 'Some Chapter',
    
    // Payment details
    'payment_method' => 'gcash',
    'price_detail' => '₱3,500.00', // Simulated registration fee
    'payment_confirmation' => true
);

// Initialize cURL
$ch = curl_init($url);

// Convert data to URL-encoded query string format
$postData = http_build_query($data);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/x-www-form-urlencoded'
));

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    // Display the response
    echo 'Response from submit_registration.php: ';
    var_dump($response);
}

// Close cURL
curl_close($ch);
