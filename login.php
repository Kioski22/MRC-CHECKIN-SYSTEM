<?php
session_start();
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}

if (isset($_SESSION['user_id']) || (isset($_SESSION['username']))) {
    header("Location: index.php");
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

    <title>Login | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

</head>

<body class="bg-gradient-teal">

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5" style="max-width: 400px; margin: auto;">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="px-5 pt-5 pb-3">
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
                                    <form class="user" method="POST" action="loginScript.php">
                                        <div class="form-group">
                                            <input type="text" class="form-control form-control-user" id="username" name="username" aria-describedby="usernameHelp" placeholder="Enter Username">
                                        </div>
                                        <div class="form-group input-group">
                                            <input type="password" class="form-control form-control-user" id="password" name="password" placeholder="Enter Password">
                                            <div class="input-group-append">
                                                <span class="input-group-text" onclick="togglePasswordVisibility()">
                                                    <i class="fas fa-eye" id="togglePasswordIcon"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block">Login</button>
                                    </form>
                                    <div class="text-center mt-3">
                                        <a class="small" href="forgot-password">Forgot password?</a>
                                    </div>
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
    <script>
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('togglePasswordIcon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>

</body>

</html>
