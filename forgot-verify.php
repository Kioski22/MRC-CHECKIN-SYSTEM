<?php
session_start();
require 'dbConnection.php'; // Database connection file

if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}

if (!isset($_SESSION['email'])) {
    header("Location: forgot-password.php"); // Redirect back if email is not set
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = trim($_POST['otp']);
    $email = $_SESSION['email'];

    // Verify the OTP in the database
    $stmt = $db->prepare("SELECT * FROM otp WHERE email = ? AND otp = ? AND otp_expiry > NOW()");
    if (!$stmt) {
        $_SESSION['error'] = 'Database error: Failed to prepare statement.';
        header('Location: forgot-verify.php');
        exit();
    }
    $stmt->bind_param('si', $email, $otp);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // If OTP is invalid or expired
        $_SESSION['error'] = 'Invalid or expired OTP. Please try again.';
        header('Location: forgot-verify.php');
        exit();
    } else {
        // OTP is valid, proceed to reset password page
        $_SESSION['error'] = 'OTP verified';
        $_SESSION['otp_verified'] = true; // Set a session variable to indicate successful verification
        header('Location: reset-password.php');
        exit();
    }
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

    <title>Verify OTP</title>

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
                                        <img src="img/73rdNatCon.jpg" alt="Logo" class="img-fluid pb-5" width="40%">
                                    </div>
                                    <!-- Display error message -->
                                    <?php if (!empty($_SESSION['error'])): ?>
                                        <div class="alert alert-danger">
                                            <?php echo $_SESSION['error'];
                                            $_SESSION['error'] = ""; ?>
                                        </div>
                                    <?php endif; ?>
                                    <form class="user" method="POST" action="forgot-verify.php">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" id="otp" name="otp" placeholder="Enter OTP" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Confirm OTP</button>
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
