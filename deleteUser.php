<?php
session_start();
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Begin transaction to ensure both deletions occur together
    $db->begin_transaction();

    try {
        // Delete from user_roles table first
        $roleStmt = $db->prepare("DELETE FROM user_roles WHERE user_id = ?");
        $roleStmt->bind_param("i", $user_id);

        if (!$roleStmt->execute()) {
            throw new Exception('Failed to delete from user_roles.');
        }
        $roleStmt->close();

        // Delete from users table
        $userStmt = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $userStmt->bind_param("i", $user_id);

        if (!$userStmt->execute()) {
            throw new Exception('Failed to delete from users.');
        }
        $userStmt->close();

        // If both deletions were successful, commit the transaction
        $db->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        // Rollback transaction in case of error
        $db->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $db->close();
} else {
    // Invalid request
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
