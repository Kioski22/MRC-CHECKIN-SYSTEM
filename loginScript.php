<?php
session_start();
include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $_SESSION['error'] = 'Please enter both username and password.';
        header("Location: login.php");
        exit();
    }

    // Verify user credentials
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Unable to process login at this time.';
        header("Location: login.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $isPasswordValid = false;
    if ($user) {
        $storedPasswordHash = (string)($user['password_hash'] ?? '');
        $isPasswordValid = password_verify($password, $storedPasswordHash);

        // Backward compatibility for legacy plaintext passwords, then migrate to hash.
        if (!$isPasswordValid && $storedPasswordHash !== '' && hash_equals($storedPasswordHash, $password)) {
            $isPasswordValid = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            if ($newHash !== false) {
                $updateStmt = $db->prepare("UPDATE users SET password_hash = ? WHERE username = ?");
                if ($updateStmt) {
                    $updateStmt->bind_param("ss", $newHash, $username);
                    $updateStmt->execute();
                    $updateStmt->close();
                }
            }
        }
    }

    if ($user && $isPasswordValid) {
        $resolvedUserId = $user['user_id'] ?? ($user['id'] ?? ($user['users_id'] ?? null));
        if ($resolvedUserId === null || $resolvedUserId === '') {
            $_SESSION['error'] = 'Login failed due to account configuration.';
            header("Location: login.php");
            exit();
        }

        $_SESSION['user_id'] = (int)$resolvedUserId;
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = 'Invalid credentials';
        header("Location: login.php");
        exit();
    }
}
?>
