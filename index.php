<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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

    <title>Dashboard | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <!-- Feather Icons -->
    <script src="vendor/feather-icons/feather.min.js"></script>

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

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
                    </div>

                    <?php if (!hasRole('Food Distributor')) { ?>
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

                            <!-- Scan Button -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <a href="checkin" class="card border-left-primary shadow h-100 py-2 text-decoration-none">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Scan QR Code</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Search Registration
                                                    using QR Code</div>
                                            </div>
                                            <div class="col-auto">
                                                <i data-feather="camera" class="feather-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- Search Button -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <a href="search" class="card border-left-secondary shadow h-100 py-2 text-decoration-none">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                    Search</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Search Registration
                                                    using Name, Email, or PRC ID</div>
                                            </div>
                                            <div class="col-auto">
                                                <i data-feather="search" class="feather-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <!-- View Button -->
                            <div class="col-xl-6 col-md-6 mb-4">
                                <a href="list_checkins"
                                    class="card border-left-success shadow h-100 py-2 text-decoration-none">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                    Check-In</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">Manage Check-Ins</div>
                                            </div>
                                            <div class="col-auto">
                                                <i data-feather="check-circle" class="feather-icon"></i>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                    <?php } ?>

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

    <!-- Initialize Feather Icons -->
    <script>
        // Initialize Feather icons when page loads
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof feather !== 'undefined') {
                feather.replace();
            }
        });
    </script>

    <script>
        // Wait for both DOM and all resources to load
        window.addEventListener('load', function () {
            // Double-check that elements exist with a slight delay
            setTimeout(function () {
                initializeDashboard();
            }, 100);
        });

        function initializeDashboard() {
            // Check if required elements exist
            const checkinsContainer = document.getElementById("checkins-container");
            const kitsContainer = document.getElementById("kits-container");
            const checkinsContent = document.getElementById("checkins-content");
            const kitsContent = document.getElementById("kits-content");
            const checkinsLoader = document.getElementById("checkins-loader");
            const kitsLoader = document.getElementById("kits-loader");

            console.log('Checking elements:', {
                checkinsContainer: !!checkinsContainer,
                kitsContainer: !!kitsContainer,
                checkinsContent: !!checkinsContent,
                kitsContent: !!kitsContent,
                checkinsLoader: !!checkinsLoader,
                kitsLoader: !!kitsLoader
            });

            if (!checkinsContainer || !kitsContainer || !checkinsContent || !kitsContent || !checkinsLoader || !kitsLoader) {
                console.error('Required DOM elements not found');
                return;
            }

            // Show loaders initially
            checkinsLoader.style.display = 'block';
            kitsLoader.style.display = 'block';

            // Function to fetch data using AJAX
            function fetchData() {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'data', true); // Use clean URL without .php extension
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        if (xhr.status === 200) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                console.log('Data received:', data);

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

            // Fetch data initially and then every 5 seconds
            fetchData(); // Initial fetch
            setInterval(fetchData, 5000); // Polling every 5 seconds
        }
    </script>
</body>

</html>
