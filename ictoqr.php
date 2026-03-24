<?php
header('Content-Type: application/json');
require('dbConnection.php');  // Include your database connection file

// Initialize response array
$response = [
    "resultKey" => 0,
    "resultValue" => [],
    "errorCode" => 0,
    "errorMsg" => ""
];

// Check if 'qrcode' is provided in the POST request
if (isset($_POST['qrcode'])) {
    $qrcode = $db->real_escape_string(trim($_POST['qrcode']));

    // Prepare the SQL query
    $sql = "SELECT first_name, last_name, middle_name, personal_email, prc_license_number, name_suffix, chapter, qrcode_url
            FROM registrations 
            WHERE qrcode = ?";

    // Prepare the statement
    if ($stmt = $db->prepare($sql)) {
        // Bind parameters and execute the query
        $stmt->bind_param("s", $qrcode);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Fetch data into an associative array
            $data = $result->fetch_assoc();

            // Set response for successful result
            $response['resultKey'] = 1;
            $response['resultValue'][] = [
                "firstname" => $data['first_name'],
                "lastname" => $data['last_name'],
                "middlename" => $data['middle_name'],
                "email" => $data['personal_email'],
                "prclicencenumber" => $data['prc_license_number'],
                "suffix" => $data['name_suffix'],
                "chapter" => $data['chapter'],
                "qrcode" => $data['qrcode_url']
            ];
        } else {
            // No record found
            $response['errorCode'] = 2;
            $response['errorMsg'] = "No record found for the provided QR code.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Database query preparation error
        $response['errorCode'] = 3;
        $response['errorMsg'] = "Failed to prepare the SQL query.";
    }
} else {
    // Missing 'qrcode' parameter
    $response['errorCode'] = 1;
    $response['errorMsg'] = "Missing 'qrcode' parameter.";
}

// Close the database connection
$db->close();

// Output the response in JSON format
echo json_encode($response);
