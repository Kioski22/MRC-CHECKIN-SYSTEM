<?php
// Database connection settings
$host = 'localhost';        // Database host (usually localhost)
$db_user = 'root'; // Database username
$db_password = ''; // Database password
$db_name = 'MRC';  // Database name

// Create a new MySQLi connection
$db = new mysqli($host, $db_user, $db_password, $db_name);

// Check the connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Optionally, you can set the character set to UTF-8
$db->set_charset("utf8mb4");

// Now you can use $conn for any database queries
?>