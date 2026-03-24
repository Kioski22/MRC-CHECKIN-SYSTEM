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

    <title>List Food Registration | 73rd National Convention</title>
    <link rel="icon" href="img/logo.ico" type="image/icon type">

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
        #totalfood-container,
        #unmapped-container,
        #releasedtoday-container,
        #pendingtoday-container {
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
                    <!-- Dashboard Cards Row -->
                    <div class="row">
                        <!-- Total Food Registration Card -->
                        <div class="col-lg-3 col-md-6 mb-4" id="totalfood-container">
                            <div id="totalfood-loader" class="loader"></div>
                            <div id="totalfood-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                        <!-- Unmapped Registrations Card -->
                        <div class="col-lg-3 col-md-6 mb-4" id="unmapped-container">
                            <div id="unmapped-loader" class="loader"></div>
                            <div id="unmapped-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                        <!-- Released Food Today Card -->
                        <div class="col-lg-3 col-md-6 mb-4" id="releasedtoday-container">
                            <div id="releasedtoday-loader" class="loader"></div>
                            <div id="releasedtoday-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                        <!-- Pending Claims Today Card -->
                        <div class="col-lg-3 col-md-6 mb-4" id="pendingtoday-container">
                            <div id="pendingtoday-loader" class="loader"></div>
                            <div id="pendingtoday-content">
                                <!-- Content will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- DataTable Row -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">List Food Registration</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="foodTable" class="table table-bordered table-striped table-hover display nowrap" style="width: 100%;">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Email Address</th>
                                                    <th>First Name</th>
                                                    <th>Middle Name</th>
                                                    <th>Last Name</th>
                                                    <th>Suffix</th>
                                                    <th>Mapping</th>
                                                    <th>Releases</th>
                                                    <th>Release Status</th>
                                                    <th>Actions</th>
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

    <!-- Add Registration Modal -->
    <div class="modal fade" id="addRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="addRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRegistrationModalLabel">Add New Food Registration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="addRegistrationForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="email_address" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email_address" name="email_address" required>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                                <div class="invalid-feedback">Please provide a first name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name" name="middle_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                                <div class="invalid-feedback">Please provide a last name.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="suffix" class="form-label">Suffix</label>
                                <input type="text" class="form-control" id="suffix" name="suffix" placeholder="Jr., Sr., III, etc.">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="mapping" class="form-label">Mapping (Email)</label>
                                <input type="email" class="form-control" id="mapping" name="mapping" placeholder="Optional - email mapping">
                                <small class="form-text text-muted">Leave empty to map later using the Map Registrations feature</small>
                            </div>
                        </div>
                        <div id="formErrorMessage" class="alert alert-danger d-none" role="alert"></div>
                        <div id="formSuccessMessage" class="alert alert-success d-none" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitRegistrationBtn">
                            <i class="fas fa-plus"></i> Add Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Registration Modal -->
    <div class="modal fade" id="editRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="editRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRegistrationModalLabel">Edit Food Registration</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="editRegistrationForm">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_email_address" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email_address" name="email_address" required>
                                <div class="invalid-feedback">Please provide a valid email address.</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                <div class="invalid-feedback">Please provide a first name.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="edit_middle_name" name="middle_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label for="edit_last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                <div class="invalid-feedback">Please provide a last name.</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_suffix" class="form-label">Suffix</label>
                                <input type="text" class="form-control" id="edit_suffix" name="suffix" placeholder="Jr., Sr., III, etc.">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_mapping" class="form-label">Mapping (Email)</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="edit_mapping" name="mapping" placeholder="Optional - email mapping">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="searchMappingBtn" title="Search for mapping">
                                            <i class="fas fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Search using first name or last name to find mapping email</small>
                            </div>
                        </div>
                        
                        <!-- Mapping Search Section -->
                        <div class="row d-none" id="mappingSearchSection">
                            <div class="col-md-12 mb-3">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-search"></i> Search for Mapping</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="searchFirstName" placeholder="First Name">
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <input type="text" class="form-control" id="searchLastName" placeholder="Last Name">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-2">
                                                <button type="button" class="btn btn-primary btn-sm" id="performSearchBtn">
                                                    <i class="fas fa-search"></i> Search
                                                </button>
                                                <button type="button" class="btn btn-secondary btn-sm" id="cancelSearchBtn">
                                                    <i class="fas fa-times"></i> Cancel
                                                </button>
                                            </div>
                                        </div>
                                        <div id="searchResults" class="mt-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="editFormErrorMessage" class="alert alert-danger d-none" role="alert"></div>
                        <div id="editFormSuccessMessage" class="alert alert-success d-none" role="alert"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="updateRegistrationBtn">
                            <i class="fas fa-save"></i> Update Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteRegistrationModal" tabindex="-1" role="dialog" aria-labelledby="deleteRegistrationModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteRegistrationModalLabel">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this food registration?</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                    <div id="deleteRegistrationDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash"></i> Delete Registration
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Release Status Modal -->
    <div class="modal fade" id="releaseStatusModal" tabindex="-1" role="dialog" aria-labelledby="releaseStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="releaseStatusModalLabel">Food Release Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="releaseStatusContent">
                        <div class="text-center">
                            <i class="fas fa-spinner fa-spin"></i> Loading release status...
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

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

            // Show loaders initially for all 4 cards
            const loaderIds = ['totalfood-loader', 'unmapped-loader', 'releasedtoday-loader', 'pendingtoday-loader'];
            loaderIds.forEach(loaderId => {
                const loader = document.getElementById(loaderId);
                if (loader) {
                    loader.style.display = 'block';
                }
            });

            // Function to fetch data using AJAX
            function fetchData() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'foodData', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                
                                // Update all 4 dashboard cards
                                if (data.totalfood) {
                                    updateContainer('totalfood-content', data.totalfood, 'totalfood-loader');
                                }
                                if (data.unmapped) {
                                    updateContainer('unmapped-content', data.unmapped, 'unmapped-loader');
                                }
                                if (data.releasedtoday) {
                                    updateContainer('releasedtoday-content', data.releasedtoday, 'releasedtoday-loader');
                                }
                                if (data.pendingtoday) {
                                    updateContainer('pendingtoday-content', data.pendingtoday, 'pendingtoday-loader');
                                }
                                
                                // Refresh the DataTable if unmapped count changed to keep it in sync
                                if (typeof window.foodTable !== 'undefined' && data.unmapped) {
                                    // Store previous unmapped count to detect changes
                                    if (window.previousUnmappedCount !== data.unmapped.value) {
                                        window.previousUnmappedCount = data.unmapped.value;
                                        // Only reload if table is already initialized and user isn't actively using it
                                        if ($.fn.dataTable.isDataTable('#foodTable')) {
                                            window.foodTable.ajax.reload(null, false); // false = don't reset paging
                                        }
                                    }
                                }
                                
                                // Initialize Feather icons if library is loaded
                                if (typeof feather !== 'undefined') {
                                    feather.replace();
                                }
                            } catch (e) {
                                console.error('Error parsing JSON data:', e);

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

            // Make foodTable global for access in other functions
            window.foodTable = $('#foodTable').DataTable({
                "processing": true,
                "serverSide": true,
                "responsive": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "lengthChange": true,
                "autoWidth": false,
                "ajax": {
                    "url": "fetchFoodData",
                    "type": "POST",
                    "cache": false // Disable caching for AJAX requests
                },
                "columns": [{
                        "data": "id",
                        "title": "ID"
                    },
                    {
                        "data": "email_address",
                        "title": "Email Address"
                    },
                    {
                        "data": "first_name",
                        "title": "First Name"
                    },
                    {
                        "data": "middle_name",
                        "title": "Middle Name"
                    },
                    {
                        "data": "last_name",
                        "title": "Last Name"
                    },
                    {
                        "data": "suffix",
                        "title": "Suffix"
                    },
                    {
                        "data": "mapping",
                        "title": "Mapping"
                    },
                    {
                        "data": "release_summary",
                        "title": "Releases",
                        "render": function(data, type, row, meta) {
                            let badgeClass = 'secondary';
                            if (row.total_releases == 3) badgeClass = 'success';
                            else if (row.total_releases > 0) badgeClass = 'warning';
                            
                            return `<span class="badge badge-${badgeClass}">${data}</span>`;
                        }
                    },
                    {
                        "data": null,
                        "title": "Release Status",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row, meta) {
                            const currentDate = new Date().toISOString().split('T')[0];
                            const conventionDates = ['2025-10-16', '2025-10-17', '2025-10-18'];
                            const isConventionPeriod = conventionDates.includes(currentDate);
                            
                            let statusHtml = '<div class="text-sm">';
                            
                            // Show day statuses
                            const day1Status = row.day1_released ? '<span class="badge badge-success badge-sm">D1 ✓</span>' : '<span class="badge badge-light badge-sm">D1</span>';
                            const day2Status = row.day2_released ? '<span class="badge badge-success badge-sm">D2 ✓</span>' : '<span class="badge badge-light badge-sm">D2</span>';
                            const day3Status = row.day3_released ? '<span class="badge badge-success badge-sm">D3 ✓</span>' : '<span class="badge badge-light badge-sm">D3</span>';
                            
                            statusHtml += day1Status + ' ' + day2Status + ' ' + day3Status;
                            statusHtml += '</div>';
                            
                            return statusHtml;
                        }
                    },
                    {
                        "data": null,
                        "title": "Actions",
                        "orderable": false,
                        "searchable": false,
                        "render": function(data, type, row, meta) {
                            const currentDate = new Date().toISOString().split('T')[0];
                            const conventionDates = ['2025-10-16', '2025-10-17', '2025-10-18'];
                            const isConventionPeriod = conventionDates.includes(currentDate);
                            
                            let actionsHtml = `
                                <div class="btn-group-vertical" role="group">
                                    <button type="button" class="btn btn-sm btn-primary mb-1" 
                                            onclick="editRegistration(${row.id})" 
                                            title="Edit Registration">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                            `;
                            
                            // Show release button only during convention period and if can release today
                            if (isConventionPeriod && row.can_release_today) {
                                actionsHtml += `
                                    <button type="button" class="btn btn-sm btn-success mb-1" 
                                            onclick="releaseFoodToday(${row.id})" 
                                            title="Release Food Today">
                                        <i class="fas fa-utensils"></i> Release
                                    </button>
                                `;
                            }
                            
                            // Show status button
                            actionsHtml += `
                                <button type="button" class="btn btn-sm btn-info mb-1" 
                                        onclick="viewReleaseStatus(${row.id})" 
                                        title="View Release Status">
                                    <i class="fas fa-eye"></i> Status
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" 
                                        onclick="deleteRegistration(${row.id})" 
                                        title="Delete Registration">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                            `;
                            
                            return actionsHtml;
                        }
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
                        text: '<i class="fas fa-plus"></i> Add Registration',
                        titleAttr: 'Add new food registration',
                        className: 'btn btn-info btn-sm',
                        action: function(e, dt, button, config) {
                            openAddRegistrationModal();
                        }
                    },
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
                                url: 'fetchAllFoodData',
                                type: 'POST',
                                success: function(response) {
                                    let exportData = response.data;
                                    var ws = XLSX.utils.json_to_sheet(exportData);
                                    var wb = XLSX.utils.book_new();
                                    XLSX.utils.book_append_sheet(wb, ws, "All Food Data");
                                    XLSX.writeFile(wb, "Food_Registration_Data.xlsx");
                                }
                            });
                        }
                    },
                    {
                        text: '<i class="fas fa-map"></i> Map Registrations',
                        titleAttr: 'Automatically map food registrations with API data',
                        className: 'btn btn-warning btn-sm',
                        action: function(e, dt, button, config) {
                            mapRegistrations();
                        }
                    }
                ]
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

        // Function to map registrations
        async function mapRegistrations() {
            if (!confirm('This will map all unmapped food registrations with API data. Continue?')) {
                return;
            }

            // Show loading indicator
            const button = document.querySelector('.btn-warning');
            const originalText = button.innerHTML;
            
            try {
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mapping...';
                button.disabled = true;

                // First, get all unmapped food records (using GET for internal system)

                const response = await fetch('getUnmappedFoodData', {
                    method: 'GET'
                });

                if (!response.ok) {
                    throw new Error('Failed to fetch unmapped records');
                }

                const unmappedData = await response.json();
                
                if (!unmappedData.success) {
                    throw new Error(unmappedData.message || 'Failed to get unmapped data');
                }

                const unmappedRecords = unmappedData.data;
                let mappedCount = 0;
                let errorCount = 0;

                // Process each unmapped record
                for (const record of unmappedRecords) {
                    try {

                        const apiData = await lookupEmailInAPI(record.email_address);
                        
                        if (apiData && apiData.resultKey === 1 && apiData.resultValue && apiData.resultValue.length > 0) {
                            // Find matching result by email (in case there are multiple results)
                            let matchedResult = apiData.resultValue.find(result => 
                                result.email && result.email.toLowerCase() === record.email_address.toLowerCase()
                            );
                            
                            // If no exact email match, use the first result
                            if (!matchedResult) {
                                matchedResult = apiData.resultValue[0];

                            }
                            
                            // Get the email for mapping
                            const mappingEmail = matchedResult.email || record.email_address;

                            
                            // Update the mapping in the database
                            const updateSuccess = await updateFoodMapping(record.id, mappingEmail);
                            
                            if (updateSuccess) {
                                mappedCount++;

                            } else {
                                errorCount++;

                            }
                        } else {

                            if (apiData) {

                            }
                        }
                        
                        // Small delay to avoid overwhelming the API
                        await new Promise(resolve => setTimeout(resolve, 200));
                        
                    } catch (error) {
                        console.error(`Error processing ${record.email_address}:`, error);
                        errorCount++;
                    }
                }

                // Show results
                alert(`Mapping completed!\nMapped: ${mappedCount}\nErrors: ${errorCount}`);
                
                // Refresh the table using jQuery DataTable reference
                $('#foodTable').DataTable().ajax.reload();

            } catch (error) {
                console.error('Error during mapping:', error);
                alert('Error during mapping: ' + error.message);
            } finally {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            }
        }

        // Function to lookup email in API (External API - uses POST)
        async function lookupEmailInAPI(email) {
            try {
                const apiUrl = `https://psme-api.smartpay.ph/public/index.php/api/postSearch?search=${encodeURIComponent(email)}&event_id=65`;
                

                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer <?php echo $_SESSION['access_token']; ?>'
                    }
                });

                // Handle token renewal if needed
                if (response.status === 401) {

                    const newToken = await renewAccessToken();
                    const retryResponse = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Authorization': `Bearer ${newToken}`
                        }
                    });
                    
                    if (retryResponse.ok) {
                        const result = await retryResponse.json();

                        
                        return result;
                    } else {
                        throw new Error(`API request failed after token renewal: ${retryResponse.status}`);
                    }
                }

                if (response.ok) {
                    const result = await response.json();

                    
                    return result;
                } else {
                    throw new Error(`API request failed: ${response.status}`);
                }
            } catch (error) {
                console.error('API lookup error:', error);
                return null;
            }
        }

        // Function to update food mapping (Internal system - uses GET due to Python HTTP server)
        async function updateFoodMapping(id, mapping) {
            // For internal system calls, use GET method directly due to Python HTTP server limitations

            return await updateFoodMappingGET(id, mapping);
        }

        // Direct database update function (using clean URLs due to .htaccess)
        async function updateFoodMappingGET(id, mapping) {
            try {

                
                // Use POST with JSON data to the main updateFoodMapping endpoint
                const response = await fetch('updateFoodMapping', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: id,
                        mapping: mapping
                    })
                });

                if (response.ok) {
                    const result = await response.json();

                    return result.success;
                } else {
                    console.error('Clean URL update mapping failed:', response.status);
                    const errorText = await response.text();
                    console.error('Error details:', errorText);
                    return false;
                }
            } catch (error) {
                console.error('Clean URL update mapping error:', error);
                return false;
            }
        }

        // Alternative function using XMLHttpRequest
        function updateFoodMappingXHR(id, mapping) {
            return new Promise((resolve) => {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'updateFoodMapping.php', true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('Accept', 'application/json');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const result = JSON.parse(xhr.responseText);

                                resolve(result.success);
                            } catch (e) {
                                console.error('XHR JSON parse error:', e);
                                console.error('XHR Response:', xhr.responseText);
                                resolve(false);
                            }
                        } else {
                            console.error('XHR Update mapping failed:', xhr.status);
                            console.error('XHR Response:', xhr.responseText);
                            resolve(false);
                        }
                    }
                };
                
                xhr.send(JSON.stringify({
                    id: id,
                    mapping: mapping
                }));
            });
        }

        // Function to renew access token (reused from search.php)
        async function renewAccessToken() {
            try {
                const response = await fetch('renewToken');
                if (!response.ok) {
                    throw new Error(`Failed to renew token. Status code: ${response.status}`);
                }
                const data = await response.json();
                if (data.access_token) {
                    return data.access_token;
                } else {
                    throw new Error('Failed to get new token');
                }
            } catch (error) {
                throw error;
            }
        }

        // Function to open the Add Registration modal
        function openAddRegistrationModal() {
            // Clear any previous form data and messages
            clearAddRegistrationForm();
            
            // Show the modal
            $('#addRegistrationModal').modal('show');
        }

        // Function to clear the form and messages
        function clearAddRegistrationForm() {
            document.getElementById('addRegistrationForm').reset();
            document.getElementById('formErrorMessage').classList.add('d-none');
            document.getElementById('formSuccessMessage').classList.add('d-none');
            
            // Remove validation classes
            const formControls = document.querySelectorAll('#addRegistrationForm .form-control');
            formControls.forEach(control => {
                control.classList.remove('is-valid', 'is-invalid');
            });
        }

        // Function to show error message
        function showFormError(message) {
            const errorDiv = document.getElementById('formErrorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            document.getElementById('formSuccessMessage').classList.add('d-none');
        }

        // Function to show success message
        function showFormSuccess(message) {
            const successDiv = document.getElementById('formSuccessMessage');
            successDiv.textContent = message;
            successDiv.classList.remove('d-none');
            document.getElementById('formErrorMessage').classList.add('d-none');
        }

        // Function to validate form fields
        function validateAddRegistrationForm() {
            let isValid = true;
            
            // Required fields
            const requiredFields = ['email_address', 'first_name', 'last_name'];
            
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const value = field.value.trim();
                
                if (!value) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    isValid = false;
                } else {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                }
            });
            
            // Email validation
            const emailField = document.getElementById('email_address');
            const emailValue = emailField.value.trim();
            
            if (emailValue) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailValue)) {
                    emailField.classList.add('is-invalid');
                    emailField.classList.remove('is-valid');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        // Function to submit the registration form
        async function submitAddRegistrationForm(event) {
            event.preventDefault();
            
            // Validate form
            if (!validateAddRegistrationForm()) {
                showFormError('Please fill in all required fields correctly.');
                return;
            }
            
            // Get form data
            const formData = new FormData(event.target);
            const registrationData = {
                email_address: formData.get('email_address').trim(),
                first_name: formData.get('first_name').trim(),
                middle_name: formData.get('middle_name').trim(),
                last_name: formData.get('last_name').trim(),
                suffix: formData.get('suffix').trim(),
                mapping: formData.get('mapping').trim()
            };
            
            // Show loading state
            const submitBtn = document.getElementById('submitRegistrationBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('addFoodRegistration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(registrationData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showFormSuccess(result.message);
                    
                    // Refresh the DataTable and dashboard cards
                    if (window.foodTable && $.fn.dataTable.isDataTable('#foodTable')) {
                        window.foodTable.ajax.reload(null, false);
                    }
                    
                    // Clear form after successful submission
                    setTimeout(() => {
                        clearAddRegistrationForm();
                        $('#addRegistrationModal').modal('hide');
                    }, 1500);
                    
                } else {
                    showFormError(result.message || 'An error occurred while adding the registration.');
                }
                
            } catch (error) {
                console.error('Error submitting registration:', error);
                showFormError('Network error. Please try again.');
            } finally {
                // Restore button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        }

        // Function to edit a registration
        async function editRegistration(id) {
            try {
                // Fetch the registration data
                const response = await fetch(`getFoodRegistration?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    // Populate the edit form
                    populateEditForm(result.data);
                    
                    // Show the edit modal
                    $('#editRegistrationModal').modal('show');
                } else {
                    alert('Error fetching registration data: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching registration:', error);
                alert('Network error while fetching registration data.');
            }
        }

        // Function to populate the edit form with data
        function populateEditForm(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_email_address').value = data.email_address || '';
            document.getElementById('edit_first_name').value = data.first_name || '';
            document.getElementById('edit_middle_name').value = data.middle_name || '';
            document.getElementById('edit_last_name').value = data.last_name || '';
            document.getElementById('edit_suffix').value = data.suffix || '';
            document.getElementById('edit_mapping').value = data.mapping || '';
            
            // Clear any previous validation states
            clearEditFormValidation();
        }

        // Function to clear edit form validation
        function clearEditFormValidation() {
            document.getElementById('editFormErrorMessage').classList.add('d-none');
            document.getElementById('editFormSuccessMessage').classList.add('d-none');
            
            // Hide mapping search section
            document.getElementById('mappingSearchSection').classList.add('d-none');
            document.getElementById('searchFirstName').value = '';
            document.getElementById('searchLastName').value = '';
            document.getElementById('searchResults').innerHTML = '';
            
            const formControls = document.querySelectorAll('#editRegistrationForm .form-control');
            formControls.forEach(control => {
                control.classList.remove('is-valid', 'is-invalid');
            });
        }

        // Function to show edit form error
        function showEditFormError(message) {
            const errorDiv = document.getElementById('editFormErrorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.remove('d-none');
            document.getElementById('editFormSuccessMessage').classList.add('d-none');
        }

        // Function to show edit form success
        function showEditFormSuccess(message) {
            const successDiv = document.getElementById('editFormSuccessMessage');
            successDiv.textContent = message;
            successDiv.classList.remove('d-none');
            document.getElementById('editFormErrorMessage').classList.add('d-none');
        }

        // Function to toggle mapping search section
        function toggleMappingSearch() {
            const searchSection = document.getElementById('mappingSearchSection');
            const isVisible = !searchSection.classList.contains('d-none');
            
            if (isVisible) {
                searchSection.classList.add('d-none');
                // Clear search fields
                document.getElementById('searchFirstName').value = '';
                document.getElementById('searchLastName').value = '';
                document.getElementById('searchResults').innerHTML = '';
            } else {
                searchSection.classList.remove('d-none');
                // Auto-populate from current registration data
                document.getElementById('searchFirstName').value = document.getElementById('edit_first_name').value;
                document.getElementById('searchLastName').value = document.getElementById('edit_last_name').value;
            }
        }

        // Function to perform mapping search
        async function performMappingSearch() {
            const firstName = document.getElementById('searchFirstName').value.trim();
            const lastName = document.getElementById('searchLastName').value.trim();
            
            if (!firstName && !lastName) {
                alert('Please enter at least a first name or last name to search.');
                return;
            }
            
            const searchBtn = document.getElementById('performSearchBtn');
            const originalText = searchBtn.innerHTML;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            searchBtn.disabled = true;
            
            try {
                // Create search term - use name that's provided
                let searchTerm = '';
                if (firstName && lastName) {
                    searchTerm = `${firstName} ${lastName}`;
                } else if (firstName) {
                    searchTerm = firstName;
                } else {
                    searchTerm = lastName;
                }
                
                const apiData = await lookupEmailInAPI(searchTerm);
                displaySearchResults(apiData, firstName, lastName);
                
            } catch (error) {
                console.error('Error during mapping search:', error);
                document.getElementById('searchResults').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error during search: ${error.message}
                    </div>
                `;
            } finally {
                searchBtn.innerHTML = originalText;
                searchBtn.disabled = false;
            }
        }

        // Function to display search results
        function displaySearchResults(apiData, searchFirstName, searchLastName) {
            const resultsDiv = document.getElementById('searchResults');
            
            if (!apiData || apiData.resultKey !== 1 || !apiData.resultValue || apiData.resultValue.length === 0) {
                resultsDiv.innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> No results found for "${searchFirstName} ${searchLastName}".
                    </div>
                `;
                return;
            }
            
            let resultsHtml = `
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Found ${apiData.resultValue.length} result(s):
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            apiData.resultValue.forEach((result, index) => {
                const fullName = `${result.firstname || ''} ${result.lastname || ''}`.trim();
                const email = result.email || 'No email';
                
                resultsHtml += `
                    <tr>
                        <td>${fullName}</td>
                        <td>${email}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="selectMappingEmail('${email}')" 
                                    ${email === 'No email' ? 'disabled' : ''}>
                                <i class="fas fa-check"></i> Use
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            resultsHtml += `
                        </tbody>
                    </table>
                </div>
            `;
            
            resultsDiv.innerHTML = resultsHtml;
        }

        // Function to select mapping email
        function selectMappingEmail(email) {
            if (email && email !== 'No email') {
                document.getElementById('edit_mapping').value = email;
                
                // Hide search section
                document.getElementById('mappingSearchSection').classList.add('d-none');
                
                // Clear search fields
                document.getElementById('searchFirstName').value = '';
                document.getElementById('searchLastName').value = '';
                document.getElementById('searchResults').innerHTML = '';
                
                // Show success message
                const successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success alert-dismissible fade show mt-2';
                successAlert.innerHTML = `
                    <i class="fas fa-check-circle"></i> Mapping email set to: ${email}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                `;
                
                const mappingField = document.getElementById('edit_mapping');
                mappingField.parentNode.appendChild(successAlert);
                
                // Auto-dismiss after 3 seconds
                setTimeout(() => {
                    if (successAlert.parentNode) {
                        successAlert.parentNode.removeChild(successAlert);
                    }
                }, 3000);
            }
        }

        // Function to validate edit form
        function validateEditRegistrationForm() {
            let isValid = true;
            
            // Required fields
            const requiredFields = ['edit_email_address', 'edit_first_name', 'edit_last_name'];
            
            requiredFields.forEach(fieldName => {
                const field = document.getElementById(fieldName);
                const value = field.value.trim();
                
                if (!value) {
                    field.classList.add('is-invalid');
                    field.classList.remove('is-valid');
                    isValid = false;
                } else {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                }
            });
            
            // Email validation
            const emailField = document.getElementById('edit_email_address');
            const emailValue = emailField.value.trim();
            
            if (emailValue) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailValue)) {
                    emailField.classList.add('is-invalid');
                    emailField.classList.remove('is-valid');
                    isValid = false;
                }
            }
            
            return isValid;
        }

        // Function to submit edit form
        async function submitEditRegistrationForm(event) {
            event.preventDefault();
            
            // Validate form
            if (!validateEditRegistrationForm()) {
                showEditFormError('Please fill in all required fields correctly.');
                return;
            }
            
            // Get form data
            const formData = new FormData(event.target);
            const registrationData = {
                id: parseInt(formData.get('id')),
                email_address: formData.get('email_address').trim(),
                first_name: formData.get('first_name').trim(),
                middle_name: formData.get('middle_name').trim(),
                last_name: formData.get('last_name').trim(),
                suffix: formData.get('suffix').trim(),
                mapping: formData.get('mapping').trim()
            };
            
            // Show loading state
            const submitBtn = document.getElementById('updateRegistrationBtn');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('updateFoodRegistration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(registrationData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showEditFormSuccess(result.message);
                    
                    // Refresh the DataTable
                    if (window.foodTable && $.fn.dataTable.isDataTable('#foodTable')) {
                        window.foodTable.ajax.reload(null, false);
                    }
                    
                    // Close modal after successful submission
                    setTimeout(() => {
                        clearEditFormValidation();
                        $('#editRegistrationModal').modal('hide');
                    }, 1500);
                    
                } else {
                    showEditFormError(result.message || 'An error occurred while updating the registration.');
                }
                
            } catch (error) {
                console.error('Error updating registration:', error);
                showEditFormError('Network error. Please try again.');
            } finally {
                // Restore button state
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            }
        }

        // Function to delete a registration
        async function deleteRegistration(id) {
            try {
                // First get registration details for confirmation
                const response = await fetch(`getFoodRegistration?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    const registration = result.data;
                    
                    // Show registration details in delete modal
                    const detailsDiv = document.getElementById('deleteRegistrationDetails');
                    detailsDiv.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Registration Details:</h6>
                                <p class="card-text">
                                    <strong>ID:</strong> ${registration.id}<br>
                                    <strong>Name:</strong> ${registration.first_name} ${registration.middle_name || ''} ${registration.last_name} ${registration.suffix || ''}<br>
                                    <strong>Email:</strong> ${registration.email_address}<br>
                                    <strong>Mapping:</strong> ${registration.mapping || 'Not mapped'}
                                </p>
                            </div>
                        </div>
                    `;
                    
                    // Store the ID for deletion
                    document.getElementById('confirmDeleteBtn').setAttribute('data-id', id);
                    
                    // Show the delete modal
                    $('#deleteRegistrationModal').modal('show');
                } else {
                    alert('Error fetching registration data: ' + result.message);
                }
            } catch (error) {
                console.error('Error fetching registration for delete:', error);
                alert('Network error while fetching registration data.');
            }
        }

        // Function to confirm deletion
        async function confirmDelete() {
            const deleteBtn = document.getElementById('confirmDeleteBtn');
            const id = parseInt(deleteBtn.getAttribute('data-id'));
            
            if (!id) {
                alert('Invalid registration ID');
                return;
            }
            
            // Show loading state
            const originalBtnText = deleteBtn.innerHTML;
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
            deleteBtn.disabled = true;
            
            try {
                const response = await fetch('deleteFoodRegistration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Close the modal
                    $('#deleteRegistrationModal').modal('hide');
                    
                    // Show success message
                    alert('Registration deleted successfully');
                    
                    // Refresh the DataTable
                    if (window.foodTable && $.fn.dataTable.isDataTable('#foodTable')) {
                        window.foodTable.ajax.reload(null, false);
                    }
                    
                } else {
                    alert('Error deleting registration: ' + result.message);
                }
                
            } catch (error) {
                console.error('Error deleting registration:', error);
                alert('Network error while deleting registration.');
            } finally {
                // Restore button state
                deleteBtn.innerHTML = originalBtnText;
                deleteBtn.disabled = false;
            }
        }

        // Function to release food for today
        async function releaseFoodToday(id) {
            if (!confirm('Release food to this registrant for today?')) {
                return;
            }
            
            try {
                const response = await fetch('releaseFoodToday', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ food_id: id })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(`${result.message}\n\nRegistrant: ${result.data.registrant_name}\nRelease Time: ${result.data.release_time}`);
                    
                    // Refresh the DataTable
                    if (window.foodTable && $.fn.dataTable.isDataTable('#foodTable')) {
                        window.foodTable.ajax.reload(null, false);
                    }
                } else {
                    alert('Error releasing food: ' + result.message);
                }
                
            } catch (error) {
                console.error('Error releasing food:', error);
                alert('Network error while releasing food.');
            }
        }

        // Function to view release status
        async function viewReleaseStatus(id) {
            try {
                // Store the current registrant ID for later use
                window.currentRegistrantId = id;
                
                // Show loading in modal
                document.getElementById('releaseStatusContent').innerHTML = `
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading release status...
                    </div>
                `;
                
                // Show the modal
                $('#releaseStatusModal').modal('show');
                
                const response = await fetch(`getFoodReleaseStatus?id=${id}`);
                const result = await response.json();
                
                if (result.success) {
                    displayReleaseStatus(result.data);
                } else {
                    document.getElementById('releaseStatusContent').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Error: ${result.message}
                        </div>
                    `;
                }
                
            } catch (error) {
                console.error('Error fetching release status:', error);
                document.getElementById('releaseStatusContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> Network error while fetching release status.
                    </div>
                `;
            }
        }

        // Function to display release status in modal
        function displayReleaseStatus(data) {
            const registrant = data.registrant;
            const summary = data.release_summary;
            const dailyStatus = data.daily_status;
            const history = data.release_history;
            
            let statusHtml = `
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-user"></i> Registrant Information</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Name:</strong></td><td>${registrant.full_name}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${registrant.email_address}</td></tr>
                            <tr><td><strong>Mapping:</strong></td><td>${registrant.mapping || 'Not mapped'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-chart-pie"></i> Release Summary</h6>
                        <table class="table table-sm">
                            <tr><td><strong>Total Releases:</strong></td><td><span class="badge badge-primary">${summary.total_releases}/3</span></td></tr>
                            <tr><td><strong>Current Date:</strong></td><td>${summary.current_date}</td></tr>
                            <tr><td><strong>Can Release Today:</strong></td><td>${summary.can_release_today ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>'}</td></tr>
                            <tr><td><strong>Days Remaining:</strong></td><td>${summary.convention_days_remaining}</td></tr>
                        </table>
                    </div>
                </div>
                
                <hr>
                
                <h6><i class="fas fa-calendar-alt"></i> Daily Release Status</h6>
                <div class="row">
            `;
            
            Object.keys(dailyStatus).forEach(day => {
                const dayData = dailyStatus[day];
                const statusBadge = dayData.released ? 
                    `<span class="badge badge-success"><i class="fas fa-check"></i> Released</span>` : 
                    `<span class="badge badge-light">Not Released</span>`;
                
                const releaseTime = dayData.released ? 
                    `<small class="text-muted">Released: ${new Date(dayData.release_time).toLocaleString()}</small>` : 
                    `<small class="text-muted">Not released yet</small>`;
                
                const canReleaseToday = dayData.can_release ? 
                    `<br><button class="btn btn-sm btn-success mt-1" onclick="releaseFoodToday(${registrant.id})">
                        <i class="fas fa-utensils"></i> Release Now
                    </button>` : '';
                
                statusHtml += `
                    <div class="col-md-4">
                        <div class="card mb-2">
                            <div class="card-body p-2">
                                <h6 class="card-title">${day.toUpperCase()} - ${dayData.date}</h6>
                                ${statusBadge}<br>
                                ${releaseTime}
                                ${canReleaseToday}
                            </div>
                        </div>
                    </div>
                `;
            });
            
            statusHtml += `</div>`;
            
            // Add release history if available
            if (history && history.length > 0) {
                statusHtml += `
                    <hr>
                    <h6><i class="fas fa-history"></i> Release History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Day</th>
                                    <th>Released By</th>
                                    <th>Notes</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                history.forEach(release => {
                    statusHtml += `
                        <tr>
                            <td>${new Date(release.release_date).toLocaleDateString()}</td>
                            <td>${new Date(release.release_time).toLocaleTimeString()}</td>
                            <td><span class="badge badge-info">${release.convention_day}</span></td>
                            <td>${release.released_by_username}</td>
                            <td><small>${release.notes || 'No notes'}</small></td>
                            <td>
                                <button class="btn btn-danger btn-sm" 
                                        onclick="deleteFoodRelease(${release.release_id})" 
                                        title="Delete this release record">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                statusHtml += `
                            </tbody>
                        </table>
                    </div>
                `;
            }
            
            document.getElementById('releaseStatusContent').innerHTML = statusHtml;
        }

        // Initialize form submission handler when document is ready
        $(document).ready(function() {
            // Add form submission handler for add form
            document.getElementById('addRegistrationForm').addEventListener('submit', submitAddRegistrationForm);
            
            // Add form submission handler for edit form
            document.getElementById('editRegistrationForm').addEventListener('submit', submitEditRegistrationForm);
            
            // Add delete confirmation handler
            document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDelete);
            
            // Add mapping search event listeners
            document.getElementById('searchMappingBtn').addEventListener('click', toggleMappingSearch);
            document.getElementById('performSearchBtn').addEventListener('click', performMappingSearch);
            document.getElementById('cancelSearchBtn').addEventListener('click', function() {
                document.getElementById('mappingSearchSection').classList.add('d-none');
                document.getElementById('searchFirstName').value = '';
                document.getElementById('searchLastName').value = '';
                document.getElementById('searchResults').innerHTML = '';
            });
            
            // Add Enter key support for search fields
            document.getElementById('searchFirstName').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performMappingSearch();
                }
            });
            
            document.getElementById('searchLastName').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performMappingSearch();
                }
            });
            
            // Add real-time validation for add form
            const addFormFields = document.querySelectorAll('#addRegistrationForm .form-control');
            addFormFields.forEach(field => {
                field.addEventListener('blur', function() {
                    validateFormField(this);
                });
            });
            
            // Add real-time validation for edit form
            const editFormFields = document.querySelectorAll('#editRegistrationForm .form-control');
            editFormFields.forEach(field => {
                field.addEventListener('blur', function() {
                    validateFormField(this);
                });
            });
        });

        // Helper function for field validation
        function validateFormField(field) {
            if (field.hasAttribute('required') && !field.value.trim()) {
                field.classList.add('is-invalid');
                field.classList.remove('is-valid');
            } else if (field.value.trim()) {
                // Special validation for email
                if (field.type === 'email') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (emailRegex.test(field.value.trim())) {
                        field.classList.add('is-valid');
                        field.classList.remove('is-invalid');
                    } else {
                        field.classList.add('is-invalid');
                        field.classList.remove('is-valid');
                    }
                } else {
                    field.classList.add('is-valid');
                    field.classList.remove('is-invalid');
                }
            }
        }

        // Function to delete food release record
        async function deleteFoodRelease(releaseId) {
            // Confirm deletion
            if (!confirm('Are you sure you want to delete this food release record? This action cannot be undone.')) {
                return;
            }
            
            try {
                const response = await fetch('deleteFoodRelease', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        release_id: releaseId
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Show success message
                    alert('Food release record deleted successfully.');
                    
                    // Refresh the modal by re-checking the current registrant
                    const currentRegistrantId = window.currentRegistrantId;
                    if (currentRegistrantId) {
                        await viewReleaseStatus(currentRegistrantId);
                    }
                    
                    // Refresh the main data table
                    $('#foodTable').DataTable().ajax.reload(null, false);
                } else {
                    alert('Error deleting food release record: ' + (data.message || 'Unknown error'));
                }
                
            } catch (error) {
                console.error('Error:', error);
                alert('Network error while deleting food release record: ' + error.message);
            }
        }


    </script>

</body>

</html>