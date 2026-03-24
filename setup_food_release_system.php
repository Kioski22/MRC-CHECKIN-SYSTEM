<?php
// Script to create food_releases table and views
include 'dbConnection.php';

echo "<h1>Creating Food Release System Database Schema</h1>\n";

try {
    $success_count = 0;
    $error_count = 0;
    
    // Define SQL statements directly (avoiding file parsing issues)
    $sql_statements = [
        // Create food_releases table
        "CREATE TABLE IF NOT EXISTS food_releases (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        // Create food_releases_view
        "CREATE OR REPLACE VIEW food_releases_view AS
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
        JOIN users u ON fr.released_by = u.user_id",
        
        // Create food_release_status view
        "CREATE OR REPLACE VIEW food_release_status AS
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
    ];
    
    foreach ($sql_statements as $index => $statement) {
        try {
            $result = $db->query($statement);
            if ($result) {
                $statement_name = '';
                if (strpos($statement, 'CREATE TABLE') !== false) {
                    $statement_name = 'food_releases table';
                } elseif (strpos($statement, 'food_releases_view') !== false) {
                    $statement_name = 'food_releases_view';
                } elseif (strpos($statement, 'food_release_status') !== false) {
                    $statement_name = 'food_release_status view';
                }
                
                echo "<p style='color: green;'>✓ Created: $statement_name</p>\n";
                $success_count++;
            } else {
                echo "<p style='color: red;'>✗ Failed: Statement " . ($index + 1) . " - Error: " . $db->error . "</p>\n";
                $error_count++;
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Exception: Statement " . ($index + 1) . " - Error: " . $e->getMessage() . "</p>\n";
            $error_count++;
        }
    }
    
    // Test the views
    echo "<hr>\n";
    echo "<h2>Testing Database Objects</h2>\n";
    
    // Test table exists
    $result = $db->query("SHOW TABLES LIKE 'food_releases'");
    if ($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ food_releases table exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ food_releases table not found</p>\n";
    }
    
    // Test views exist
    $result = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $views = [];
    if ($result) {
        while ($row = $result->fetch_array()) {
            $views[] = $row[0];
        }
    }
    
    if (in_array('food_releases_view', $views)) {
        echo "<p style='color: green;'>✓ food_releases_view exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ food_releases_view not found</p>\n";
    }
    
    if (in_array('food_release_status', $views)) {
        echo "<p style='color: green;'>✓ food_release_status view exists</p>\n";
    } else {
        echo "<p style='color: red;'>✗ food_release_status view not found</p>\n";
    }
    
    // Test sample query
    try {
        $result = $db->query("SELECT COUNT(*) as count FROM food_release_status");
        if ($result) {
            $count = $result->fetch_assoc()['count'];
            echo "<p style='color: green;'>✓ food_release_status view working - $count registrants found</p>\n";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error testing food_release_status view: " . $e->getMessage() . "</p>\n";
    }
    
    echo "<hr>\n";
    echo "<h2>Summary</h2>\n";
    echo "<p>Successful statements: <strong>$success_count</strong></p>\n";
    echo "<p>Failed statements: <strong>$error_count</strong></p>\n";
    
    if ($error_count === 0) {
        echo "<p style='color: green; font-weight: bold;'>🎉 Food release system schema created successfully!</p>\n";
        echo "<p>You can now:</p>\n";
        echo "<ul>\n";
        echo "<li>Track food releases per day (Oct 16-18, 2025)</li>\n";
        echo "<li>Prevent duplicate releases to same registrant on same day</li>\n";
        echo "<li>View release history and status</li>\n";
        echo "<li>Generate reports on food distribution</li>\n";
        echo "</ul>\n";
        
        echo "<h3>Next Steps:</h3>\n";
        echo "<p>1. Visit <a href='list_food'>Food Registration Management</a> to test the new features</p>\n";
        echo "<p>2. The dashboard will show release metrics during convention period (Oct 16-18)</p>\n";
        echo "<p>3. Release buttons will appear in the Actions column when eligible</p>\n";
        
    } else {
        echo "<p style='color: orange; font-weight: bold;'>⚠️ Some statements failed. Please check the errors above.</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal error: " . $e->getMessage() . "</p>\n";
}

$db->close();
?>