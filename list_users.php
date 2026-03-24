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

    <title>List Users | 73rd National Convention</title>
    <link rel="icon" href="img/logo.ico" type="image/icon type">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.min.css">

    <!-- DataTables Responsive CSS -->
    <link href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css" rel="stylesheet" type="text/css">
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
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">List Users</h6>
                                </div>
                                <div class="card-body">
                                    <table id="usersTable" class="display nowrap table-responsive" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>User ID</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Created At</th>
                                                <th>Role</th>
                                                <th>Actions</th> <!-- Actions column -->
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

                <!-- Modal for showing the new password -->
                <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="resetPasswordModalLabel">Password Reset</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p><strong>New Password:</strong> <span id="newPassword"></span></p>

                                <!-- Container for buttons and spinner in a single row -->
                                <div class="d-flex align-items-center">
                                    <button id="copyPasswordBtn" class="btn btn-secondary mr-2">Copy to Clipboard</button>
                                    <button id="sendEmailBtn" class="btn btn-primary mr-2">Send Email</button>

                                    <!-- Inline spinner aligned with the buttons, initially hidden -->
                                    <div id="emailSpinner" class="spinner-border text-primary align-middle" role="status" style="width: 1.5rem; height: 1.5rem;">
                                        <span class="sr-only">Sending email...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#emailSpinner').css('display', 'none'); // Explicitly set it to none

            $('#usersTable').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "ajax": {
                    "url": "fetchUsersData", // Server-side script
                    "type": "POST"
                },
                "columns": [{
                        "data": "user_id"
                    },
                    {
                        "data": "username"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "created_at"
                    },
                    {
                        "data": "role_name"
                    },
                    {
                        "data": "actions",
                        "orderable": false,
                        "searchable": false
                    } // Actions column
                ],
                "order": [
                    [0, "asc"]
                ]
            });

            // Handle delete button click
            $('#usersTable').on('click', '.delete-user-btn', function() {
                var userId = $(this).data('id');
                if (confirm('Are you sure you want to delete this user?')) {
                    deleteUser(userId); // Call function to delete the user
                }
            });

            $('#usersTable').on('click', '.reset-password-btn', function() {
                var userId = $(this).data('id');

                // Generate a 6-character alphanumeric password
                var newPassword = generateRandomPassword();

                // First, update the password in the database
                $.ajax({
                    url: 'resetPassword',
                    type: 'POST',
                    data: {
                        user_id: userId,
                        new_password: newPassword
                    },
                    success: function(response) {
                        try {
                            // Ensure the response is valid JSON
                            response = JSON.parse(response);

                            if (response.success) {
                                // If the password update is successful, display it in the modal
                                $('#newPassword').text(newPassword);

                                // Store the user ID and password in data attributes for use in email/copy actions
                                $('#resetPasswordModal').data('userId', userId);
                                $('#resetPasswordModal').data('newPassword', newPassword);

                                // Show the modal
                                $('#resetPasswordModal').modal('show');
                            } else {
                                alert('Failed to reset password: ' + response.message);
                            }
                        } catch (e) {
                            alert('Error parsing response: ' + e.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        // Detailed error message for network issues or server errors
                        alert('Error resetting password: ' + xhr.responseText || error);
                    }
                });
            });

            function generateRandomPassword() {
                var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                var password = '';
                for (var i = 0; i < 6; i++) {
                    password += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return password;
            }


        });

        $('#copyPasswordBtn').on('click', function() {
            var newPassword = $('#resetPasswordModal').data('newPassword');
            copyToClipboard(newPassword).then(function(success) {
                if (success) {
                    alert('Password copied to clipboard!');
                } else {
                    alert('Failed to copy password to clipboard.');
                }
            });
        });

        // Function to copy text to clipboard with support for modern API
        function copyToClipboard(text) {
            // Check if the modern Clipboard API is available
            if (navigator.clipboard && window.isSecureContext) {
                // Use the modern asynchronous clipboard API
                return navigator.clipboard.writeText(text).then(function() {
                    return true; // Success
                }).catch(function() {
                    return false; // Failure
                });
            } else {
                // Fallback to the older document.execCommand method
                return new Promise(function(resolve, reject) {
                    var tempInput = document.createElement('input');
                    tempInput.style.position = 'absolute';
                    tempInput.style.left = '-9999px';
                    tempInput.value = text;
                    document.body.appendChild(tempInput);
                    tempInput.select();
                    try {
                        var successful = document.execCommand('copy');
                        document.body.removeChild(tempInput);
                        resolve(successful);
                    } catch (err) {
                        document.body.removeChild(tempInput);
                        resolve(false);
                    }
                });
            }
        }

        $('#sendEmailBtn').on('click', function() {
            var userId = $('#resetPasswordModal').data('userId');
            var newPassword = $('#resetPasswordModal').data('newPassword');

            // Disable the button and manually set display to block to show the spinner
            $('#sendEmailBtn').prop('disabled', true);
            $('#emailSpinner').css('display', 'inline-block'); // Manually set display to inline-block to show the spinner

            // Send an AJAX request to email the password
            $.ajax({
                url: 'sendPasswordEmail',
                type: 'POST',
                data: {
                    user_id: userId,
                    new_password: newPassword
                },
                dataType: 'json', // Expecting a JSON response
                success: function(response) {
                    // On success or failure, re-enable the button and manually hide the spinner
                    $('#sendEmailBtn').prop('disabled', false);
                    $('#emailSpinner').css('display', 'none'); // Manually set display to none to hide the spinner

                    if (response.success) {
                        alert('Password email sent successfully');
                    } else {
                        alert('Failed to send password email: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // On error, re-enable the button and manually hide the spinner
                    $('#sendEmailBtn').prop('disabled', false);
                    $('#emailSpinner').css('display', 'none'); // Manually set display to none to hide the spinner

                    alert('Error sending password email: ' + textStatus + ' - ' + errorThrown);
                }
            });
        });

        // Function to send AJAX request to delete the user
        function deleteUser(userId) {
            $.ajax({
                url: 'deleteUser', // Server-side script to handle deletion
                type: 'POST',
                data: {
                    user_id: userId
                },
                success: function(response) {
                    // Ensure the response is parsed as JSON
                    try {
                        response = JSON.parse(response);
                    } catch (error) {
                        alert('Unexpected response from server');
                        return;
                    }

                    if (response.success) {
                        alert('User deleted successfully');
                        $('#usersTable').DataTable().ajax.reload(); // Reload the table after deletion
                    } else {
                        // Show the server's detailed failure message
                        alert('Failed to delete user: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Display more information about the error
                    alert('Error occurred while deleting the user: ' + textStatus + ' - ' + errorThrown);
                }
            });
        }

        feather.replace();
    </script>
</body>

</html>