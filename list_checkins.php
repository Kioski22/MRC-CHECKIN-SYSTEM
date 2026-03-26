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

    <title>List Check-Ins | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <script src="vendor/feather-icons/feather.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="vendor/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/datatables-extensions/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="vendor/datatables-extensions/buttons.dataTables.min.css">

    <script src="vendor/xlsx/xlsx.full.min.js"></script>

    <style>
        #checkins-container,
        #kits-container {
            position: relative;
            /* Set position to relative to contain the absolute loader */
            min-height: 110px;
            /* Adjust based on your needs to ensure thereâ€™s enough space */
        }

        .loader {
            border: 4px solid #f3f3f3;
            /* Light grey */
            border-top: 4px solid #3498db;
            /* Blue */
            border-radius: 100%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10;
            /* Ensure it is above other content */
        }

        /* Feather Icons styling */
        .feather-icon {
            width: 24px;
            height: 24px;
            stroke-width: 2;
        }

        .feather-xl {
            stroke-width: 1.5;
        }

        /* DataTables custom styling */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.375rem 0.75rem !important;
            margin-left: 0.125rem !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 0.25rem !important;
            background: #fff !important;
            color: #495057 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #e9ecef !important;
            border-color: #adb5bd !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #007bff !important;
            border-color: #007bff !important;
            color: #fff !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
            color: #6c757d !important;
            background: #fff !important;
            border-color: #dee2e6 !important;
        }

        /* Table borders */
        table.dataTable thead th,
        table.dataTable tbody td {
            border: 1px solid #dee2e6 !important;
        }

        table.dataTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }


        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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
                    <div class="row">
                        <div class="col-lg-6 mb-4" id="checkins-container">
                            <div id="checkins-loader" class="loader"></div>
                            <div id="checkins-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4" id="kits-container">
                            <div id="kits-loader" class="loader"></div>
                            <div id="kits-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">List Check Ins</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="checkinsTable" class="table table-bordered table-striped table-hover display nowrap" style="width: 100%;">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>Check-in ID</th>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Middle Name</th>
                                                    <th>Email</th>
                                                    <th>PRC License Number</th>
                                                    <th>Chapter</th>
                                                    <th>QR Code</th>
                                                    <th>Scanned Time</th>
                                                    <th>Check-in Time</th>
                                                    <th>Kit Release Time</th>
                                                    <th>Mini Souvenir Program Release Time</th>
                                                    <th>Scanned By</th>
                                                    <th>Check-in By</th>
                                                    <th>Released By</th>
                                                    <th>Mini Souvenir Program Release By</th>
                                                    <th>Actions</th> <!-- New column for actions -->
                                                </tr>
                                            </thead>
                                        </table>
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
    <script src="vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="vendor/datatables-extensions/dataTables.responsive.min.js"></script>
    <script src="vendor/datatables-extensions/dataTables.buttons.min.js"></script>
    <script src="vendor/jszip/jszip.min.js"></script>
    <script src="vendor/pdfmake/pdfmake.min.js"></script>
    <script src="vendor/pdfmake/vfs_fonts.js"></script>
    <script src="vendor/datatables-extensions/buttons.html5.min.js"></script>
    <script src="vendor/datatables-extensions/buttons.print.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        // Initialize Feather icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });

        $(document).ready(function() {

            // Show loaders initially
            const checkinsLoader = document.getElementById("checkins-loader");
            const kitsLoader = document.getElementById("kits-loader");
            
            if (checkinsLoader && kitsLoader) {
                checkinsLoader.style.display = 'block';
                kitsLoader.style.display = 'block';
            }

            // Function to fetch data using AJAX
            function fetchData() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'data', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                if (data.checkedins) {
                                    updateContainer('checkins-content', data.checkedins, 'checkins-loader');
                                }
                                if (data.releasedkits) {
                                    updateContainer('kits-content', data.releasedkits, 'kits-loader');
                                }
                                
                                // Initialize Feather icons if library is loaded
                                if (typeof feather !== 'undefined') {
                                    feather.replace();
                                }
                            } catch (e) {
                                console.error('Error parsing JSON data:', e);
                                console.log('Response text:', xhr.responseText);
                            }
                        } else {
                            console.error('Error fetching data from server. Status:', xhr.status);
                        }
                    }
                };
                xhr.send();
            }

            // Fetch data initially and then every 5 seconds
            fetchData(); // Initial fetch
            setInterval(fetchData, 5000); // Polling every 5 seconds

            var checkinsTable = $('#checkinsTable').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthChange": true,
                "autoWidth": false,
                "ajax": {
                    "url": "fetchCheckinsData",
                    "type": "POST",
                    "cache": false // Disable caching for AJAX requests
                },
                "columns": [{
                        "data": "checkin_id"
                    },
                    {
                        "data": "firstname"
                    },
                    {
                        "data": "lastname"
                    },
                    {
                        "data": "middlename"
                    },
                    {
                        "data": "email"
                    },
                    {
                        "data": "prclicensenumber"
                    },
                    {
                        "data": "chapter"
                    },
                    {
                        "data": "qrcode",
                        "render": function(data, type, row) {
                            return `<a href="${data}" target="_blank">View</a>`;
                        }
                    },
                    {
                        "data": "scanned_time"
                    },
                    {
                        "data": "checkin_time"
                    },
                    {
                        "data": "kit_release_time"
                    },
                    {
                        "data": "releasedprogram_time"
                    },
                    {
                        "data": "scanned_by_username"
                    },
                    {
                        "data": "checkin_by_username"
                    },
                    {
                        "data": "released_by_username"
                    },
                    {
                        "data": "releasedprogram_by_username"
                    },
                    {
                        "data": "checkin_id",
                        "render": function(data, type, row) {
                            <?php if (hasPermission('delete_check_in')): ?>
                                return `
                                    <button class="btn btn-danger btn-sm delete-btn" data-id="${data}">Delete</button>
                                    <button class="btn btn-warning btn-sm delete-kit-btn" data-id="${data}">Delete Kit Released Status</button>
                                `;
                            <?php else: ?>
                                return `No permission to delete`;
                            <?php endif; ?>
                        },
                    }
                ],
                "order": [
                    [0, "asc"]
                ],
                "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                       '<"row"<"col-sm-12"B>>' +
                       '<"row"<"col-sm-12"tr>>' +
                       '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                "pageLength": 25,
                "buttons": [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Export page to Excel',
                        titleAttr: 'Export table data to Excel',
                        className: 'btn btn-success btn-sm',
                        exportOptions: {
                            columns: ':visible, :hidden'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-download"></i> Export all to Excel',
                        titleAttr: 'Export all data to Excel',
                        className: 'btn btn-primary btn-sm',
                        action: function(e, dt, button, config) {
                            $.ajax({
                                url: 'fetchAllCheckinsData',
                                type: 'POST',
                                success: function(response) {
                                    let exportData = response.data;
                                    var ws = XLSX.utils.json_to_sheet(exportData);
                                    var wb = XLSX.utils.book_new();
                                    XLSX.utils.book_append_sheet(wb, ws, "All Data");
                                    XLSX.writeFile(wb, "Exported_Data.xlsx");
                                }
                            });
                        }
                    }
                ]
            });

            // Handle delete button click
            $('#checkinsTable').on('click', '.delete-btn', function() {
                var checkinId = $(this).data('id');
                if (confirm('Are you sure you want to delete this check-in record? \nThis will remove all records of the participant.\nThis action cannot be undone.')) {
                    deleteCheckin(checkinId);
                }
            });

            // Handle delete button click for deleting kit release status only
            $('#checkinsTable').on('click', '.delete-kit-btn', function() {
                var checkinId = $(this).data('id');
                if (confirm('Are you sure you want to delete the kit released status for this participant? \nThis action cannot be undone.')) {
                    deleteKitReleasedStatus(checkinId);
                }
            });
        });

        // Function to update container contents and hide loader
        function updateContainer(containerId, data, loaderId) {
            var container = document.getElementById(containerId);
            var loader = document.getElementById(loaderId);

            if (!container) {
                console.error('Container element not found:', containerId);
                return;
            }
            if (!loader) {
                console.error('Loader element not found:', loaderId);
                return;
            }

            // Hide loader once data is ready
            loader.style.display = 'none';

            // Ensure data has required properties
            if (!data || !data.color || !data.label || data.value === undefined || !data.icon) {
                console.error('Invalid data structure:', data);
                container.innerHTML = '<div class="card bg-secondary text-white h-100"><div class="card-body"><div class="text-center">No data available</div></div></div>';
                return;
            }

            // Update container content with the data
            container.innerHTML = `
                <div class="card bg-${data.color} text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="me-3">
                                <div class="text-white-75 small">${data.label}</div>
                                <div class="fw-bold" style="font-size: 35px;">${data.value}</div>
                            </div>
                            <i class="feather-xl text-white-50" data-feather="${data.icon}" style="width: 60px; height: 60px;"></i>
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to delete a check-in entry by ID
        function deleteCheckin(checkinId) {
            $.ajax({
                url: 'deleteCheckin',
                type: 'POST',
                data: {
                    checkin_id: checkinId
                },
                success: function(response) {
                    try {
                        // Ensure response is properly parsed
                        if (typeof response === "string") {
                            response = JSON.parse(response);
                        }
                        if (response.success) {
                            alert(response.message);
                            $('#checkinsTable').DataTable().ajax.reload(null, false); // Reload the table data without resetting pagination
                        } else {
                            alert('Error: ' + response.message);
                        }
                    } catch (e) {
                        console.error('Failed to parse response:', e);
                        alert('Failed to process the request.');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('Failed to delete the check-in record. Please try again.');
                }
            });
        }

        function deleteKitReleasedStatus(checkinId) {
            $.ajax({
                url: 'deleteKitReleasedStatus', // Update this to your server-side endpoint
                type: 'POST',
                data: {
                    checkin_id: checkinId
                },
                success: function(response) {
                    // Parse the response to ensure it is in JSON format
                    response = JSON.parse(response);

                    if (response.success) {
                        alert(response.message); // Show success message if the kit release status was deleted
                        $('#checkinsTable').DataTable().ajax.reload(); // Reload the DataTable
                    } else {
                        // Display the specific error message returned by the server
                        alert(response.message); // Show the server message (e.g., kit not yet released)
                    }
                },
                error: function() {
                    alert('Error deleting kit release status.');
                }
            });
        }
    </script>

</body>

</html>
