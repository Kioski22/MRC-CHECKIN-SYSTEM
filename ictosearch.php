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

// Check if 'search_term' is provided in the POST request
if (isset($_POST['search_term'])) {
    $searchTerm = $db->real_escape_string(trim($_POST['search_term']));

    // Prepare the SQL query to search by first_name, personal_email, or prc_license_number using LIKE
    $sql = "SELECT first_name, last_name, middle_name, personal_email, prc_license_number, name_suffix, chapter, qrcode_url
            FROM registrations 
            WHERE first_name LIKE ? OR personal_email LIKE ? OR prc_license_number LIKE ?";

    // Prepare the statement
    if ($stmt = $db->prepare($sql)) {
        // Append wildcards for partial matching
        $likeTerm = "%" . $searchTerm . "%";
        
        // Bind parameters and execute the query
        $stmt->bind_param("sss", $likeTerm, $likeTerm, $likeTerm);
        $stmt->execute();

        // Fetch the result
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Loop through and fetch data into an associative array
            while ($data = $result->fetch_assoc()) {
                $response['resultValue'][] = [
                    "firstname" => $data['first_name'],
                    "lastname" => $data['last_name'],
                    "middlename" => $data['middle_name'],
                    "email" => $data['personal_email'],
                    "prclicencenumber" => $data['prc_license_number'],
                    "chapter" => $data['chapter'],
                    "suffix" => $data['name_suffix'], // Assuming this is the correct field for suffix
                    "qrcode" => $data['qrcode_url'],
                ];
            }
            $response['resultKey'] = 1;  // Successful search
        } else {
            // No record found
            $response['errorCode'] = 1; // Changed to 1 for no records found
            $response['errorMsg'] = "No record found for the provided search term.";
        }

        // Close the statement
        $stmt->close();
    } else {
        // Database query preparation error
        $response['errorCode'] = 3;
        $response['errorMsg'] = "Failed to prepare the SQL query.";
    }
} else {
    // Missing 'search_term' parameter
    $response['errorCode'] = 1;
    $response['errorMsg'] = "Missing 'search_term' parameter.";
}

// Close the database connection
$db->close();

// Output the response in JSON format
echo json_encode($response);
