<?php
require("dbConnection.php");

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');

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

// Fetch total food registrations and total unmapped
$totalFoodRegistration = fetchData($db, "SELECT COUNT(*) AS count FROM `food`;");
$totalUnmapped = fetchData($db, "SELECT COUNT(*) AS count FROM `food` WHERE mapping IS NULL OR mapping = '';");

// Fetch food release statistics
$currentDate = date('Y-m-d');
$totalReleasesToday = fetchData($db, "SELECT COUNT(*) AS count FROM `food_releases` WHERE release_date = '$currentDate';");
$eligibleForReleaseToday = fetchData($db, "
    SELECT COUNT(*) AS count 
    FROM `food` f 
    LEFT JOIN `food_releases` fr ON f.id = fr.food_id AND fr.release_date = '$currentDate' 
    WHERE fr.id IS NULL
;");

// Get release statistics for each convention day
$totalReleasesDay1 = fetchData($db, "SELECT COUNT(*) AS count FROM `food_releases` WHERE release_date = '2025-10-16';");
$totalReleasesDay2 = fetchData($db, "SELECT COUNT(*) AS count FROM `food_releases` WHERE release_date = '2025-10-17';");
$totalReleasesDay3 = fetchData($db, "SELECT COUNT(*) AS count FROM `food_releases` WHERE release_date = '2025-10-18';");
$totalAllReleases = $totalReleasesDay1 + $totalReleasesDay2 + $totalReleasesDay3;

// Determine which cards to show based on current date
$conventionDates = ['2025-10-16', '2025-10-17', '2025-10-18'];
$isConventionPeriod = in_array($currentDate, $conventionDates);

// Prepare data to return - always show 4 cards
$data = [
    'totalfood' => [
        'value' => $totalFoodRegistration,
        'color' => 'primary',
        'icon' => 'users',
        'label' => 'Total Food Registration'
    ],
    'unmapped' => [
        'value' => $totalUnmapped,
        'color' => 'warning',
        'icon' => 'alert-circle',
        'label' => 'Unmapped Registrations'
    ],
    'releasedtoday' => [
        'value' => $totalReleasesToday,
        'color' => 'success',
        'icon' => 'check-circle',
        'label' => 'Released Food Today'
    ],
    'pendingtoday' => [
        'value' => $eligibleForReleaseToday,
        'color' => 'info',
        'icon' => 'clock',
        'label' => 'Pending Claims Today'
    ]
];

// Add release breakdown for detailed view (always available)
$data['release_breakdown'] = [
    'day1' => $totalReleasesDay1,
    'day2' => $totalReleasesDay2,
    'day3' => $totalReleasesDay3,
    'total' => $totalAllReleases,
    'current_date' => $currentDate,
    'is_convention_period' => $isConventionPeriod
];

// Output the data as a JSON object
echo json_encode($data);

// Close the database connection
$db->close();