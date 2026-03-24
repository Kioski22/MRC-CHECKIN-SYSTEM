<?php
// Database Schema Scanner
// This script scans the database and outputs the complete schema information

require 'dbConnection.php';

header('Content-Type: text/plain');

echo "=== DATABASE SCHEMA REFERENCE ===\n";
echo "Generated on: " . date('Y-m-d H:i:s') . "\n";
echo "Database: {$db_name}\n";
echo "Host: {$host}\n\n";

// Get all tables
$tables_query = "SHOW TABLES";
$tables_result = $db->query($tables_query);

if ($tables_result) {
    $tables = [];
    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }
    
    echo "=== TABLES FOUND ===\n";
    foreach ($tables as $table) {
        echo "- {$table}\n";
    }
    echo "\n";
    
    // For each table, get detailed information
    foreach ($tables as $table) {
        echo "=== TABLE: {$table} ===\n";
        
        // Get column information
        $columns_query = "DESCRIBE {$table}";
        $columns_result = $db->query($columns_query);
        
        if ($columns_result) {
            echo "COLUMNS:\n";
            printf("%-20s %-20s %-10s %-10s %-15s %-10s\n", 
                   "Field", "Type", "Null", "Key", "Default", "Extra");
            echo str_repeat("-", 95) . "\n";
            
            while ($column = $columns_result->fetch_assoc()) {
                printf("%-20s %-20s %-10s %-10s %-15s %-10s\n",
                       $column['Field'],
                       $column['Type'],
                       $column['Null'],
                       $column['Key'],
                       $column['Default'] ?: 'NULL',
                       $column['Extra']
                );
            }
        }
        
        // Get row count
        $count_query = "SELECT COUNT(*) as count FROM {$table}";
        $count_result = $db->query($count_query);
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo "\nROW COUNT: {$count}\n";
        }
        
        // Get indexes
        $indexes_query = "SHOW INDEX FROM {$table}";
        $indexes_result = $db->query($indexes_query);
        
        if ($indexes_result) {
            echo "\nINDEXES:\n";
            printf("%-15s %-15s %-15s %-10s %-10s\n", 
                   "Key_name", "Column_name", "Index_type", "Unique", "Null");
            echo str_repeat("-", 75) . "\n";
            
            while ($index = $indexes_result->fetch_assoc()) {
                printf("%-15s %-15s %-15s %-10s %-10s\n",
                       $index['Key_name'],
                       $index['Column_name'],
                       $index['Index_type'],
                       $index['Non_unique'] == 0 ? 'YES' : 'NO',
                       $index['Null']
                );
            }
        }
        
        // Get foreign keys
        $fk_query = "SELECT 
            CONSTRAINT_NAME,
            COLUMN_NAME,
            REFERENCED_TABLE_NAME,
            REFERENCED_COLUMN_NAME
        FROM information_schema.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = '{$db_name}' 
        AND TABLE_NAME = '{$table}' 
        AND REFERENCED_TABLE_NAME IS NOT NULL";
        
        $fk_result = $db->query($fk_query);
        
        if ($fk_result && $fk_result->num_rows > 0) {
            echo "\nFOREIGN KEYS:\n";
            printf("%-20s %-15s %-20s %-15s\n", 
                   "Constraint", "Column", "Referenced Table", "Referenced Column");
            echo str_repeat("-", 80) . "\n";
            
            while ($fk = $fk_result->fetch_assoc()) {
                printf("%-20s %-15s %-20s %-15s\n",
                       $fk['CONSTRAINT_NAME'],
                       $fk['COLUMN_NAME'],
                       $fk['REFERENCED_TABLE_NAME'],
                       $fk['REFERENCED_COLUMN_NAME']
                );
            }
        }
        
        // Sample data (first 3 rows)
        $sample_query = "SELECT * FROM {$table} LIMIT 3";
        $sample_result = $db->query($sample_query);
        
        if ($sample_result && $sample_result->num_rows > 0) {
            echo "\nSAMPLE DATA (first 3 rows):\n";
            $first_row = true;
            while ($row = $sample_result->fetch_assoc()) {
                if ($first_row) {
                    // Print headers
                    echo implode(" | ", array_keys($row)) . "\n";
                    echo str_repeat("-", strlen(implode(" | ", array_keys($row)))) . "\n";
                    $first_row = false;
                }
                // Print values, truncate long strings
                $values = array_map(function($val) {
                    if ($val === null) return 'NULL';
                    return strlen($val) > 20 ? substr($val, 0, 20) . '...' : $val;
                }, array_values($row));
                echo implode(" | ", $values) . "\n";
            }
        }
        
        echo "\n" . str_repeat("=", 80) . "\n\n";
    }
} else {
    echo "Error getting tables: " . $db->error . "\n";
}

$db->close();
?>