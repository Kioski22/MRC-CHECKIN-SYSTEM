<?php
session_start();
require 'dbConnection.php'; // Database connection file
require 'gmail_api_mailer.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    // Check if the email exists in the database
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Database error: Failed to prepare statement.';
        header('Location: forgot-password.php');
        exit();
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // If the email doesn't exist, return an error
        $_SESSION['error'] = 'The email address does not exist.';
        header('Location: forgot-password.php');
        exit();
    } else {
        // Generate OTP
        $otp = mt_rand(100000, 999999); // 6-digit random number

        // Insert OTP into the 'otp' table
        $stmt = $db->prepare("INSERT INTO otp (email, otp, otp_expiry) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
        if (!$stmt) {
            $_SESSION['error'] = 'Database error: Failed to prepare statement for OTP insertion.';
            header('Location: forgot-password.php');
            exit();
        }
        $stmt->bind_param('si', $email, $otp);
        $stmt->execute();

        $mailDefaults = gmail_api_mail_defaults();
        $fromEmail = $mailDefaults['from_address'] ?: 'icto@psmeinc.org.ph';
        $fromName = $mailDefaults['from_name'] ?: 'PSME ICTO';
        $subject = 'Your OTP for Password Reset';
        $htmlBody = 'Your OTP is <b>' . $otp . '</b>. It is valid for 10 minutes.';
        $textBody = 'Your OTP is ' . $otp . '. It is valid for 10 minutes.';
        $mailError = '';

        if (send_via_gmail_api($email, '', $subject, $htmlBody, $textBody, $fromEmail, $fromName, $mailError)) {
            // If successful, redirect to forgot-verify.php
            $_SESSION['email'] = $email; // Store the email in the session for verification
            $_SESSION['error'] = 'OTP sent to ' . $email;
            header('Location: forgot-verify.php');
            exit();
        }

        // Log the error message for debugging
        error_log('Mail error: ' . $mailError);

        // If email sending fails, return an error
        $_SESSION['error'] = 'Failed to send OTP. Please try again.';
        header('Location: forgot-password.php');
        exit();
    }
}
