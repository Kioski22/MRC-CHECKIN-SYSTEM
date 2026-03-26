<?php
session_start();
include 'dbConnection.php';
require __DIR__ . '/smartpayAuth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verify user credentials
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $tokenError = null;
        $accessToken = smartpayEnsureSessionToken($tokenError, true);

        if ($accessToken !== null) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = 'Login failed: ' . ($tokenError ?? 'API connection error');
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = 'Invalid credentials';
        header("Location: login.php");
        exit();
    }
}
?>
