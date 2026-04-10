<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

include 'functions.php';
include 'test_config.php';

// Set timezone to Asia/Manila
date_default_timezone_set('Asia/Manila');
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Release Food by QR Scan | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

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
            0%, 100% {
                opacity: 0.7;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
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
        }

        #html5qr-reader > div {
            min-width: 300px !important;
            min-height: 200px !important;
            margin: 0 auto;
        }

        /* Mobile-specific improvements */
        @media (max-width: 768px) {
            #html5qr-reader {
                max-width: 100%;
                margin: 10px 0;
            }

            #html5qr-reader > div {
                min-width: 250px !important;
                min-height: 180px !important;
            }

            #html5qr-reader button {
                padding: 8px 12px;
                font-size: 14px;
                margin: 2px;
            }

            #html5qr-reader select {
                padding: 6px;
                font-size: 14px;
                margin: 2px;
            }
        }

        /* Very small screens (phones in portrait) */
        @media (max-width: 480px) {
            #html5qr-reader {
                margin: 5px 0;
                padding: 3px;
            }

            #html5qr-reader > div {
                min-width: 200px !important;
                min-height: 150px !important;
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
            0%, 100% {
                opacity: 0.6;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
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

                    <?php displayTestModeBanner(); ?>

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-qrcode"></i> Release Food by QR Scan
                        </h1>
                        <div class="btn-group" role="group">
                            <a href="search_food_release" class="btn btn-info btn-sm">
                                <i class="fas fa-search"></i> Release by Search
                            </a>
                            <a href="list_food" class="btn btn-primary btn-sm">
                                <i class="fas fa-list"></i> Food Distribution List
                            </a>
                        </div>
                    </div>

                    <!-- Convention Status Card -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="fas fa-calendar-alt"></i> Convention Status
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Current Date:</strong> <?php echo getCurrentDate() . ' (' . date('F j, Y', strtotime(getCurrentDate())) . ')'; ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Convention Status:</strong>
                                                <?php if (isConventionDay()): ?>
                                                    <span class="badge badge-success">Day <?php echo getConventionDay(); ?> - Active</span>
                                                <?php elseif (getCurrentDate() < '2025-10-16'): ?>
                                                    <span class="badge badge-info">Pre-Convention</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Post-Convention</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Food Release:</strong>
                                                <?php if (isConventionDay()): ?>
                                                    <span class="badge badge-success">AVAILABLE</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">NOT AVAILABLE</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <?php if (!isConventionDay()): ?>
                                        <div class="alert alert-info mt-3">
                                            <i class="fas fa-info-circle"></i> 
                                            Food release is only available during convention dates (October 16-18, 2025).
                                            <?php if (isTestMode()): ?>
                                                <br><small>Use <a href="test_mode_controls">Test Mode Controls</a> to simulate convention dates for testing.</small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Scanner Card -->
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-success">
                                        <i class="fas fa-camera"></i> QR Code Scanner
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <?php if (isConventionDay()): ?>
                                        <div class="text-center mb-4">
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-success" onclick="startHtml5QrCode()">
                                                    <i class="fas fa-play"></i> Start Camera
                                                </button>
                                                <button type="button" class="btn btn-secondary" onclick="stopHtml5QrCode()">
                                                    <i class="fas fa-stop"></i> Stop Camera
                                                </button>
                                            </div>
                                        </div>

                                        <!-- HTTPS Status Indicator -->
                                        <div id="httpsStatus" class="text-center mb-3"></div>

                                        <!-- QR Code Reader -->
                                        <div id="html5qr-reader" style="display: none;"></div>

                                        <!-- Result Display -->
                                        <div id="result" class="mt-3 text-center">
                                            <span class="text-muted">
                                                <i class="fas fa-qrcode mr-2"></i>
                                                Click "Start Camera" to begin scanning QR codes for food release
                                            </span>
                                        </div>

                                        <!-- Loading Indicator -->
                                        <div id="loading" style="display: none;" class="text-center mt-3">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            <p class="mt-2"><strong>Processing QR Code...</strong></p>
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-warning text-center">
                                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                                            <h5>Food Release Not Available</h5>
                                            <p>Food release is only available during convention dates (October 16-18, 2025).</p>
                                            <?php if (hasRole('admin')): ?>
                                                <a href="test_mode_controls" class="btn btn-info">
                                                    <i class="fas fa-flask"></i> Test Mode Controls
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
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

    <!-- Food Release Confirmation Modal -->
    <div class="modal fade" id="foodReleaseModal" tabindex="-1" role="dialog" aria-labelledby="foodReleaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="foodReleaseModalLabel">
                        <i class="fas fa-utensils"></i> Food Release Confirmation
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-user"></i> Registrant Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Name:</strong></td><td id="modalName">-</td></tr>
                                <tr><td><strong>Email:</strong></td><td id="modalEmail">-</td></tr>
                                <tr><td><strong>Chapter:</strong></td><td id="modalChapter">-</td></tr>
                                <tr><td><strong>PRC License:</strong></td><td id="modalPrcLicense">-</td></tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-calendar-alt"></i> Release Information</h6>
                            <table class="table table-sm">
                                <tr><td><strong>Release Date:</strong></td><td id="modalReleaseDate">-</td></tr>
                                <tr><td><strong>Convention Day:</strong></td><td id="modalConventionDay">-</td></tr>
                                <tr><td><strong>Released By:</strong></td><td><?php echo htmlspecialchars($username); ?></td></tr>
                            </table>
                        </div>
                    </div>
                    
                    <div id="releaseStatusSection" class="mt-3">
                        <!-- Release status will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="confirmReleaseBtn" onclick="confirmFoodRelease()">
                        <i class="fas fa-utensils"></i> Confirm Release Food
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Food Release Result Modal -->
    <div class="modal fade" id="foodReleaseResultModal" tabindex="-1" role="dialog" aria-labelledby="foodReleaseResultModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="foodReleaseResultModalLabel">
                        <i class="fas fa-utensils"></i> Food Release Result
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="foodReleaseResultMessage">
                        <!-- Result message will be populated here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="resetScanner()">
                        <i class="fas fa-qrcode"></i> Scan Another QR Code
                    </button>
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

    <script>
        // Check HTTPS status and display indicator
        function checkHttpsStatus() {
            const httpsStatusDiv = document.getElementById('httpsStatus');
            const isHttps = window.location.protocol === 'https:';
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            const isSecureContext = window.isSecureContext;

            if (isHttps) {
                httpsStatusDiv.innerHTML = '<small class="text-success"><i class="fas fa-lock mr-1"></i>Secure HTTPS - Camera access available</small>';
            } else if (isLocalhost && isSecureContext) {
                httpsStatusDiv.innerHTML = '<small class="text-info"><i class="fas fa-home mr-1"></i>Localhost secure context - Camera should work</small>';
            } else if (isLocalhost) {
                httpsStatusDiv.innerHTML = '<small class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Localhost - Camera may work depending on browser</small>';
            } else {
                httpsStatusDiv.innerHTML = '<small class="text-danger"><i class="fas fa-times mr-1"></i>HTTPS required for camera access</small>';
            }
        }

        // Initialize HTTPS status check
        checkHttpsStatus();

        let html5QrcodeScanner = null;
        let currentRegistrantData = null;

        async function startHtml5QrCode() {
            try {
                console.log('Starting Html5QrcodeScanner...');

                // Stop any existing scanner first
                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                }

                // Clear the reader div
                const readerDiv = document.getElementById('html5qr-reader');
                if (readerDiv) {
                    readerDiv.innerHTML = '';
                    readerDiv.style.display = 'block';
                }

                // Configure Html5QrcodeScanner with mobile-friendly settings
                const config = {
                    fps: 10,
                    qrbox: function (viewfinderWidth, viewfinderHeight) {
                        // Make qrbox responsive - use 70% of the smaller dimension for mobile
                        const minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
                        const qrboxSize = Math.floor(minEdgeSize * 0.7);
                        return {
                            width: qrboxSize,
                            height: qrboxSize
                        };
                    },
                    rememberLastUsedCamera: true,
                    // Mobile-friendly camera preferences
                    videoConstraints: {
                        facingMode: "environment" // Prefer back camera on mobile
                    }
                };

                console.log('Creating Html5QrcodeScanner with config:', config);

                // Create new Html5QrcodeScanner instance
                html5QrcodeScanner = new Html5QrcodeScanner(
                    "html5qr-reader",
                    config,
                    false
                );

                // Start scanning with success and error callbacks
                html5QrcodeScanner.render(onScanSuccess, onScanFailure);

                console.log('Html5QrcodeScanner started successfully');

                // Update result message
                document.getElementById('result').innerHTML = '<span style="color: green;"><i class="fas fa-check-circle mr-2"></i>QR Code scanner ready! Point your camera at a QR code to scan.</span>';

            } catch (err) {
                console.error('Error starting Html5QrcodeScanner:', err);
                let errorMessage = 'Scanner error: ' + err.message;

                if (err.name === 'NotAllowedError') {
                    errorMessage = 'Camera permission denied. Please allow camera access in your browser settings and try again.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage = 'No camera found on this device.';
                } else if (err.name === 'NotReadableError') {
                    errorMessage = 'Camera is being used by another application. Please close other applications using the camera and try again.';
                }

                document.getElementById('result').innerHTML = `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>${errorMessage}</span>`;
            }
        }

        async function stopHtml5QrCode() {
            try {
                console.log('Stopping Html5QrcodeScanner...');

                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                }

                // Clear the reader div and hide it
                const readerDiv = document.getElementById('html5qr-reader');
                if (readerDiv) {
                    readerDiv.innerHTML = '';
                    readerDiv.style.display = 'none';
                }

                document.getElementById('result').innerHTML = '<span style="color: green;"><i class="fas fa-check-circle mr-2"></i>Camera stopped successfully.</span>';

                console.log('Scanner stopped and UI updated');

            } catch (err) {
                console.error('Error stopping scanner:', err);
                document.getElementById('result').innerHTML = `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>Error stopping camera: ${err.message}</span>`;
            }
        }

        // Enhanced success sound function
        function playSuccessSound() {
            try {
                const sounds = [
                    'data:audio/wav;base64,UklGRpkBAABXQVZFZm10IBAAAAABAAEBVUDQAAKEAQBAAAAAAGF0AAAAAAAAAAAAAAAAAAAA',
                    'data:audio/mpeg;base64,//MQxAANQC8AAMAQAImNcCwAAMAQAA5AwAABhgBQADAREADAQAAAx1DAmAYAAAGA='
                ];

                const audio = new Audio(sounds[0]);
                audio.volume = 0.3;

                const playPromise = audio.play();

                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log('ðŸ”Š Success sound played');
                    }).catch(error => {
                        console.log('ðŸ”‡ Audio autoplay prevented:', error.message);
                        playWebAudioBeep();
                    });
                }
            } catch (error) {
                console.log('ðŸ”‡ Audio not supported:', error.message);
                playWebAudioBeep();
            }
        }

        // Fallback Web Audio API beep
        function playWebAudioBeep() {
            try {
                if ('AudioContext' in window || 'webkitAudioContext' in window) {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.value = 800;
                    oscillator.type = 'sine';
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.1);
                }
            } catch (error) {
                console.log('ðŸ”‡ Web Audio not supported:', error.message);
                if ('vibrate' in navigator) {
                    navigator.vibrate(200);
                }
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log('ðŸŽ‰ QR CODE SCANNED SUCCESSFULLY!');
            console.log('Decoded text:', decodedText);

            // Immediate visual feedback
            document.getElementById('result').innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i><strong>QR Code Scanned!</strong><br>Processing: ' + decodedText + '</div>';

            // Play success sound
            playSuccessSound();

            // Visual flash effect
            const reader = document.getElementById('html5qr-reader');
            if (reader) {
                reader.style.border = '4px solid #28a745';
                reader.style.boxShadow = '0 0 20px #28a745';
                setTimeout(() => {
                    reader.style.border = '2px solid #28a745';
                    reader.style.boxShadow = 'none';
                }, 1000);
            }

            // Process the scanned code for food release
            // First check if it's an internal ICTO QR code
            if (decodedText.startsWith("ICTO-")) {
                fetchFoodRegistrantDetails(decodedText);
            } else {
                // For external QR codes, use getScanData API
                fetchExternalRegistrantDetails(decodedText);
            }

            // Stop the QR code scanner after a successful scan
            try {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.pause(true);
                }
            } catch (err) {
                console.error(`Error pausing QR Code scanner: ${err}`);
            }
        }

        async function fetchFoodRegistrantDetails(qrCode) {
            try {
                showLoadingIndicator();

                const response = await fetch('ictoqr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `qrcode=${encodeURIComponent(qrCode)}`
                });

                const data = await response.json();

                hideLoadingIndicator();

                if (data.resultKey === 1 && data.resultValue && data.resultValue.length > 0) {
                    const registrant = data.resultValue[0];
                    await lookupFoodRegistration(registrant);
                } else {
                    document.getElementById('result').innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>QR Code Not Found!</strong><br>This QR code is not registered in the system.</div>`;
                    setTimeout(resetScanner, 3000);
                }
            } catch (error) {
                hideLoadingIndicator();
                console.error('Error fetching QR details:', error);
                document.getElementById('result').innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Network Error!</strong><br>Unable to process QR code: ${error.message}</div>`;
                setTimeout(resetScanner, 3000);
            }
        }

        async function fetchExternalRegistrantDetails(qrCode) {
            try {
                showLoadingIndicator();

                const response = await fetch('getScanData', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `qrcode=${encodeURIComponent(qrCode)}`
                });

                const data = await response.json();

                hideLoadingIndicator();

                if (data.resultKey === 1 && data.resultValue && data.resultValue.length > 0) {
                    const registrant = data.resultValue[0];
                    await lookupFoodRegistration(registrant);
                } else {
                    document.getElementById('result').innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>QR Code Not Found!</strong><br>This QR code is not registered in the external system.</div>`;
                    setTimeout(resetScanner, 3000);
                }
            } catch (error) {
                hideLoadingIndicator();
                console.error('Error fetching external QR details:', error);
                document.getElementById('result').innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Network Error!</strong><br>Unable to process external QR code: ${error.message}</div>`;
                setTimeout(resetScanner, 3000);
            }
        }

        async function lookupFoodRegistration(registrant) {
            try {
                const email = registrant.email;
                
                // Look up food registration using the email in the mapping column
                const response = await fetch(`getFoodReleaseStatus?mapping=${encodeURIComponent(email)}`);
                const result = await response.json();

                if (result.success && result.data && result.data.registrant) {
                    // Found food registration
                    const foodRegistrant = result.data.registrant;
                    currentRegistrantData = {
                        food_id: foodRegistrant.id,
                        qr_registrant: registrant,
                        food_registrant: foodRegistrant,
                        release_data: result.data
                    };

                    // Populate the confirmation modal
                    populateFoodReleaseModal(registrant, foodRegistrant, result.data);
                    
                    // Show confirmation modal
                    $('#foodReleaseModal').modal('show');
                } else {
                    // No food registration found
                    document.getElementById('result').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <strong>Food Registration Not Found!</strong><br>
                            <strong>Name:</strong> ${registrant.firstname} ${registrant.lastname}<br>
                            <strong>Email:</strong> ${registrant.email}<br>
                            <strong>Chapter:</strong> ${registrant.chapter}<br><br>
                            This person is not registered for food distribution. 
                            Please try using <a href="search_food_release" class="alert-link">Release by Search</a> 
                            or verify their food registration status.
                        </div>
                    `;
                    setTimeout(resetScanner, 5000);
                }
            } catch (error) {
                console.error('Error looking up food registration:', error);
                document.getElementById('result').innerHTML = `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><strong>Lookup Error!</strong><br>Unable to check food registration: ${error.message}</div>`;
                setTimeout(resetScanner, 3000);
            }
        }

        function populateFoodReleaseModal(qrRegistrant, foodRegistrant, releaseData) {
            // Populate registrant information
            const fullName = `${qrRegistrant.firstname} ${qrRegistrant.middlename ? qrRegistrant.middlename + ' ' : ''}${qrRegistrant.lastname}${qrRegistrant.suffix ? ' ' + qrRegistrant.suffix : ''}`;
            document.getElementById('modalName').textContent = fullName;
            document.getElementById('modalEmail').textContent = qrRegistrant.email;
            document.getElementById('modalChapter').textContent = qrRegistrant.chapter || 'Not specified';
            document.getElementById('modalPrcLicense').textContent = qrRegistrant.prclicencenumber || 'Not specified';

            // Populate release information
            const currentDate = '<?php echo getCurrentDate(); ?>';
            const conventionDay = getConventionDayNumber(currentDate);
            document.getElementById('modalReleaseDate').textContent = currentDate;
            document.getElementById('modalConventionDay').textContent = conventionDay ? `Day ${conventionDay}` : 'Not a convention day';

            // Show release status
            displayReleaseStatus(releaseData);
        }

        function displayReleaseStatus(data) {
            const summary = data.release_summary;
            const dailyStatus = data.daily_status;
            
            let statusHtml = `
                <div class="alert alert-info">
                    <h6><i class="fas fa-chart-pie"></i> Release Summary</h6>
                    <p><strong>Total Releases:</strong> <span class="badge badge-primary">${summary.total_releases}/3</span></p>
                    <p><strong>Can Release Today:</strong> ${summary.can_release_today ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>'}</p>
                </div>
            `;

            if (!summary.can_release_today) {
                const currentDate = '<?php echo getCurrentDate(); ?>';
                const todayKey = currentDate === '2025-10-16' ? 'day1' : 
                                currentDate === '2025-10-17' ? 'day2' : 
                                currentDate === '2025-10-18' ? 'day3' : null;

                if (todayKey && dailyStatus[todayKey] && dailyStatus[todayKey].released) {
                    statusHtml += `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Already Released Today!</strong><br>
                            Food has already been released to this registrant today.
                        </div>
                    `;
                    document.getElementById('confirmReleaseBtn').style.display = 'none';
                } else {
                    statusHtml += `
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i> 
                            <strong>Cannot Release Today!</strong><br>
                            Food release is not available on this date.
                        </div>
                    `;
                    document.getElementById('confirmReleaseBtn').style.display = 'none';
                }
            } else {
                document.getElementById('confirmReleaseBtn').style.display = 'inline-block';
            }

            document.getElementById('releaseStatusSection').innerHTML = statusHtml;
        }

        function getConventionDayNumber(date) {
            const conventionDays = {
                '2025-10-16': 1,
                '2025-10-17': 2,
                '2025-10-18': 3
            };
            return conventionDays[date] || null;
        }

        async function confirmFoodRelease() {
            if (!currentRegistrantData) {
                alert('No registrant data available');
                return;
            }

            try {
                const response = await fetch('releaseFoodToday', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        food_id: currentRegistrantData.food_id
                    })
                });

                const data = await response.json();

                $('#foodReleaseModal').modal('hide');

                if (data.success) {
                    document.getElementById('foodReleaseResultMessage').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <strong>Food Released Successfully!</strong><br>
                            Food has been released to <strong>${currentRegistrantData.qr_registrant.firstname} ${currentRegistrantData.qr_registrant.lastname}</strong>.
                        </div>
                    `;
                } else {
                    document.getElementById('foodReleaseResultMessage').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Release Failed!</strong><br>
                            ${data.message || 'Unknown error occurred'}
                        </div>
                    `;
                }

                $('#foodReleaseResultModal').modal('show');

            } catch (error) {
                console.error('Error releasing food:', error);
                $('#foodReleaseModal').modal('hide');
                document.getElementById('foodReleaseResultMessage').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle"></i> <strong>Network Error!</strong><br>
                        Unable to release food: ${error.message}
                    </div>
                `;
                $('#foodReleaseResultModal').modal('show');
            }
        }

        function resetScanner() {
            currentRegistrantData = null;
            document.getElementById('result').innerHTML = '<span class="text-muted"><i class="fas fa-qrcode mr-2"></i>Ready to scan next QR code...</span>';
            
            // Resume scanning if scanner is still active
            if (html5QrcodeScanner) {
                try {
                    html5QrcodeScanner.resume();
                } catch (err) {
                    console.log('Scanner not paused or already resumed');
                }
            }
        }

        function onScanFailure(error) {
            // Filter out noisy errors that are normal during scanning
            const noisyErrors = [
                'No QR code found',
                'QR code parse error',
                'NotFoundException',
                'Unable to decode',
                'Canvas'
            ];

            const isNoisyError = noisyErrors.some(noise => error.includes(noise));
            
            if (!isNoisyError) {
                console.log('QR Scan error:', error);
            }
        }

        // Function to show the loading indicator
        function showLoadingIndicator() {
            const loader = document.getElementById('loading');
            if (loader) {
                loader.style.display = 'block';
            }
        }

        // Function to hide the loading indicator
        function hideLoadingIndicator() {
            const loader = document.getElementById('loading');
            if (loader) {
                loader.style.display = 'none';
            }
        }

        // Clean up camera when page is closed or refreshed
        window.addEventListener('beforeunload', async function () {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();
                } catch (err) {
                    console.error('Error cleaning up scanner:', err);
                }
            }
        });

        // Also clean up on visibility change (when tab becomes hidden)
        document.addEventListener('visibilitychange', function () {
            if (document.hidden && html5QrcodeScanner) {
                console.log('Tab hidden, stopping camera...');
                stopHtml5QrCode();
            }
        });
    </script>

</body>

</html>
