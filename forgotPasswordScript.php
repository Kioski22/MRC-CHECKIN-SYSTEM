<?php
session_start();
require 'dbConnection.php'; // Database connection file
require 'PHPMailer/src/PHPMailer.php'; // Include PHPMailer
require 'PHPMailer/src/SMTP.php'; // Include SMTP for sending email
require 'PHPMailer/src/Exception.php'; // Include Exception for error handling

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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

        // Send OTP using PHPMailer
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Specify SMTP server (e.g., smtp.gmail.com for Gmail)
            $mail->SMTPAuth = true;
            $mail->Username = 'icto@psmeinc.org.ph'; // SMTP username
            $mail->Password = 'xjmt szqn gqzj bosz'; // SMTP password or App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_STARTTLS for most common providers like Gmail
            $mail->Port = 587; // Port used for TLS connections

            //Recipients
            $mail->setFrom('icto@psmeinc.org.ph', 'PSME ICTO'); // Adjust 'Your Website' to your desired "From" name
            $mail->addAddress($email);

            //Content
            $mail->isHTML(true);
            $mail->Subject = 'Your OTP for Password Reset';
            $mail->Body = 'Your OTP is <b>' . $otp . '</b>. It is valid for 10 minutes.';

            $mail->send();

            // If successful, redirect to forgot-verify.php
            $_SESSION['email'] = $email; // Store the email in the session for verification
            $_SESSION['error'] = 'OTP sent to ' . $email;
            header('Location: forgot-verify.php');
            exit();
        } catch (Exception $e) {
            // Log the error message for debugging
            error_log('Mail error: ' . $mail->ErrorInfo);

            // If email sending fails, return an error
            $_SESSION['error'] = 'Failed to send OTP. Please try again.';
            header('Location: forgot-password.php');
            exit();
        }
    }
}
