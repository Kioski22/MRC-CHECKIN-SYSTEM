<?php
include 'test_config.php';

echo "<h2>Test Mode Status Check - Database Version</h2>";
echo "<p><strong>Test Mode Enabled:</strong> " . (isTestMode() ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Current Date:</strong> " . getCurrentDate() . "</p>";
echo "<p><strong>Actual Date:</strong> " . date('Y-m-d') . "</p>";
echo "<p><strong>Is Convention Day:</strong> " . (isConventionDay() ? 'YES' : 'NO') . "</p>";

// Show database configuration
echo "<h3>Database Configuration:</h3>";
$db_config = getAllTestConfigFromDB();
if (!empty($db_config)) {
    echo "<pre>";
    print_r($db_config);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>No database configuration found - using file fallback</p>";
}

// Show combined configuration (what the system actually uses)
echo "<h3>Combined Configuration (Database + File Fallback):</h3>";
echo "<pre>";
print_r(getTestConfig());
echo "</pre>";

// Test individual config values
echo "<h3>Individual Config Value Tests:</h3>";
echo "<p><strong>test_mode_enabled:</strong> " . var_export(getTestConfigValue('test_mode_enabled'), true) . "</p>";
echo "<p><strong>test_date_override:</strong> " . var_export(getTestConfigValue('test_date_override'), true) . "</p>";
echo "<p><strong>test_mode_banner:</strong> " . var_export(getTestConfigValue('test_mode_banner'), true) . "</p>";

// Database connection test
echo "<h3>Database Connection Test:</h3>";
try {
    include 'dbConnection.php';
    if ($db) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
        
        // Test if table exists
        $result = $db->query("SHOW TABLES LIKE 'test_config'");
        if ($result && $result->num_rows > 0) {
            echo "<p style='color: green;'>✓ test_config table exists</p>";
            
            $count_result = $db->query("SELECT COUNT(*) as count FROM test_config");
            if ($count_result) {
                $count = $count_result->fetch_assoc()['count'];
                echo "<p style='color: green;'>✓ test_config table has $count records</p>";
            }
        } else {
            echo "<p style='color: red;'>✗ test_config table does not exist</p>";
            echo "<p><a href='setup_test_config_db.php'>Run Database Setup</a></p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>