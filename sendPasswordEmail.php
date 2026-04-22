<?php
session_start();
require 'gmail_api_mailer.php';

include 'dbConnection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id']) && isset($_POST['new_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = $_POST['new_password'];

    // Fetch user's email address from the database
    $stmt = $db->prepare("SELECT email, username FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($email, $username);
    $stmt->fetch();
    $stmt->close();

    if ($email) {
        $safeUsername = htmlspecialchars((string) $username, ENT_QUOTES, 'UTF-8');
        $safePassword = htmlspecialchars((string) $new_password, ENT_QUOTES, 'UTF-8');

        $mailDefaults = gmail_api_mail_defaults();
        $fromEmail = $mailDefaults['from_address'];
        $fromName = $mailDefaults['from_name'];
        $subject = 'Your Password Has Been Reset';
        $htmlBody = "<p>Hello <strong>{$safeUsername}</strong>,</p><p>Your new password is: <strong>{$safePassword}</strong></p><p>Please log in and change your password as soon as possible.</p>";
        $textBody = "Hello {$username},\n\nYour new password is: {$new_password}\n\nPlease log in and change your password as soon as possible.";
        $mailError = '';

        if (send_via_gmail_api($email, $username, $subject, $htmlBody, $textBody, $fromEmail, $fromName, $mailError)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Message could not be sent. Mailer Error: ' . $mailError]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found for this user']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
