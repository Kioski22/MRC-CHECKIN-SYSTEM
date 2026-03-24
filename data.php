<?php
require("dbConnection.php");

// Set headers for JSON response
header("Content-Type: application/json");

// Check database connection
if ($db->connect_error) {
    $response = [
        "error" => "Connection failed: " . $db->connect_error
    ];
    echo json_encode($response);
    exit();
}

// Function to fetch data based on the query
function fetchData($db, $query) {
    $result = $db->query($query);
    return ($result) ? $result->fetch_assoc()['count'] : 0;
}

// Fetch total released kits and total checked-ins
$totalReleasedKit = fetchData($db, "SELECT COUNT(*) AS count FROM `checkins` WHERE kit_release_time IS NOT NULL;");
$totalCheckedIn = fetchData($db, "SELECT COUNT(*) AS count FROM `checkins` WHERE checkin_time IS NOT NULL;");

// Prepare data to return
$data = [
    'checkedins' => [
        'value' => $totalCheckedIn,
        'color' => 'primary',
        'icon' => 'user-check',
        'label' => 'Total Checked In'
    ],
    'releasedkits' => [
        'value' => $totalReleasedKit,
        'color' => 'danger',
        'icon' => 'shopping-bag',
        'label' => 'Total Released Kit'
    ]
];

// Output the data as a JSON object
echo json_encode($data);

// Close the database connection
$db->close();
