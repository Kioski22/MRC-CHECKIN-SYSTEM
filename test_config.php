<?php
/**
 * Test Configuration for Food Release System
 * This file allows overriding system behavior for testing purposes
 * Now uses database storage with file fallback
 */

// Prevent multiple inclusions
if (!defined('TEST_CONFIG_LOADED')) {
    define('TEST_CONFIG_LOADED', true);

// Include database test config functions
include_once 'db_test_config_functions.php';

// File-based fallback configuration (used if database is not available)
$test_config_fallback = [
    // Set to true to enable test mode
    'test_mode_enabled' => false,
    
    // Override current date for testing (format: Y-m-d)
    // Set to null to use actual current date
    'test_date_override' => null, // Use actual current date when test mode is disabled
    
    // Available test dates
    'available_test_dates' => [
        '2025-10-15' => 'Pre-Convention (Day before)',
        '2025-10-16' => 'Convention Day 1',
        '2025-10-17' => 'Convention Day 2', 
        '2025-10-18' => 'Convention Day 3',
        '2025-10-19' => 'Post-Convention (Day after)'
    ],
    
    // Test mode messages
    'test_mode_banner' => false, // Show test mode banner
    'test_mode_message' => 'TEST MODE ACTIVE - Simulating date for testing purposes'
];

/**
 * Get test configuration - tries database first, then falls back to file config
 * @param string $key Configuration key
 * @param mixed $default Default value
 * @return mixed Configuration value
 */
function getTestConfigValue($key, $default = null) {
    global $test_config_fallback;
    
    // Try to get from database first
    $db_value = getTestConfigFromDB($key, null);
    if ($db_value !== null) {
        return $db_value;
    }
    
    // Fall back to file configuration
    if (isset($test_config_fallback[$key])) {
        return $test_config_fallback[$key];
    }
    
    return $default;
}

/**
 * Get all test configuration - combines database and fallback
 * @return array Complete test configuration
 */
function getTestConfig() {
    global $test_config_fallback;
    
    // Start with fallback config
    $config = $test_config_fallback;
    
    // Override with database values
    $db_config = getAllTestConfigFromDB();
    if (!empty($db_config)) {
        $config = array_merge($config, $db_config);
    }
    
    return $config;
}

/**
 * Get the current date for the system (respects test mode)
 */
if (!function_exists('getCurrentDate')) {
    function getCurrentDate() {
        $test_enabled = getTestConfigValue('test_mode_enabled', false);
        $test_date = getTestConfigValue('test_date_override', null);
        
        if ($test_enabled && $test_date) {
            return $test_date;
        }
        
        return date('Y-m-d');
    }
}

/**
 * Get current datetime for the system (respects test mode)
 */
if (!function_exists('getCurrentDateTime')) {
    function getCurrentDateTime() {
        // Ensure Asia/Manila timezone is set
        $original_timezone = date_default_timezone_get();
        date_default_timezone_set('Asia/Manila');
        
        $test_enabled = getTestConfigValue('test_mode_enabled', false);
        $test_date = getTestConfigValue('test_date_override', null);
        
        if ($test_enabled && $test_date) {
            // Use test date but current time in Asia/Manila timezone
            $current_time = date('H:i:s');
            $result = $test_date . ' ' . $current_time;
        } else {
            $result = date('Y-m-d H:i:s');
        }
        
        // Restore original timezone
        date_default_timezone_set($original_timezone);
        
        return $result;
    }
}

/**
 * Check if system is in test mode
 */
if (!function_exists('isTestMode')) {
    function isTestMode() {
        return getTestConfigValue('test_mode_enabled', false);
    }
}

/**
 * Check if current date is a convention day (respects test mode)
 */
if (!function_exists('isConventionDay')) {
    function isConventionDay($date = null) {
        if ($date === null) {
            $date = getCurrentDate();
        }
        
        $convention_dates = ['2025-10-16', '2025-10-17', '2025-10-18'];
        return in_array($date, $convention_dates);
    }
}

/**
 * Get which convention day it is (1, 2, 3) or false if not a convention day
 */
if (!function_exists('getConventionDay')) {
    function getConventionDay($date = null) {
        if ($date === null) {
            $date = getCurrentDate();
        }
        
        $convention_days = [
            '2025-10-16' => 1,
            '2025-10-17' => 2,
            '2025-10-18' => 3
        ];
        
        return isset($convention_days[$date]) ? $convention_days[$date] : false;
    }
}

/**
 * Display test mode banner if enabled
 */
if (!function_exists('displayTestModeBanner')) {
    function displayTestModeBanner() {
        $test_enabled = getTestConfigValue('test_mode_enabled', false);
        $show_banner = getTestConfigValue('test_mode_banner', false);
        $test_message = getTestConfigValue('test_mode_message', 'TEST MODE ACTIVE');
        
        if ($test_enabled && $show_banner) {
            $current_date = getCurrentDate();
            $actual_date = date('Y-m-d');
            
            echo '<div class="alert alert-warning alert-dismissible fade show test-mode-banner" role="alert">';
            echo '<strong><i class="fas fa-flask"></i> TEST MODE ACTIVE</strong><br>';
            echo htmlspecialchars($test_message) . '<br>';
            echo '<small>Actual Date: ' . $actual_date . ' | Simulated Date: ' . $current_date . '</small>';
            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
            echo '<span aria-hidden="true">&times;</span>';
            echo '</button>';
            echo '</div>';
        }
    }
}

} // End of TEST_CONFIG_LOADED check
?>