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
include 'test_config.php';

// Handle form submission to update test config
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_test_mode') {
        $test_enabled = isset($_POST['test_enabled']) ? true : false;
        $test_date = !empty($_POST['test_date']) ? $_POST['test_date'] : null;
        $show_banner = isset($_POST['show_banner']) ? true : false;
        
        // Update configuration in database
        $success = true;
        $success &= setTestConfigInDB('test_mode_enabled', $test_enabled, 'Enable or disable test mode (true/false)');
        $success &= setTestConfigInDB('test_date_override', $test_date, 'Override current date for testing (Y-m-d format or NULL)');
        $success &= setTestConfigInDB('test_mode_banner', $show_banner, 'Show test mode banner (true/false)');
        
        if ($success) {
            $success_message = "Test configuration updated successfully in database!";
        } else {
            $error_message = "Error updating test configuration in database. Check error logs for details.";
        }
    }
}

$current_config = getTestConfig();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Test Mode Controls | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <style>
        .test-mode-card {
            border-left: 4px solid #ffc107;
        }
        
        .current-status {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
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

                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-flask"></i> Test Mode Controls
                        </h1>
                    </div>

                    <!-- Current Status -->
                    <div class="current-status">
                        <h5><i class="fas fa-info-circle"></i> Current System Status</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Test Mode:</strong> 
                                    <?php if (isTestMode()): ?>
                                        <span class="badge badge-warning">ENABLED</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">DISABLED</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Actual Date:</strong> <?php echo date('Y-m-d (F j, Y)'); ?></p>
                                <p><strong>System Date:</strong> <?php echo getCurrentDate() . ' (' . date('F j, Y', strtotime(getCurrentDate())) . ')'; ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Convention Status:</strong>
                                    <?php if (isConventionDay()): ?>
                                        <span class="badge badge-success">Day <?php echo getConventionDay(); ?> - Active</span>
                                    <?php elseif (getCurrentDate() < '2025-10-16'): ?>
                                        <span class="badge badge-info">Pre-Convention</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Post-Convention</span>
                                    <?php endif; ?>
                                </p>
                                <p><strong>Food Release:</strong>
                                    <?php if (isConventionDay()): ?>
                                        <span class="badge badge-success">AVAILABLE</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">NOT AVAILABLE</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Test Mode Configuration -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4 test-mode-card">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-warning">
                                        <i class="fas fa-cog"></i> Test Mode Configuration
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <input type="hidden" name="action" value="update_test_mode">
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="test_enabled" name="test_enabled" 
                                                               <?php echo $current_config['test_mode_enabled'] ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="test_enabled">
                                                            <strong>Enable Test Mode</strong>
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Enables date simulation for testing food release functionality
                                                    </small>
                                                </div>

                                                <div class="form-group">
                                                    <label for="test_date"><strong>Simulation Date</strong></label>
                                                    <select class="form-control" id="test_date" name="test_date">
                                                        <option value="">Use actual current date</option>
                                                        <?php foreach ($current_config['available_test_dates'] as $date => $description): ?>
                                                            <option value="<?php echo $date; ?>" 
                                                                    <?php echo ($current_config['test_date_override'] === $date) ? 'selected' : ''; ?>>
                                                                <?php echo $date . ' - ' . $description; ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        Select which date to simulate for testing
                                                    </small>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="show_banner" name="show_banner" 
                                                               <?php echo $current_config['test_mode_banner'] ? 'checked' : ''; ?>>
                                                        <label class="custom-control-label" for="show_banner">
                                                            <strong>Show Test Mode Banner</strong>
                                                        </label>
                                                    </div>
                                                    <small class="form-text text-muted">
                                                        Display warning banner when test mode is active
                                                    </small>
                                                </div>

                                                <div class="alert alert-info">
                                                    <h6><i class="fas fa-lightbulb"></i> Testing Instructions</h6>
                                                    <ol class="mb-0">
                                                        <li>Enable test mode above</li>
                                                        <li>Select a convention date (Day 1, 2, or 3)</li>
                                                        <li>Go to <a href="search_food_release" target="_blank">Food Release Search</a></li>
                                                        <li>Search for any registrant and test releasing food</li>
                                                        <li>Disable test mode when done testing</li>
                                                    </ol>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="form-group">
                                            <button type="submit" class="btn btn-warning">
                                                <i class="fas fa-save"></i> Update Test Configuration
                                            </button>
                                            
                                            <a href="search_food_release" class="btn btn-success ml-2" target="_blank">
                                                <i class="fas fa-external-link-alt"></i> Test Food Release Search
                                            </a>
                                            
                                            <a href="list_food" class="btn btn-primary ml-2" target="_blank">
                                                <i class="fas fa-external-link-alt"></i> Test Food Management
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Test Scenarios -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-clipboard-list"></i> Quick Test Scenarios
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <h6>ðŸ• Pre-Convention Testing</h6>
                                            <p>Set date to <code>2025-10-15</code> to test:</p>
                                            <ul>
                                                <li>Food release should be disabled</li>
                                                <li>Status shows "Pre-Convention"</li>
                                                <li>Release buttons are hidden</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>ðŸ½ï¸ Convention Day Testing</h6>
                                            <p>Set date to <code>2025-10-16/17/18</code> to test:</p>
                                            <ul>
                                                <li>Food release is enabled</li>
                                                <li>Can release food to registrants</li>
                                                <li>Prevents duplicate releases</li>
                                                <li>Shows correct day status</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <h6>ðŸ•• Post-Convention Testing</h6>
                                            <p>Set date to <code>2025-10-19</code> to test:</p>
                                            <ul>
                                                <li>Food release should be disabled</li>
                                                <li>Status shows "Post-Convention"</li>
                                                <li>Historical data is visible</li>
                                            </ul>
                                        </div>
                                    </div>
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
                        <span>&copy; 2024 PSME ICTO. All Rights Reserved.</span>
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
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">Ã—</span>
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

</body>

</html>
