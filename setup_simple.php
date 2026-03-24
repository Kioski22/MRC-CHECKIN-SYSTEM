<?php
// Simple script to create food_releases system - run this directly
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Setting up Food Release System...\n\n";

include 'dbConnection.php';

if (!$db) {
    die("Database connection failed: " . mysqli_connect_error() . "\n");
}

echo "Database connected successfully.\n";

$statements = [
    // 1. Create food_releases table
    [
        'name' => 'food_releases table',
        'sql' => "CREATE TABLE IF NOT EXISTS food_releases (
            id INT(11) NOT NULL AUTO_INCREMENT,
            food_id INT(11) NOT NULL,
            release_date DATE NOT NULL,
            release_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            released_by INT(11) NOT NULL,
            notes TEXT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (food_id) REFERENCES food(id) ON DELETE CASCADE,
            FOREIGN KEY (released_by) REFERENCES users(user_id) ON DELETE RESTRICT,
            UNIQUE KEY unique_daily_release (food_id, release_date),
            INDEX idx_release_date (release_date),
            INDEX idx_food_id (food_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ],
    
    // 2. Create food_releases_view
    [
        'name' => 'food_releases_view',
        'sql' => "CREATE OR REPLACE VIEW food_releases_view AS
        SELECT 
            fr.id as release_id,
            fr.food_id,
            f.email_address,
            f.first_name,
            f.middle_name,
            f.last_name,
            f.suffix,
            f.mapping,
            fr.release_date,
            fr.release_time,
            fr.released_by,
            u.username as released_by_username,
            fr.notes,
            CASE 
                WHEN fr.release_date = '2025-10-16' THEN 'Day 1'
                WHEN fr.release_date = '2025-10-17' THEN 'Day 2'
                WHEN fr.release_date = '2025-10-18' THEN 'Day 3'
                ELSE 'Unknown'
            END as convention_day
        FROM food_releases fr
        JOIN food f ON fr.food_id = f.id
        JOIN users u ON fr.released_by = u.user_id"
    ],
    
    // 3. Create food_release_status view
    [
        'name' => 'food_release_status view',
        'sql' => "CREATE OR REPLACE VIEW food_release_status AS
        SELECT 
            f.id,
            f.email_address,
            f.first_name,
            f.middle_name,
            f.last_name,
            f.suffix,
            f.mapping,
            MAX(CASE WHEN fr.release_date = '2025-10-16' THEN fr.release_time END) as day1_released,
            MAX(CASE WHEN fr.release_date = '2025-10-17' THEN fr.release_time END) as day2_released,
            MAX(CASE WHEN fr.release_date = '2025-10-18' THEN fr.release_time END) as day3_released,
            COUNT(fr.id) as total_releases,
            CASE 
                WHEN CURDATE() = '2025-10-16' AND MAX(CASE WHEN fr.release_date = '2025-10-16' THEN 1 END) IS NULL THEN 1
                WHEN CURDATE() = '2025-10-17' AND MAX(CASE WHEN fr.release_date = '2025-10-17' THEN 1 END) IS NULL THEN 1
                WHEN CURDATE() = '2025-10-18' AND MAX(CASE WHEN fr.release_date = '2025-10-18' THEN 1 END) IS NULL THEN 1
                ELSE 0
            END as can_release_today
        FROM food f
        LEFT JOIN food_releases fr ON f.id = fr.food_id
        GROUP BY f.id, f.email_address, f.first_name, f.middle_name, f.last_name, f.suffix, f.mapping"
    ]
];

$success = 0;
$errors = 0;

foreach ($statements as $stmt) {
    echo "Creating {$stmt['name']}... ";
    
    if ($db->query($stmt['sql'])) {
        echo "SUCCESS\n";
        $success++;
    } else {
        echo "FAILED: " . $db->error . "\n";
        $errors++;
    }
}

echo "\n=== VERIFICATION ===\n";

// Check table exists
$result = $db->query("SHOW TABLES LIKE 'food_releases'");
echo "food_releases table: " . ($result && $result->num_rows > 0 ? "EXISTS" : "NOT FOUND") . "\n";

// Check views exist
$result = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
$views = [];
if ($result) {
    while ($row = $result->fetch_array()) {
        $views[] = $row[0];
    }
}

echo "food_releases_view: " . (in_array('food_releases_view', $views) ? "EXISTS" : "NOT FOUND") . "\n";
echo "food_release_status view: " . (in_array('food_release_status', $views) ? "EXISTS" : "NOT FOUND") . "\n";

// Test query
try {
    $result = $db->query("SELECT COUNT(*) as count FROM food_release_status");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Test query: SUCCESS ($count registrants found)\n";
    }
} catch (Exception $e) {
    echo "Test query: FAILED - " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "Success: $success\n";
echo "Errors: $errors\n";

if ($errors == 0) {
    echo "\n🎉 SETUP COMPLETE! Food release system is ready.\n";
    echo "Next: Visit list_food.php to see the new features.\n";
} else {
    echo "\n❌ SETUP INCOMPLETE. Please check errors above.\n";
}

$db->close();
?>