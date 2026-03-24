<?php
/**
 * Database Test Configuration Functions
 * Functions to read and write test configuration from/to database
 */

// Prevent multiple inclusions
if (!defined('DB_TEST_CONFIG_LOADED')) {
    define('DB_TEST_CONFIG_LOADED', true);

/**
 * Get test configuration value from database
 * @param string $key Configuration key
 * @param mixed $default Default value if key not found
 * @return mixed Configuration value or default
 */
function getTestConfigFromDB($key, $default = null) {
    global $db;
    
    try {
        // Include database connection if not already included
        if (!isset($db)) {
            include_once 'dbConnection.php';
        }
        
        if (!$db) {
            error_log("Database connection not available for test config");
            return $default;
        }
        
        $key_escaped = $db->real_escape_string($key);
        $query = "SELECT config_value FROM test_config WHERE config_key = '$key_escaped'";
        $result = $db->query($query);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $value = $row['config_value'];
            
            // Handle NULL values
            if ($value === null) {
                return null;
            }
            
            // Try to decode JSON if it looks like JSON
            if (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }
            
            // Convert string booleans to actual booleans
            if ($value === 'true') return true;
            if ($value === 'false') return false;
            
            return $value;
        }
        
        return $default;
        
    } catch (Exception $e) {
        error_log("Error getting test config '$key': " . $e->getMessage());
        return $default;
    }
}

/**
 * Set test configuration value in database
 * @param string $key Configuration key
 * @param mixed $value Configuration value
 * @param string $description Optional description
 * @return bool Success status
 */
function setTestConfigInDB($key, $value, $description = null) {
    global $db;
    
    try {
        // Include database connection if not already included
        if (!isset($db)) {
            include_once 'dbConnection.php';
        }
        
        if (!$db) {
            error_log("Database connection not available for test config");
            return false;
        }
        
        $key_escaped = $db->real_escape_string($key);
        
        // Convert value to string for storage
        if ($value === null) {
            $value_sql = 'NULL';
        } elseif (is_bool($value)) {
            $value_sql = "'" . ($value ? 'true' : 'false') . "'";
        } elseif (is_array($value) || is_object($value)) {
            $value_sql = "'" . $db->real_escape_string(json_encode($value)) . "'";
        } else {
            $value_sql = "'" . $db->real_escape_string($value) . "'";
        }
        
        // Build query
        if ($description !== null) {
            $description_escaped = $db->real_escape_string($description);
            $query = "
                INSERT INTO test_config (config_key, config_value, description) 
                VALUES ('$key_escaped', $value_sql, '$description_escaped')
                ON DUPLICATE KEY UPDATE 
                    config_value = VALUES(config_value),
                    description = VALUES(description),
                    updated_at = CURRENT_TIMESTAMP
            ";
        } else {
            $query = "
                INSERT INTO test_config (config_key, config_value) 
                VALUES ('$key_escaped', $value_sql)
                ON DUPLICATE KEY UPDATE 
                    config_value = VALUES(config_value),
                    updated_at = CURRENT_TIMESTAMP
            ";
        }
        
        $result = $db->query($query);
        
        if (!$result) {
            error_log("Error setting test config '$key': " . $db->error);
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error setting test config '$key': " . $e->getMessage());
        return false;
    }
}

/**
 * Get all test configuration from database
 * @return array All configuration as key-value pairs
 */
function getAllTestConfigFromDB() {
    global $db;
    
    try {
        // Include database connection if not already included
        if (!isset($db)) {
            include_once 'dbConnection.php';
        }
        
        if (!$db) {
            error_log("Database connection not available for test config");
            return [];
        }
        
        $query = "SELECT config_key, config_value FROM test_config";
        $result = $db->query($query);
        
        $config = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $key = $row['config_key'];
                $value = $row['config_value'];
                
                // Handle NULL values
                if ($value === null) {
                    $config[$key] = null;
                    continue;
                }
                
                // Try to decode JSON if it looks like JSON
                if (is_string($value) && (strpos($value, '{') === 0 || strpos($value, '[') === 0)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $config[$key] = $decoded;
                        continue;
                    }
                }
                
                // Convert string booleans to actual booleans
                if ($value === 'true') {
                    $config[$key] = true;
                } elseif ($value === 'false') {
                    $config[$key] = false;
                } else {
                    $config[$key] = $value;
                }
            }
        }
        
        return $config;
        
    } catch (Exception $e) {
        error_log("Error getting all test config: " . $e->getMessage());
        return [];
    }
}

/**
 * Delete test configuration key from database
 * @param string $key Configuration key to delete
 * @return bool Success status
 */
function deleteTestConfigFromDB($key) {
    global $db;
    
    try {
        // Include database connection if not already included
        if (!isset($db)) {
            include_once 'dbConnection.php';
        }
        
        if (!$db) {
            error_log("Database connection not available for test config");
            return false;
        }
        
        $key_escaped = $db->real_escape_string($key);
        $query = "DELETE FROM test_config WHERE config_key = '$key_escaped'";
        
        $result = $db->query($query);
        
        if (!$result) {
            error_log("Error deleting test config '$key': " . $db->error);
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error deleting test config '$key': " . $e->getMessage());
        return false;
    }
}

} // End of DB_TEST_CONFIG_LOADED check
?>