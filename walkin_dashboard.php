<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

include 'functions.php';

if (!hasRole('admin')) {
    header('Location: index.php');
    exit();
}

$totalRegistrations = 0;
$todayRegistrations = 0;

$statsSql = "SELECT
                COUNT(*) AS total_registrations,
                SUM(CASE WHEN DATE(`timestamp`) = CURDATE() THEN 1 ELSE 0 END) AS today_registrations
             FROM registrations";
$statsResult = $db->query($statsSql);
if ($statsResult) {
    $statsRow = $statsResult->fetch_assoc();
    $totalRegistrations = (int) ($statsRow['total_registrations'] ?? 0);
    $todayRegistrations = (int) ($statsRow['today_registrations'] ?? 0);
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

    <title>Walk-in Dashboard | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,700,800,900" rel="stylesheet">

    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="vendor/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/datatables-extensions/responsive.dataTables.min.css">
</head>

<body id="page-top">
    <div id="wrapper">
        <?php include 'sidenav.php'; ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include 'topbar.php'; ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Walk-in Registration Dashboard</h1>
                    </div>

                    <div id="statusAlert"></div>

                    <div class="row">
                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Walk-in Registrations</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalRegistrations); ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today's Registrations</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($todayRegistrations); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Walk-in Registrations</h6>
                            <button id="exportCsvBtn" class="btn btn-sm btn-success">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="walkinTable" class="table table-bordered table-striped table-hover display nowrap" style="width:100%">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Date Registered</th>
                                            <th>Reference No.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Mobile</th>
                                            <th>Membership</th>
                                            <th>Fee</th>
                                            <th>Payment Method</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; PSME ICTO 2026</span>
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
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables-extensions/dataTables.responsive.min.js"></script>

    <script>
        function showAlert(message, type) {
            const alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">'
                + message
                + '<button type="button" class="close" data-dismiss="alert" aria-label="Close">'
                + '<span aria-hidden="true">&times;</span>'
                + '</button></div>';
            $('#statusAlert').html(alertHtml);
        }

        function resendConfirmationEmail(qrcode) {
            if (!qrcode) {
                showAlert('Missing reference number.', 'danger');
                return;
            }

            if (!confirm('Resend confirmation email for ' + qrcode + '?')) {
                return;
            }

            $.ajax({
                url: 'resendConfirmationEmail.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({ qrcode: qrcode }),
                success: function(response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (response.success) {
                        showAlert(response.message, 'success');
                    } else {
                        showAlert(response.message || 'Failed to resend email.', 'danger');
                    }
                },
                error: function(xhr) {
                    const fallback = 'Failed to resend confirmation email.';
                    showAlert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : fallback, 'danger');
                }
            });
        }

        $(document).ready(function() {
            $('#walkinTable').DataTable({
                responsive: true,
                processing: true,
                ajax: {
                    url: 'fetchWalkinRegistrations.php',
                    type: 'GET'
                },
                columns: [
                    { data: 'timestamp' },
                    { data: 'qrcode' },
                    { data: 'name' },
                    { data: 'email' },
                    { data: 'mobile' },
                    { data: 'membership' },
                    {
                        data: 'registration_fee',
                        render: function(data) {
                            const fee = parseFloat(data || 0);
                            return 'PHP ' + fee.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                        }
                    },
                    { data: 'payment_method' },
                    {
                        data: 'qrcode',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return '<button class="btn btn-sm btn-primary resend-btn" data-qrcode="' + data + '"><i class="fas fa-paper-plane"></i> Resend Confirmation</button>';
                        }
                    }
                ],
                order: [[0, 'desc']],
                pageLength: 25
            });

            $('#walkinTable').on('click', '.resend-btn', function() {
                const qrcode = $(this).data('qrcode');
                resendConfirmationEmail(qrcode);
            });

            $('#exportCsvBtn').on('click', function() {
                const table = $('#walkinTable').DataTable();
                const headers = ['Date Registered', 'Reference No.', 'Name', 'Email', 'Mobile', 'Membership', 'Fee', 'Payment Method'];
                const rows = table.rows({ search: 'applied' }).data().toArray();

                const escape = function(val) {
                    val = (val === null || val === undefined) ? '' : String(val);
                    val = val.replace(/<[^>]*>/g, ''); // strip HTML tags
                    if (val.search(/("|
|,)/g) >= 0) {
                        val = '"' + val.replace(/"/g, '""') + '"';
                    }
                    return val;
                };

                let csv = headers.map(escape).join(',') + '\n';
                rows.forEach(function(row) {
                    const fee = parseFloat(row.registration_fee || 0).toFixed(2);
                    const line = [
                        row.timestamp,
                        row.qrcode,
                        row.name,
                        row.email,
                        row.mobile,
                        row.membership,
                        fee,
                        row.payment_method
                    ].map(escape).join(',');
                    csv += line + '\n';
                });

                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'walkin_registrations_' + new Date().toISOString().slice(0, 10) + '.csv';
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
        });
    </script>
</body>

</html>
