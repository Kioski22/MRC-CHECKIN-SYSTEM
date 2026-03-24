<?php
// Test fetchFoodData endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== TESTING fetchFoodData ENDPOINT ===\n\n";

include 'dbConnection.php';

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "Database connected successfully.\n\n";

// Test the food_release_status view
echo "Testing food_release_status view:\n";
try {
    $result = $db->query("SELECT COUNT(*) as count FROM food_release_status LIMIT 1");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "✓ food_release_status view accessible ($count records)\n";
    } else {
        echo "✗ Error querying food_release_status: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception querying food_release_status: " . $e->getMessage() . "\n";
}

// Test a sample query from fetchFoodData
echo "\nTesting sample fetchFoodData query:\n";
try {
    $sql = "SELECT 
                frs.id,
                frs.email_address,
                frs.first_name,
                frs.middle_name,
                frs.last_name,
                frs.suffix,
                frs.mapping,
                frs.day1_released,
                frs.day2_released,
                frs.day3_released,
                frs.total_releases,
                frs.can_release_today
            FROM food_release_status frs 
            ORDER BY frs.id ASC 
            LIMIT 5";
    
    $result = $db->query($sql);
    if ($result) {
        echo "✓ Sample query successful\n";
        echo "Columns returned: " . $result->field_count . "\n";
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "Sample row data:\n";
            foreach ($row as $key => $value) {
                echo "  $key: " . ($value ?? 'NULL') . "\n";
            }
        }
    } else {
        echo "✗ Sample query failed: " . $db->error . "\n";
    }
} catch (Exception $e) {
    echo "✗ Exception in sample query: " . $e->getMessage() . "\n";
}

// Test the actual fetchFoodData.php output
echo "\n=== TESTING ACTUAL fetchFoodData.php ===\n";

// Simulate POST request
$_POST = [
    'draw' => 1,
    'start' => 0,
    'length' => 10,
    'search' => ['value' => ''],
    'order' => [['column' => 0, 'dir' => 'asc']]
];

ob_start();
try {
    // Start session for the included file
    session_start();
    $_SESSION['access_token'] = 'test_token'; // Set a dummy token
    
    include 'fetchFoodData.php';
    $output = ob_get_contents();
} catch (Exception $e) {
    $output = "Exception: " . $e->getMessage();
} catch (ParseError $e) {
    $output = "Parse Error: " . $e->getMessage();
} catch (Error $e) {
    $output = "Fatal Error: " . $e->getMessage();
}
ob_end_clean();

echo "fetchFoodData.php output:\n";
echo "Length: " . strlen($output) . " characters\n";
echo "Content:\n";
echo $output . "\n";

// Test if it's valid JSON
if (!empty($output)) {
    $decoded = json_decode($output, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "\n✓ Output is valid JSON\n";
        echo "Keys: " . implode(', ', array_keys($decoded)) . "\n";
    } else {
        echo "\n✗ Invalid JSON: " . json_last_error_msg() . "\n";
    }
}

$db->close();
?>