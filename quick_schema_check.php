<?php
// Quick Database Schema Checker
require 'dbConnection.php';

// Check if food table exists and get its structure
$tables = ['food', 'checkins', 'users'];

foreach ($tables as $table) {
    echo "=== {$table} TABLE ===\n";
    
    $result = $db->query("DESCRIBE {$table}");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']} - {$row['Extra']}\n";
        }
    } else {
        echo "Table {$table} does not exist or error: " . $db->error . "\n";
    }
    
    // Get count
    $count_result = $db->query("SELECT COUNT(*) as cnt FROM {$table}");
    if ($count_result) {
        $count = $count_result->fetch_assoc()['cnt'];
        echo "Total records: {$count}\n";
    }
    echo "\n";
}

$db->close();
?>