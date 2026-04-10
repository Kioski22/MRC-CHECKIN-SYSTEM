<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

include 'functions.php';
require __DIR__ . '/apiHealth.php';

if (!hasRole('admin')) {
    http_response_code(403);
    echo 'Forbidden: Admin access required.';
    exit();
}

$summary = apiHealthGetServiceSummary($db, 'smartpay');
$recentEvents = apiHealthGetRecentEvents($db, 'smartpay', 50);

$endpointSummary = [];
foreach ($recentEvents as $event) {
    $endpoint = $event['endpoint_name'];
    if (!isset($endpointSummary[$endpoint])) {
        $endpointSummary[$endpoint] = [
            'endpoint' => $endpoint,
            'latest_state' => $event['health_state'],
            'ok_count' => 0,
            'failure_count' => 0,
        ];
    }

    if (in_array($event['failure_type'], ['ok', 'no_data'], true)) {
        $endpointSummary[$endpoint]['ok_count']++;
    } else {
        $endpointSummary[$endpoint]['failure_count']++;
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

    <title>API Monitoring | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <style>
        .status-hero {
            background: linear-gradient(135deg, #0f766e 0%, #155e75 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .metric-card {
            border-left-width: 4px;
            border-left-style: solid;
        }

        .metric-card.success {
            border-left-color: #1cc88a;
        }

        .metric-card.warning {
            border-left-color: #f6c23e;
        }

        .metric-card.danger {
            border-left-color: #e74a3b;
        }

        .metric-card.secondary {
            border-left-color: #858796;
        }
    </style>
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-heartbeat"></i> Smartpay API Monitoring</h1>
                    </div>

                    <div class="status-hero">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fas fa-info-circle"></i> Current Status</h5>
                                <p class="mb-2">
                                    <strong>Service:</strong> Smartpay
                                    <span class="badge badge-<?php echo apiHealthBadgeClass($summary['current_state']); ?> ml-2">
                                        <?php echo strtoupper($summary['current_state']); ?>
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Last Message:</strong> <?php echo htmlspecialchars($summary['last_message']); ?></p>
                                <p class="mb-1"><strong>Failure Type:</strong> <?php echo htmlspecialchars($summary['last_failure_type'] ?: 'N/A'); ?></p>
                                <p class="mb-0"><strong>Failure Streak:</strong> <?php echo intval($summary['failure_streak']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Last Event:</strong> <?php echo htmlspecialchars($summary['last_event_at'] ?: 'No activity yet'); ?></p>
                                <p class="mb-1"><strong>Last Success:</strong> <?php echo htmlspecialchars($summary['last_success_at'] ?: 'None recorded'); ?></p>
                                <p class="mb-1"><strong>Last Failure:</strong> <?php echo htmlspecialchars($summary['last_failure_at'] ?: 'None recorded'); ?></p>
                                <p class="mb-0"><strong>Last Endpoint:</strong> <?php echo htmlspecialchars($summary['last_endpoint'] ?: 'N/A'); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2 metric-card <?php echo apiHealthBadgeClass($summary['current_state']); ?>">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Current Health</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo strtoupper($summary['current_state']); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2 metric-card secondary">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Last HTTP Code</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $summary['last_http_code'] !== null ? intval($summary['last_http_code']) : 'N/A'; ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4 col-md-6 mb-4">
                            <div class="card shadow h-100 py-2 metric-card secondary">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-uppercase mb-1">Last Response Time</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $summary['last_duration_ms'] !== null ? intval($summary['last_duration_ms']) . ' ms' : 'N/A'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-5 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Endpoint Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($endpointSummary)): ?>
                                        <p class="mb-0 text-muted">No endpoint activity recorded yet.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Endpoint</th>
                                                        <th>Status</th>
                                                        <th>OK</th>
                                                        <th>Fail</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($endpointSummary as $item): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($item['endpoint']); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php echo apiHealthBadgeClass($item['latest_state']); ?>">
                                                                    <?php echo strtoupper($item['latest_state']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo intval($item['ok_count']); ?></td>
                                                            <td><?php echo intval($item['failure_count']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7 mb-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recent Smartpay Events</h6>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recentEvents)): ?>
                                        <p class="mb-0 text-muted">No Smartpay events have been logged yet.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Time</th>
                                                        <th>Endpoint</th>
                                                        <th>State</th>
                                                        <th>Failure</th>
                                                        <th>HTTP</th>
                                                        <th>Duration</th>
                                                        <th>Message</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recentEvents as $event): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($event['created_at']); ?></td>
                                                            <td><?php echo htmlspecialchars($event['endpoint_name']); ?></td>
                                                            <td>
                                                                <span class="badge badge-<?php echo apiHealthBadgeClass($event['health_state']); ?>">
                                                                    <?php echo strtoupper($event['health_state']); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($event['failure_type']); ?></td>
                                                            <td><?php echo $event['http_code'] !== null ? intval($event['http_code']) : 'N/A'; ?></td>
                                                            <td><?php echo $event['duration_ms'] !== null ? intval($event['duration_ms']) . ' ms' : 'N/A'; ?></td>
                                                            <td><?php echo htmlspecialchars($event['message']); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
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

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
</body>

</html>