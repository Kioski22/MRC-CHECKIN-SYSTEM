<?php
include 'dbConnection.php';

// get table schema
$res = $db->query("DESCRIBE users");
if (!$res) {
    echo "Error describing users: " . $db->error . "\n";
} else {
    $schema = [];
    while ($row = $res->fetch_assoc()) {
        $schema[] = $row;
    }
    echo "Schema:\n";
    print_r($schema);
}

// get users
$res2 = $db->query("SELECT * FROM users LIMIT 5");
if (!$res2) {
    echo "Error querying users: " . $db->error . "\n";
} else {
    $users = [];
    while ($row = $res2->fetch_assoc()) {
        $users[] = $row;
    }
    echo "\nUsers:\n";
    print_r($users);
}
?>
