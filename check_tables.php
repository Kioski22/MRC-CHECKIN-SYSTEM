<?php
// Check existing table structures before creating foreign keys
include 'dbConnection.php';

echo "=== CHECKING EXISTING TABLE STRUCTURES ===\n\n";

// Check food table structure
echo "FOOD TABLE STRUCTURE:\n";
$result = $db->query("DESCRIBE food");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']} {$row['Extra']}\n";
    }
} else {
    echo "Error: " . $db->error . "\n";
}

echo "\n";

// Check users table structure
echo "USERS TABLE STRUCTURE:\n";
$result = $db->query("DESCRIBE users");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']} {$row['Extra']}\n";
    }
} else {
    echo "Error: " . $db->error . "\n";
}

echo "\n";

// Check if food_releases table already exists
echo "CHECKING EXISTING food_releases TABLE:\n";
$result = $db->query("SHOW TABLES LIKE 'food_releases'");
if ($result && $result->num_rows > 0) {
    echo "food_releases table already exists!\n";
    $result = $db->query("DESCRIBE food_releases");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']} {$row['Null']} {$row['Key']} {$row['Default']} {$row['Extra']}\n";
        }
    }
} else {
    echo "food_releases table does not exist.\n";
}

echo "\n";

// Check MySQL version and engine support
echo "MYSQL INFO:\n";
$result = $db->query("SELECT VERSION() as version");
if ($result) {
    $version = $result->fetch_assoc()['version'];
    echo "MySQL Version: $version\n";
}

$result = $db->query("SHOW ENGINES");
echo "Available Storage Engines:\n";
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['Engine'] == 'InnoDB') {
            echo "- InnoDB: {$row['Support']} (Comment: {$row['Comment']})\n";
        }
    }
}

echo "\n";

// Check foreign key constraints on existing tables
echo "EXISTING FOREIGN KEY CONSTRAINTS:\n";
$result = $db->query("
    SELECT 
        TABLE_NAME,
        COLUMN_NAME,
        CONSTRAINT_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE REFERENCED_TABLE_SCHEMA = 'psmenow_natcon'
    AND REFERENCED_TABLE_NAME IS NOT NULL
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['TABLE_NAME']}.{$row['COLUMN_NAME']} -> {$row['REFERENCED_TABLE_NAME']}.{$row['REFERENCED_COLUMN_NAME']}\n";
    }
} else {
    echo "No existing foreign key constraints found.\n";
}

$db->close();
?>