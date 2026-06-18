<?php
include 'dbConnection.php';

echo "<h3>Database Diagnostics</h3>";

// 1. Check connection info
echo "Connected to database: " . $db->query("SELECT DATABASE()")->fetch_row()[0] . "<br>";
echo "MySQL user: " . $db->query("SELECT CURRENT_USER()")->fetch_row()[0] . "<br>";

// 2. Test fetching 'admin' using normal query
$query = "SELECT * FROM users WHERE username = 'admin'";
$result = $db->query($query);
if (!$result) {
    echo "Query failed: " . $db->error . "<br>";
} else {
    $user = $result->fetch_assoc();
    if ($user) {
        echo "<br><b>User 'admin' found via direct query!</b><br>";
        echo "ID: " . $user['user_id'] . "<br>";
        echo "Username: " . $user['username'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
    } else {
        echo "<br><b>User 'admin' NOT found via direct query.</b><br>";
    }
}

// 3. Test fetching 'admin' using prepared statement (as loginScript.php does)
echo "<br><b>Testing Prepared Statement...</b><br>";
$stmt = $db->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
if (!$stmt) {
    echo "Prepare failed: " . $db->error . "<br>";
} else {
    $adminStr = 'admin';
    $stmt->bind_param("ss", $adminStr, $adminStr);
    $stmt->execute();
    
    // Check if get_result works
    $res = $stmt->get_result();
    if ($res === false) {
        echo "get_result() returned false. Error: " . $stmt->error . " (This usually means the mysqlnd driver is missing in PHP).<br>";
    } else {
        $userPrep = $res->fetch_assoc();
        if ($userPrep) {
            echo "User found via prepared statement!<br>";
        } else {
            echo "Prepared statement succeeded but returned 0 rows.<br>";
        }
    }
    $stmt->close();
}

// 4. Dump all users to see what's actually in the table
echo "<br><b>Dumping all usernames in the current 'users' table:</b><br>";
$all = $db->query("SELECT user_id, username, email FROM users LIMIT 10");
if ($all && $all->num_rows > 0) {
    while ($row = $all->fetch_assoc()) {
        echo "ID: " . $row['user_id'] . " | Username: '" . $row['username'] . "' | Email: '" . $row['email'] . "'<br>";
    }
} else {
    echo "The users table is completely empty or query failed: " . $db->error . "<br>";
}
?>
