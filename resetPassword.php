<?php
session_start();
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    // Hash the password securely
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }

    $stmt->close();
    $db->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>
