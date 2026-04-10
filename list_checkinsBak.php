<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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

    <title>PSME ICTO - List Check-Ins</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <style>
        #checkins-container,
        #kits-container {
            position: relative;
            /* Set position to relative to contain the absolute loader */
            min-height: 110px;
            /* Adjust based on your needs to ensure there’s enough space */
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
                            <!-- Content will be loaded here -->
                        </div>
                        <div class="col-lg-6 mb-4" id="kits-container">
                            <div id="kits-loader" class="loader"></div>
                            <!-- Content will be loaded here -->
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
                                        <table id="checkinsTable" class="display nowrap" style="width: 100%;">
                                            <thead>
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
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                    <a class="btn btn-primary" href="logout.php">Logout</a>
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
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.print.min.js"></script>

    <!-- Initialize DataTables -->
    <script>
        $(document).ready(function() {

            // Show loaders initially
            document.getElementById("checkins-loader").style.display = 'block';
            document.getElementById("kits-loader").style.display = 'block';

            // Function to fetch data using AJAX
            function fetchData() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'data.php', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            updateContainer('checkins-container', data.checkedins, 'checkins-loader');
                            updateContainer('kits-container', data.releasedkits, 'kits-loader');
                            feather.replace(); // Assuming this is a function for icons or similar
                        } catch (e) {
                            console.error('Error parsing JSON data: ', e);
                        }
                    } else if (xhr.readyState === 4 && xhr.status !== 200) {
                        console.error('Error fetching data from server.');
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
                "ajax": {
                    "url": "fetchCheckinsData.php",
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
                "dom": 'Bfrtip',
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, 'All']
                ],
                "pageLength": 25,
                "buttons": [ //{
                    //extend: 'copyHtml5',
                    //text: 'Copy',
                    //titleAttr: 'Copy table data to clipboard',
                    //},
                    {
                        extend: 'excelHtml5',
                        text: 'Export page to Excel',
                        titleAttr: 'Export table data to Excel',
                        exportOptions: {
                            columns: ':visible, :hidden'
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: 'Export all to Excel',
                        titleAttr: 'Export table data to Excel',
                        action: function(e, dt, button, config) {
                            $.ajax({
                                url: 'fetchAllCheckinsData.php', // Update this to your new endpoint's URL
                                type: 'POST',
                                success: function(response) {
                                    // Use response.data for export
                                    let exportData = response.data;

                                    // Use SheetJS or any other library to export data to Excel
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

            // Hide loader once data is ready
            loader.style.display = 'none';

            // Update container content with the data
            container.innerHTML = `
            <div class="card bg-${data.color} text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="me-3">
                            <div class="text-white-75 small">${data.label}</div>
                            <div class="fw-bold" style="font-size: 35px;">${data.value}</div>
                        </div>
                        <i class="feather-xl text-white-50" data-feather="${data.icon}" style='width: 60px; height: 60px;'></i>
                    </div>
                </div>
            </div>
        `;
        }

        // Function to delete a check-in entry by ID
        function deleteCheckin(checkinId) {
            $.ajax({
                url: 'deleteCheckin.php',
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
                url: 'deleteKitReleasedStatus.php', // Update this to your server-side endpoint
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