<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

include 'functions.php';

if (!hasRole('admin')) {
    http_response_code(403);
    echo 'Forbidden: Admin access required.';
    exit();
}

require __DIR__ . '/eventConfig.php';

$alertType = '';
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newEventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

    if ($newEventId <= 0) {
        $alertType = 'danger';
        $alertMessage = 'Please enter a valid event ID greater than zero.';
    } else {
        $saved = setActiveEventId($db, $newEventId, $user_id);
        if ($saved) {
            $alertType = 'success';
            $alertMessage = 'Active event ID was updated successfully.';
        } else {
            $alertType = 'danger';
            $alertMessage = 'Failed to save the event ID. Please try again.';
        }
    }
}

$eventConfig = getActiveEventConfig($db);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Event Settings | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
</head>

<body id="page-top">
    <div id="wrapper">

        <?php include 'sidenav.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">

                <?php include 'topbar.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Event Settings</h1>
                    </div>

                    <?php if ($alertMessage !== ''): ?>
                        <div class="alert alert-<?php echo $alertType; ?>" role="alert">
                            <?php echo htmlspecialchars($alertMessage); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Active Event ID</h6>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="event_settings">
                                        <div class="form-group">
                                            <label for="event_id">Event ID</label>
                                            <input
                                                type="number"
                                                min="1"
                                                step="1"
                                                class="form-control"
                                                id="event_id"
                                                name="event_id"
                                                value="<?php echo intval($eventConfig['event_id']); ?>"
                                                required>
                                            <small class="form-text text-muted">
                                                This event ID will be used for scan/search/registration API calls across the system.
                                            </small>
                                        </div>

                                        <button type="submit" class="btn btn-primary">Save Event ID</button>
                                    </form>

                                    <hr>

                                    <p class="mb-1"><strong>Current Active Event ID:</strong> <?php echo intval($eventConfig['event_id']); ?></p>
                                    <p class="mb-1">
                                        <strong>Last Updated By:</strong>
                                        <?php echo htmlspecialchars($eventConfig['updated_by_username'] ?: 'System'); ?>
                                    </p>
                                    <p class="mb-0">
                                        <strong>Last Updated At:</strong>
                                        <?php echo htmlspecialchars($eventConfig['updated_at'] ?: 'N/A'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; PSME ICTO 2024</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>