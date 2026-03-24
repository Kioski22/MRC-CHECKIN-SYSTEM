<?php
session_start();
require 'PHPMailer/src/PHPMailer.php'; // Include PHPMailer
require 'PHPMailer/src/SMTP.php'; // Include SMTP for sending email
require 'PHPMailer/src/Exception.php'; // Include Exception for error handling

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com';                     // Specify SMTP server (replace with your SMTP server)
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'icto@psmeinc.org.ph';               // SMTP username (replace with your email)
            $mail->Password   = 'xjmt szqn gqzj bosz';                        // SMTP password (replace with your email password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; PHPMailer::ENCRYPTION_SMTPS also possible
            $mail->Port       = 587;                                    // TCP port to connect to

            //Recipients
            $mail->setFrom('icto@psmeinc.org.ph', 'Password Reset');    // Sender email and name
            $mail->addAddress($email, $username);                       // Recipient email and name

            // Content
            $mail->isHTML(true);                                        // Set email format to HTML
            $mail->Subject = 'Your Password Has Been Reset';
            $mail->Body    = "<p>Hello <strong>$username</strong>,</p><p>Your new password is: <strong>$new_password</strong></p><p>Please log in and change your password as soon as possible.</p>";
            $mail->AltBody = "Hello $username,\n\nYour new password is: $new_password\n\nPlease log in and change your password as soon as possible.";

            // Send email
            $mail->send();
            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            // Handle PHPMailer exception
            echo json_encode(['success' => false, 'message' => "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Email not found for this user']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
