<?php
include 'dbConnection.php';

$username = 'admin';
$newPassword = 'password123'; // The new password to set
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);

// Check if user exists
$checkStmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
$checkStmt->bind_param("s", $username);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows === 0) {
    echo "User '$username' does not exist in the database.";
} else {
    // Update password
    $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
    $updateStmt->bind_param("ss", $newHash, $username);
    if ($updateStmt->execute()) {
        echo "Password for '$username' has been successfully reset to: <strong>$newPassword</strong>";
    } else {
        echo "Failed to reset password: " . $db->error;
    }
    $updateStmt->close();
}
$checkStmt->close();
?>
