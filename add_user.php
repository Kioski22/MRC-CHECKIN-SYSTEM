<?php
session_start();
include 'dbConnection.php';

if (!isset($_SESSION['access_token'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id']) || (!isset($_SESSION['username']))) {
    header("Location: login.php");
    exit();
} else {
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
}

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure password hashing
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];
    $created_at = date('Y-m-d H:i:s');

    $stmt = $db->prepare("INSERT INTO users (username, password_hash, email, status, created_at) VALUES (?, ?, ?, 'active', ?)");
    $stmt->bind_param("ssss", $username, $password, $email, $created_at);
    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $stmt->close();

        $role_stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $role_stmt->bind_param("ii", $user_id, $role_id);
        $role_stmt->execute();
        $role_stmt->close();

        header('location: list_users.php');
    } else {
        echo "Error: " . $stmt->error;
    }
}

include 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Add User | 73rd National Convention</title>
    <link rel="icon" href="img/logo.ico" type="image/icon type">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <?php include 'sidenav.php'; ?>

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <?php include 'topbar.php'; ?>

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Add New User</h6>
                                </div>
                                <div class="card-body">
                                    <form method="post" action="">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Password</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email">Email</label>
                                            <input type="email" class="form-control" id="email" name="email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="role_id">Role</label>
                                            <select class="form-control" id="role_id" name="role_id" required>
                                                <option value="" disabled selected>Select a role</option>
                                                <?php
                                                $roles_query = "SELECT role_id, role_name FROM roles";
                                                $roles_result = $db->query($roles_query);
                                                while ($role = $roles_result->fetch_assoc()) {
                                                    echo "<option value='" . $role['role_id'] . "'>" . $role['role_name'] . "</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <button type="submit" name="register" class="btn btn-primary btn-block">Add User</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; PSME ICTO 2024</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout">Logout</a>
                </div>
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
        feather.replace();
    </script>
</body>

</html>