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

    <title>Check In | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <script src="js/html5-qrcode.min.js"></script>
    <style>
        #loading,
        #waitingMessage {
            animation: breathing 1.5s ease-in-out infinite;
        }

        @keyframes breathing {

            0%,
            100% {
                opacity: 0.6;
            }

            50% {
                opacity: 1;
            }
        }

        /* QR Reader container styles */
        #html5qr-reader {
            width: 100% !important;
            max-width: 500px;
            margin: 5px auto;
            padding: 5px;
            border-radius: 8px;
            overflow: hidden;
            border: 2px solid #28a745;
        }

        #html5qr-reader video {
            width: 100% !important;
            height: auto !important;
            border-radius: 6px;
        }

        #html5qr-reader canvas {
            display: none;
            /* Hide the canvas overlay for cleaner UI */
        }

        /* Ensure the QR scanner region has minimum dimensions */
        #html5qr-reader>div {
            min-width: 300px !important;
            min-height: 200px !important;
            margin: 0 auto;
        }

        /* Mobile-specific improvements */
        @media (max-width: 768px) {
            #html5qr-reader {
                max-width: 95%;
                margin: 5px auto;
                padding: 5px;
                border-radius: 6px;
            }

            #html5qr-reader>div {
                min-width: 250px !important;
                min-height: 200px !important;
                margin: 0 auto;
            }

            /* Ensure scanner controls are touch-friendly on mobile */
            #html5qr-reader button {
                padding: 12px 16px !important;
                font-size: 16px !important;
                min-height: 44px !important;
                margin: 5px !important;
            }

            #html5qr-reader select {
                padding: 12px !important;
                font-size: 16px !important;
                min-height: 44px !important;
                margin: 5px 0 !important;
            }
        }

        /* Very small screens (phones in portrait) */
        @media (max-width: 480px) {
            #html5qr-reader {
                max-width: 90%;
                margin: 5px auto;
                padding: 3px;
            }

            #html5qr-reader>div {
                min-width: 200px !important;
                min-height: 150px !important;
                margin: 0 auto;
            }
        }

        /* Scan success animation */
        .scan-success-flash {
            animation: successFlash 0.5s ease-in-out;
        }

        @keyframes successFlash {
            0% {
                background-color: transparent;
            }

            50% {
                background-color: rgba(40, 167, 69, 0.3);
            }

            100% {
                background-color: transparent;
            }
        }

        /* Success alert styling */
        .alert-success {
            border-left: 4px solid #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        /* Pulse animation for scanning indicator */
        .scanning-indicator {
            animation: pulse 1.5s ease-in-out infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 0.7;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.05);
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

                    <!-- Page Heading 
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Scan</h1>
                    </div>-->

                    <div class="row">

                        <div class="col-lg-12">

                            <!-- Dropdown for Scan Method -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Choose Scan Method</h6>
                                    <!-- HTTPS Status Indicator -->
                                    <div id="httpsStatus" class="mt-2"></div>
                                </div>
                                <div class="card-body">
                                    <label for="scanMethod">Choose Scan Method:</label>
                                    <select id="scanMethod" class="form-control">
                                        <option value="" disabled selected>Select a method</option>
                                        <option value="html5qr">Html5-QRCode (Camera)</option>
                                        <option value="physical">Physical QR Code Scanner</option>
                                    </select>

                                    <!-- QR Reader will be populated by Html5QrcodeScanner -->
                                    <div id="html5qr-reader" style="width: 100%; display: none;" class="mt-3"></div>
                                    <input type="text" id="physicalScannerInput" style="display: none;"
                                        class="form-control mt-3" placeholder="Scan QR code here">
                                    <!-- Breathing effect message -->
                                    <div id="waitingMessage" style="display: none;" class="mt-4">Waiting for scan
                                        input...</div>
                                    <div id="loading" style="display: none;" class="mt-4">Loading, please wait...</div>
                                    <p id="result" class="mt-3 mb-0"></p>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Modal -->
                    <div class="modal fade" id="scanResultModal" tabindex="-1" role="dialog"
                        aria-labelledby="scanResultModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="scanResultModalLabel">Scan Result</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">Ã—</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>First Name:</strong> <span id="modalFirstname"></span></p>
                                    <p><strong>Last Name:</strong> <span id="modalLastname"></span></p>
                                    <p><strong>Middle Name</strong> <span id="modalMiddlename"></span></p>
                                    <p><small class="text-muted">(N/A middle name will not be printed)</small></p>
                                    <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                                    <p><strong>PRC License Number:</strong> <span id="modalPrclicensenumber"></span></p>
                                    <p><strong>Chapter:</strong> <span id="modalChapter"></span></p>
                                    <p><strong>QR Code:</strong> <a id="modalQrcodeLink" href="" target="_blank">QR
                                            Code</a></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" onclick="printLabel()">Print
                                        Only</button>
                                    <button type="button" class="btn btn-primary" onclick="editBeforePrint()">Edit
                                        before Print</button>
                                    <button type="button" class="btn btn-success" onclick="checkIn()">Check In</button>
                                    <button type="button" class="btn btn-warning"
                                        onclick="releaseKit(document.getElementById('modalEmail').innerText)">Release
                                        Kit Only</button>
                                    <button type="button" class="btn btn-warning"
                                        onclick="releaseMSP(document.getElementById('modalEmail').innerText)">Release
                                        MSP Only</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Before Print Modal -->
                    <div class="modal fade" id="editPrintModal" tabindex="-1" role="dialog"
                        aria-labelledby="editPrintModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editPrintModalLabel">Edit Information Before Print</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">Ã—</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <form id="editForm">
                                        <div class="form-group">
                                            <label for="editFirstname">First Name:</label>
                                            <input type="text" class="form-control" id="editFirstname">
                                        </div>
                                        <div class="form-group">
                                            <label for="editLastname">Last Name:</label>
                                            <input type="text" class="form-control" id="editLastname">
                                        </div>
                                        <div class="form-group">
                                            <label for="editMiddlename">Middle Name: <small class="text-muted">(N/A will
                                                    not be printed)</small></label>
                                            <input type="text" class="form-control" id="editMiddlename">
                                        </div>
                                        <div class="form-group">
                                            <label for="editChapter">Chapter:</label>
                                            <input type="text" class="form-control" id="editChapter">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary"
                                        onclick="saveAndPrint()">Print</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Check-In and Release Kit Result Modal -->
                <div class="modal fade" id="checkinResultModal" tabindex="-1" role="dialog"
                    aria-labelledby="checkinResultModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="checkinResultModalLabel">Result</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">Ã—</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p id="checkinResultMessage"></p>
                                <p id="kitReleaseStatus"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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

    <script src="js/checkin.js"></script>


</body>

</html>
