<?php
session_start();
require 'dbConnection.php'; // Database connection file

if (!isset($_SESSION['email']) || !isset($_SESSION['otp_verified'])) {
    // If the email is not set or OTP is not verified, redirect to forgot-password.php
    header("Location: forgot-password.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        // If passwords do not match, set an error message
        $_SESSION['error'] = 'Passwords do not match.';
        header('Location: reset-password.php');
        exit();
    }

    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update the user's password in the database
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
    if (!$stmt) {
        $_SESSION['error'] = 'Database error: Failed to prepare statement.';
        header('Location: reset-password.php');
        exit();
    }
    $stmt->bind_param('ss', $hashed_password, $_SESSION['email']);
    $stmt->execute();

    // Password reset successful, clear the session
    unset($_SESSION['email']);
    unset($_SESSION['otp_verified']);

    $_SESSION['error'] = 'Password reset successfully. You can now log in.';
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Reset Password</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
</head>

<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5" style="max-width: 400px; margin: auto;">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="p-5">
                                    <div class="text-center">
                                        <img src="img/logo.png" alt="Logo" class="img-fluid pb-5" width="40%">
                                    </div>
                                    <!-- Display error message -->
                                    <?php if (!empty($_SESSION['error'])): ?>
                                        <div class="alert alert-danger">
                                            <?php echo $_SESSION['error'];
                                            $_SESSION['error'] = ""; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form class="user" method="POST" action="reset-password.php">
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="new_password" name="new_password" placeholder="Enter New Password" required>
                                        </div>
                                        <div class="form-group">
                                            <input type="password" class="form-control form-control-user" id="confirm_password" name="confirm_password" placeholder="Confirm New Password" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Reset Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer -->
                <footer class="text-center text-white mt-4">
                    <small>Powered by PSME ICTO</small>
                </footer>
                <!-- End of Footer -->
            </div>
        </div>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>