<?php
include 'dbConnection.php';

$username = 'admin';
$newPassword = '091902rANDS@'; // The new password to set
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$usernameEscaped = $db->real_escape_string($username);

// Check if user exists using SELECT * so we don't hardcode user_id vs users_id
$query = "SELECT * FROM users WHERE username = '$usernameEscaped'";
$result = $db->query($query);

if (!$result) {
    die("Database query failed: " . $db->error . "<br>This means the database connection is working, but the query itself failed.");
}

if ($result->num_rows === 0) {
    echo "User '$username' does not exist in the database.";
} else {
    $userRow = $result->fetch_assoc();
    
    // Determine if we should update 'password_hash' or 'password' based on the column that exists
    $passwordColumn = isset($userRow['password_hash']) ? 'password_hash' : 'password';

    // Update password
    $updateQuery = "UPDATE users SET $passwordColumn = '$newHash' WHERE username = '$usernameEscaped'";
    if ($db->query($updateQuery)) {
        echo "Password for '$username' has been successfully reset to: <strong>$newPassword</strong>";
    } else {
        echo "Failed to reset password: " . $db->error;
    }
}
?>
