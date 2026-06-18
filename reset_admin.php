<?php
include 'dbConnection.php';

$username = 'admin';
$newPassword = 'password123'; // The new password to set
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

$usernameEscaped = $db->real_escape_string($username);

// Check if user exists
$query = "SELECT user_id FROM users WHERE username = '$usernameEscaped'";
$result = $db->query($query);

if (!$result) {
    die("Database query failed: " . $db->error . "<br>This means the database connection is working, but the query itself failed. Are you sure you're connected to the right database?");
}

if ($result->num_rows === 0) {
    echo "User '$username' does not exist in the database.";
} else {
    // Update password
    $updateQuery = "UPDATE users SET password_hash = '$newHash' WHERE username = '$usernameEscaped'";
    if ($db->query($updateQuery)) {
        echo "Password for '$username' has been successfully reset to: <strong>$newPassword</strong>";
    } else {
        echo "Failed to reset password: " . $db->error;
    }
}
?>
