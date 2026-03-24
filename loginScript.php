<?php
session_start();
include 'dbConnection.php';

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
        // Use constant username and password for the API
        $apiUsername = 'psme';
        $apiPassword = '9hbpRSKahp@T';

        // Make API request
        $ch = curl_init('https://psme-api.smartpay.ph/public/index.php/api/adminlogin');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['username' => $apiUsername, 'password' => $apiPassword]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['access_token'])) {
            $_SESSION['access_token'] = $data['access_token'];
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = 'Login failed: ' . ($data['message'] ?? 'API connection error');
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
