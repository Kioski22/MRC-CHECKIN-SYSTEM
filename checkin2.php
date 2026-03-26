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

    <script>
        // Add dropdown event listener with proper error handling
        document.addEventListener('DOMContentLoaded', function () {
            const dropdown = document.getElementById('scanMethod');
            if (dropdown) {
                console.log('Dropdown found, adding event listener');
                dropdown.addEventListener('change', handleScanMethodChange);
            } else {
                console.error('Dropdown not found!');
            }
        });

        // Also try to add it immediately in case DOM is already ready
        setTimeout(function () {
            const dropdown = document.getElementById('scanMethod');
            if (dropdown && !dropdown.hasAttribute('data-listener-added')) {
                console.log('Adding backup event listener to dropdown');
                dropdown.addEventListener('change', handleScanMethodChange);
                dropdown.setAttribute('data-listener-added', 'true');
            }
        }, 100);

        async function handleScanMethodChange() {
            try {
                const method = this.value;
                console.log('Scan method selected:', method);

                // Clear any existing scanner first
                if (html5QrcodeScanner) {
                    try {
                        console.log('Cleaning up existing Html5QrcodeScanner...');
                        await html5QrcodeScanner.clear();
                        html5QrcodeScanner = null;

                        console.log('Previous scanner cleaned up');
                    } catch (err) {
                        console.log("Error cleaning up scanner:", err);
                    }
                }

                // Clear previous results and hide all sections
                document.getElementById('result').innerHTML = '';
                document.getElementById('waitingMessage').style.display = 'none';
                document.getElementById('html5qr-reader').style.display = 'none';
                document.getElementById('physicalScannerInput').style.display = 'none';

                // Remove focus from the dropdown after selection
                this.blur();

                if (method === 'html5qr') {
                    console.log('Starting HTML5 QR scanner...');

                    // Show the reader div and start the scanner
                    document.getElementById('html5qr-reader').style.display = 'block';
                    document.getElementById('result').innerHTML = '<span style="color: blue;"><i class="fas fa-info-circle mr-2"></i>Initializing camera scanner...</span>';

                    // Start the Html5QrcodeScanner (it will handle camera permissions and UI)
                    startHtml5QrCode();

                } else if (method === 'physical') {
                    console.log('Starting physical scanner...');
                    // Show physical scanner input
                    document.getElementById('physicalScannerInput').style.display = 'block';
                    document.getElementById('waitingMessage').style.display = 'block';

                    // Make the input field hidden but still focusable
                    const input = document.getElementById('physicalScannerInput');
                    input.style.position = 'absolute';
                    input.style.left = '-9999px'; // Move it off-screen

                    input.focus(); // Focus on the hidden input field
                    startPhysicalScanner(); // Start the physical scanner function
                }
            } catch (error) {
                console.error('Error in dropdown change handler:', error);
            }
        }

        document.addEventListener('keydown', function (event) {
            const hiddenInput = document.getElementById('physicalScannerInput');

            // Only focus the hidden input if no modal is open and physical scanner is active
            const isModalOpen = document.querySelector('.modal.show') !== null;
            const isPhysicalScannerActive = document.getElementById('physicalScannerInput').style.display !== 'none';

            if (!isModalOpen && isPhysicalScannerActive) {
                hiddenInput.focus();
            }
        });

        // Optional: If you want to clear the input field each time it loses focus
        document.getElementById('physicalScannerInput').addEventListener('blur', function () {
            this.value = ''; // Clear the input when it loses focus
        });

        // Check HTTPS status and display indicator
        function checkHttpsStatus() {
            const httpsStatusDiv = document.getElementById('httpsStatus');
            const isHttps = window.location.protocol === 'https:';
            const isLocalhost = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
            const isSecureContext = window.isSecureContext;
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

            if (isHttps) {
                httpsStatusDiv.innerHTML = '<small class="text-success"><i class="fas fa-lock mr-1"></i>Secure HTTPS - Camera access available</small>';
            } else if (isLocalhost && isSecureContext) {
                httpsStatusDiv.innerHTML = '<small class="text-info"><i class="fas fa-home mr-1"></i>Localhost secure context - Camera should work</small>';
            } else if (isLocalhost) {
                httpsStatusDiv.innerHTML = `
                    <div class="alert alert-info alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Localhost HTTP Detected</strong><br>
                        <small>Camera may work on localhost in some browsers.</small><br>
                        <small><strong>If camera fails:</strong> Try "Physical QR Code Scanner" or use https://localhost:8000</small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`;
            } else {
                httpsStatusDiv.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>HTTP Connection Detected</strong><br>
                        <small>Camera access requires HTTPS. For QR code scanning on HTTP:</small><br>
                        <small><strong>Recommendation:</strong> Use "Physical QR Code Scanner" option which works on both HTTP and HTTPS</small>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>`;
            }
        }

        // Initialize HTTPS status check
        checkHttpsStatus();

        // Debug function to check permissions policy
        function debugPermissionsPolicy() {
            console.log('=== PERMISSIONS DEBUG ===');
            console.log('Protocol:', location.protocol);
            console.log('Hostname:', location.hostname);
            console.log('Is secure context:', window.isSecureContext);
            console.log('MediaDevices available:', !!navigator.mediaDevices);
            console.log('getUserMedia available:', !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia));

            // Check if we can query permissions
            if ('permissions' in navigator) {
                navigator.permissions.query({ name: 'camera' }).then(function (result) {
                    console.log('Camera permission state:', result.state);
                }).catch(function (err) {
                    console.log('Cannot query camera permission:', err);
                });
            } else {
                console.log('Permissions API not supported');
            }
            console.log('========================');
        }

        // Debug function to check if camera is actually released
        function checkCameraStatus() {
            const videoElement = document.querySelector('#html5qr-reader video');
            console.log('=== CAMERA STATUS DEBUG ===');
            console.log('Html5QrcodeScanner scanning:', html5QrcodeScanner ? 'Initialized' : 'Not initialized');
            console.log('Video element exists:', !!videoElement);

            if (videoElement) {
                console.log('Video srcObject:', videoElement.srcObject);
                console.log('Video readyState:', videoElement.readyState);
                console.log('Video paused:', videoElement.paused);

                if (videoElement.srcObject) {
                    const tracks = videoElement.srcObject.getTracks();
                    console.log('Active tracks:', tracks.length);
                    tracks.forEach((track, index) => {
                        console.log(`Track ${index}: ${track.kind} - ${track.label} - State: ${track.readyState}`);
                    });
                }
            }
            console.log('==========================');
        }

        // Run debug check
        setTimeout(debugPermissionsPolicy, 1000);

        // Add global function to check camera status (for debugging)
        window.checkCameraStatus = checkCameraStatus;

        // Debug function for scanning issues
        function showScanDebugInfo() {
            const debugInfo = `
=== QR SCANNING DEBUG INFO ===
Scan Attempts: ${window.scanAttempts || 0}
Last Error: ${window.lastScanError || 'None'}
Html5Qrcode Status: ${html5QrcodeScanner ? 'Initialized' : 'Not initialized'}
Video Element: ${document.querySelector('#html5qr-reader video') ? 'Found' : 'Missing'}
Video Dimensions: ${(() => {
                    const video = document.querySelector('#html5qr-reader video');
                    return video ? `${video.videoWidth}x${video.videoHeight}` : 'N/A';
                })()}
QR Box Size: ${(() => {
                    const reader = document.querySelector('#html5qr-reader');
                    if (reader) {
                        const rect = reader.getBoundingClientRect();
                        return `Container: ${Math.floor(rect.width)}x${Math.floor(rect.height)}`;
                    }
                    return 'N/A';
                })()}
Browser: ${navigator.userAgent.split(' ').pop()}
Protocol: ${location.protocol}
=============================`;

            console.log(debugInfo);
            alert('Debug info logged to console. Check browser console (F12) for details.');
        }

        // Make it globally available
        window.showScanDebugInfo = showScanDebugInfo;

        // Test function for detection issues
        function testDetectionSettings() {
            if (!html5QrcodeScanner) {
                alert('Camera must be started first. Click "Start Camera" then try this test.');
                return;
            }

            console.log('ðŸ” Testing QR Detection Settings...');

            // Get video element to analyze current feed
            const video = document.querySelector('#html5qr-reader video');
            if (!video) {
                console.error('Video element not found');
                alert('âŒ Video element not found - scanner may not be properly initialized');
                return;
            }

            console.log('Current video properties:', {
                videoWidth: video.videoWidth,
                videoHeight: video.videoHeight,
                width: video.width,
                height: video.height,
                readyState: video.readyState,
                currentTime: video.currentTime,
                paused: video.paused,
                ended: video.ended
            });

            // Test if we can capture a frame from the video
            try {
                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;

                if (canvas.width === 0 || canvas.height === 0) {
                    alert('âŒ Video dimensions are 0x0. Camera may not be properly initialized.');
                    return;
                }

                ctx.drawImage(video, 0, 0);
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);

                console.log('ðŸ“¸ Successfully captured frame data:', {
                    width: imageData.width,
                    height: imageData.height,
                    dataLength: imageData.data.length
                });

                // Check if the image data is not all zeros (which would indicate a black screen)
                let nonZeroPixels = 0;
                let brightPixels = 0;
                const totalPixels = imageData.width * imageData.height;

                for (let i = 0; i < imageData.data.length; i += 4) {
                    const r = imageData.data[i];
                    const g = imageData.data[i + 1];
                    const b = imageData.data[i + 2];
                    const brightness = (r + g + b) / 3;

                    if (brightness > 0) nonZeroPixels++;
                    if (brightness > 127) brightPixels++;
                }

                const activePercent = (nonZeroPixels / totalPixels * 100).toFixed(1);
                const brightPercent = (brightPixels / totalPixels * 100).toFixed(1);

                console.log(`ðŸ“Š Frame analysis: ${nonZeroPixels} non-black pixels (${activePercent}%), ${brightPixels} bright pixels (${brightPercent}%)`);

                // Create a temporary canvas to show captured frame (for debugging)
                canvas.style.position = 'fixed';
                canvas.style.top = '10px';
                canvas.style.right = '10px';
                canvas.style.width = '200px';
                canvas.style.height = 'auto';
                canvas.style.border = '2px solid red';
                canvas.style.zIndex = '9999';
                canvas.title = 'Current camera frame (will disappear in 5 seconds)';
                document.body.appendChild(canvas);

                setTimeout(() => {
                    if (canvas.parentNode) {
                        canvas.parentNode.removeChild(canvas);
                    }
                }, 5000);

                if (nonZeroPixels === 0) {
                    alert('âš ï¸ Video feed appears to be completely black!\n\nPossible issues:\nâ€¢ Camera is covered\nâ€¢ No camera permission\nâ€¢ Camera hardware failure\n\nCheck browser console for more details.');
                } else if (activePercent < 10) {
                    alert(`âš ï¸ Video feed appears very dark (${activePercent}% active pixels).\n\nTry:\nâ€¢ Improve lighting\nâ€¢ Move to brighter area\nâ€¢ Check if camera lens is clean`);
                } else if (brightPercent < 5) {
                    alert(`âš ï¸ Video feed is quite dark (${brightPercent}% bright pixels).\n\nFor better QR detection:\nâ€¢ Increase lighting\nâ€¢ Avoid shadows on QR code\nâ€¢ Move closer to light source`);
                } else {
                    alert(`âœ… Video feed looks good!\n\nðŸ“Š Stats:\nâ€¢ Active pixels: ${activePercent}%\nâ€¢ Bright pixels: ${brightPercent}%\nâ€¢ Resolution: ${canvas.width}x${canvas.height}\n\nIf QR codes still aren't scanning:\n\nðŸ“ Try these tips:\nâ€¢ Move camera closer to QR code\nâ€¢ Ensure QR code is flat and uncrumpled\nâ€¢ Try different angles\nâ€¢ Check QR code isn't damaged\nâ€¢ Ensure good lighting without glare\nâ€¢ Hold camera steady for 2-3 seconds\n\nðŸ–¼ï¸ A preview of your current camera feed will appear in the top-right corner for 5 seconds.`);
                }

            } catch (err) {
                console.error('Error testing video capture:', err);
                alert('âŒ Error testing video feed: ' + err.message);
            }
        }

        // Make it globally available
        window.testDetectionSettings = testDetectionSettings;

        // Manual scanner restart function
        function restartScanner() {
            if (!html5QrcodeScanner) {
                alert('No scanner to restart. Please start the camera first.');
                return;
            }

            console.log('ðŸ”„ Manually restarting scanner...');

            html5QrcodeScanner.clear().then(() => {
                console.log('Scanner stopped for manual restart');

                // Reset error counters
                window.scanAttempts = 0;
                window.dimensionErrors = 0;
                window.lastScanError = '';

                html5QrcodeScanner = null;

                // Wait a moment then restart
                setTimeout(() => {
                    console.log('Restarting scanner after manual restart...');
                    startHtml5QrCode();
                }, 1000);

            }).catch(err => {
                console.error('Failed to stop scanner for manual restart:', err);
                alert('Error stopping scanner: ' + err.message);
            });
        }

        // Make it globally available
        window.restartScanner = restartScanner;

        // Clean up camera when page is closed or refreshed
        window.addEventListener('beforeunload', async function () {
            if (html5QrcodeScanner) {
                try {
                    await html5QrcodeScanner.clear();

                    // Force cleanup of all video tracks
                    const videoElement = document.querySelector('#html5qr-reader video');
                    if (videoElement && videoElement.srcObject) {
                        videoElement.srcObject.getTracks().forEach(track => track.stop());
                        videoElement.srcObject = null;
                    }
                } catch (err) {
                    console.warn('Error cleaning up camera on page unload:', err);
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

        // Debug: Test if dropdown exists
        setTimeout(function () {
            const dropdown = document.getElementById('scanMethod');
            console.log('Dropdown element:', dropdown);
            if (dropdown) {
                console.log('Dropdown options:', dropdown.options.length);
            }
        }, 1000);

        let html5QrcodeScanner = null;

        async function startHtml5QrCode() {
            try {
                console.log('Starting Html5QrcodeScanner...');

                if (typeof Html5QrcodeScanner === 'undefined') {
                    throw new Error('The QR scanner library did not load. Check whether js/html5-qrcode.min.js is accessible on the hosted site.');
                }

                if (!window.isSecureContext) {
                    throw new Error('Camera access is blocked because this page is not running in a secure context. Use HTTPS directly and avoid embedded iframes.');
                }

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('Camera API is unavailable in this browser context. This usually means HTTPS is missing or camera access is blocked by browser or hosting policy.');
                }

                // Stop any existing scanner first
                if (html5QrcodeScanner) {
                    console.log('Cleaning up existing scanner...');
                    html5QrcodeScanner.clear().catch(err => {
                        console.warn('Error clearing existing scanner:', err);
                    });
                    html5QrcodeScanner = null;
                }

                // Clear the reader div
                const readerDiv = document.getElementById('html5qr-reader');
                if (readerDiv) {
                    readerDiv.innerHTML = '';
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
                    // Remove fixed aspect ratio to allow camera's natural ratio
                    rememberLastUsedCamera: true,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
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
                    /* verbose= */ false
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
                    errorMessage = 'Camera is already in use by another application.';
                } else if (!window.isSecureContext) {
                    errorMessage = 'Camera permission prompt cannot appear because the page is not in a secure HTTPS context. Open the site using HTTPS directly on your GoDaddy domain.';
                } else if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    errorMessage = 'The browser is not exposing the camera API. Check HTTPS, browser permissions, and whether the page is opened inside an iframe or app webview.';
                }

                document.getElementById('result').innerHTML = `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>${errorMessage}</span>`;
            }
        }

        async function stopHtml5QrCode() {
            try {
                console.log('Stopping Html5QrcodeScanner...');

                if (html5QrcodeScanner) {
                    await html5QrcodeScanner.clear();
                    console.log('Html5QrcodeScanner cleared successfully');
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
                // Try multiple sound options for better browser compatibility
                const sounds = [
                    // Success beep sound (short and pleasant)
                    'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBCd+0u7SejQBGGm//fCmRQkOV6zs3YNGAwoXfcn16H9NEBRru/LmyGEACi6AyOXsR',
                    // Alternative sound with higher pitch
                    'data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBCd+0u7SejQBGGm//fCmRQkOV6zs3YNGAwoXfcn16H9NEBRru/LmyGEACi6AyOXsR'
                ];

                const audio = new Audio(sounds[0]);
                audio.volume = 0.2; // Slightly louder for better feedback

                // Add multiple attempts for reliability
                const playPromise = audio.play();

                if (playPromise !== undefined) {
                    playPromise.then(() => {
                        console.log('âœ… Success sound played');
                    }).catch(error => {
                        console.log('ðŸ”‡ Audio autoplay prevented (normal on some browsers):', error.message);

                        // Try alternative: Web Audio API beep
                        playWebAudioBeep();
                    });
                }
            } catch (error) {
                console.log('ðŸ”‡ Audio not supported:', error.message);
                // Try Web Audio API as fallback
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

                    oscillator.frequency.value = 800; // Pleasant beep frequency
                    oscillator.type = 'sine';

                    gainNode.gain.setValueAtTime(0, audioContext.currentTime);
                    gainNode.gain.linearRampToValueAtTime(0.1, audioContext.currentTime + 0.01);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.2);

                    console.log('âœ… Web Audio beep played');
                }
            } catch (error) {
                console.log('ðŸ”‡ Web Audio not supported:', error.message);
                // Final fallback: vibration on mobile
                if ('vibrate' in navigator) {
                    navigator.vibrate([100, 50, 100]); // Success vibration pattern
                    console.log('ðŸ“³ Haptic feedback provided');
                }
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log('ðŸŽ‰ QR CODE SCANNED SUCCESSFULLY!');
            console.log('Decoded text:', decodedText);

            // Immediate visual feedback
            document.getElementById('result').innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i><strong>QR Code Scanned!</strong><br>Processing: ' + decodedText + '</div>';

            // Play enhanced success sound
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

            // Process the scanned code
            switch (true) {
                case decodedText.startsWith("ICTO-"):
                    // If the QR code starts with "ICTO-", fetch QR code details
                    document.getElementById('result').innerHTML += '<br><span class="text-info"><i class="fas fa-spinner fa-spin mr-2"></i>Looking up ICTO registration...</span>';
                    fetchQRCodeDetails(decodedText);
                    break;

                default:
                    // Otherwise, proceed with the normal API request
                    document.getElementById('result').innerHTML += '<br><span class="text-info"><i class="fas fa-spinner fa-spin mr-2"></i>Looking up registration details...</span>';
                    makeApiRequest(decodedText);
                    break;
            }

            // Stop the QR code scanner after a successful scan
            try {
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear().then(() => {
                        console.log("QR Code scanning stopped after success.");

                        // Clear the reader and hide it
                        document.getElementById('html5qr-reader').innerHTML = '';
                        document.getElementById('html5qr-reader').style.display = 'none';

                        // Show scan success message
                        setTimeout(() => {
                            const successMsg = document.createElement('div');
                            successMsg.className = 'alert alert-info mt-3';
                            successMsg.innerHTML = '<i class="fas fa-camera mr-2"></i>Scanner stopped automatically after successful scan. Select "Html5-QRCode (Camera)" again to scan another code.';
                            document.getElementById('result').appendChild(successMsg);
                        }, 2000);

                        // Reset the dropdown selection
                        let dropdown = document.getElementById('scanMethod');
                        if (dropdown) {
                            dropdown.selectedIndex = 0; // Reset to the first option
                        }

                        // Clear the scanner reference
                        html5QrcodeScanner = null;
                    }).catch(err => {
                        console.error(`Error stopping the QR Code scanner: ${err}`);
                    });
                }
            } catch (err) {
                console.error(`Error stopping the QR Code scanner: ${err}`);
            }
        }

        async function fetchQRCodeDetails(qrCode) {
            try {
                const response = await fetch('ictoqr', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `qrcode=${encodeURIComponent(qrCode)}`
                });

                const data = await response.json();

                if (data.resultKey === 1) {
                    // Process data as per your needs
                    handleScanData(data);
                } else {
                    // Handle error from the PHP response
                    document.getElementById('result').innerHTML += `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>${data.errorMsg}</span>`;
                }
            } catch (error) {
                console.error('Error fetching QR code details:', error);
                document.getElementById('result').innerHTML += `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>Error fetching QR code details.</span>`;
            }
        }

        function insertCheckinData(data) {
            fetch('insertCheckinData', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    firstname: data.firstname,
                    lastname: data.lastname,
                    middlename: data.middlename,
                    email: data.email,
                    prclicensenumber: data.prclicencenumber,
                    chapter: data.chapter,
                    qrcode: data.qrcode
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Check-in data inserted successfully');
                    } else {
                        console.warn('Check-in data insertion issue:', data.message);
                        // Don't show error to user if it's just a duplicate - this is expected behavior
                        if (data.message && data.message.includes('already been scanned')) {
                            console.log('QR code already processed - continuing normally');
                        }
                    }
                })
                .catch(error => {
                    console.error('Error inserting check-in data:', error);
                });
        }


        function onScanFailure(error) {
            // Track scanning attempts for debugging
            if (!window.scanAttempts) window.scanAttempts = 0;
            if (!window.lastScanError) window.lastScanError = '';

            window.scanAttempts++;
            window.lastScanError = error;

            // Check for persistent dimension errors and try to fix them
            if (error && error.includes('source width is 0') && window.scanAttempts > 0) {
                // Count dimension errors specifically
                if (!window.dimensionErrors) window.dimensionErrors = 0;
                window.dimensionErrors++;

                // If we've had too many dimension errors, try restarting the scanner
                if (window.dimensionErrors > 50 && window.dimensionErrors % 200 === 0) {
                    console.warn(`âš ï¸ ${window.dimensionErrors} dimension errors detected. Attempting scanner restart...`);

                    // Try to restart the scanner
                    if (html5QrcodeScanner) {
                        html5QrcodeScanner.clear().then(() => {
                            console.log('Scanner stopped for restart');

                            // Wait a moment then restart
                            setTimeout(() => {
                                console.log('Restarting scanner after dimension errors...');
                                startHtml5QrCode();
                            }, 2000);

                        }).catch(err => {
                            console.error('Failed to stop scanner for restart:', err);
                        });
                        return; // Don't process this error further
                    }
                }
            }

            // Filter out noisy canvas/scan errors that are normal during scanning
            const noisyErrors = [
                'QR code parse error',
                'IndexSizeError',
                'source width is 0',
                'source height is 0',
                'getImageData',
                'CanvasRenderingContext2D',
                'No MultiFormat Readers were able to detect the code'
            ];

            const isNoisyError = noisyErrors.some(noise => error.includes(noise));

            if (isNoisyError) {
                // Only log these errors every 100 attempts instead of every 10
                if (window.scanAttempts % 100 === 0) {
                    console.log(`Scan attempts: ${window.scanAttempts}. Dimension errors: ${window.dimensionErrors || 0}. Recent error: ${error}`);
                }
                return;
            }

            // Only show important errors (camera/permission related)
            if (error && (error.includes('NotAllowed') ||
                error.includes('Permission') ||
                error.includes('Camera') ||
                error.includes('NotFound') ||
                error.includes('NotReadable'))) {
                console.error(`QR Code scan error: ${error}`);
                document.getElementById('result').innerHTML = `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>Camera error: ${error}</span>`;
            }

            // For other errors, just log them without showing to user
            else if (error) {
                console.debug(`QR scan debug (attempt ${window.scanAttempts}): ${error}`);
            }
        }

        function startPhysicalScanner() {
            const input = document.getElementById('physicalScannerInput');
            input.focus(); // Ensure the hidden input field is focused

            let debounceTimeout;
            input.addEventListener('input', function () {
                clearTimeout(debounceTimeout);
                debounceTimeout = setTimeout(() => {
                    const scannedValue = input.value.trim();
                    if (scannedValue) {
                        console.log(`ðŸŽ‰ PHYSICAL SCANNER DETECTED CODE: ${scannedValue}`);

                        // Visual feedback for physical scanner
                        document.getElementById('result').innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle mr-2"></i><strong>QR Code Detected!</strong><br>Processing: ' + scannedValue + '</div>';
                        document.getElementById('waitingMessage').style.display = 'none';

                        // Play success sound (same as camera scanner)
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBCd+0u7SejQBGGm//fCmRQkOV6zs3YNGAwoXfcn16H9NEBRru/LmyGEACi6AyOXsR');
                            audio.volume = 0.1;
                            audio.play().catch(e => console.log('Audio play failed (normal on some browsers)'));
                        } catch (e) {
                            console.log('Audio not supported');
                        }

                        switch (true) {
                            case scannedValue.startsWith("ICTO-"):
                                // If the QR code starts with "ICTO-", fetch QR code details
                                document.getElementById('result').innerHTML += '<br><span class="text-info"><i class="fas fa-spinner fa-spin mr-2"></i>Looking up ICTO registration...</span>';
                                fetchQRCodeDetails(scannedValue);
                                break;

                            default:
                                // Otherwise, proceed with the normal API request
                                document.getElementById('result').innerHTML += '<br><span class="text-info"><i class="fas fa-spinner fa-spin mr-2"></i>Looking up registration details...</span>';
                                makeApiRequest(scannedValue);
                                break;
                        }

                        document.getElementById('result').style.display = 'block';

                        // Show scanner ready message after processing
                        setTimeout(() => {
                            const readyMsg = document.createElement('div');
                            readyMsg.className = 'alert alert-info mt-3';
                            readyMsg.innerHTML = '<i class="fas fa-barcode mr-2"></i>Physical scanner ready for next code. Scan another QR code or switch scan methods above.';
                            document.getElementById('result').appendChild(readyMsg);

                            // Re-show waiting message
                            document.getElementById('waitingMessage').style.display = 'block';
                        }, 3000);
                    }
                    input.value = ''; // Clear the input field after processing
                }, 300); // Adjust debounce delay as needed
            });

            input.addEventListener('blur', function () {
                // Only re-focus if no modal is open and physical scanner is still active
                const isModalOpen = document.querySelector('.modal.show') !== null;
                const isPhysicalScannerActive = document.getElementById('physicalScannerInput').style.display !== 'none';

                if (!isModalOpen && isPhysicalScannerActive) {
                    input.focus(); // Re-focus if the input loses focus
                }
            });
        }


        async function makeApiRequest(scannedValue, retryCount = 3, retryDelay = 1000) {
            document.getElementById('result').innerText = ``;
            // Show loading indicator before the request starts
            showLoadingIndicator();

            try {
                const response = await fetch('getScanData', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `qrcode=${encodeURIComponent(scannedValue)}`
                });

                if (response.status === 401) {
                    // Unauthorized, renew token
                    try {
                        const newToken = await renewAccessToken();
                        hideLoadingIndicator();
                        return await makeApiRequestWithNewToken(scannedValue, newToken);
                    } catch (error) {
                        hideLoadingIndicator();
                        console.error('Error renewing token:', error);
                        document.getElementById('result').innerText += `\nError: Failed to renew token. ${error.message}`;
                    }
                } else if (response.ok) {
                    const data = await response.json();
                    hideLoadingIndicator();

                    if (data.resultKey === 1) {
                        // Process data through handleScanData
                        handleScanData(data);
                    } else {
                        //console.warn(`Unexpected resultKey received: ${data.resultKey}`);
                        //document.getElementById('result').innerText += `\nWarning: Unexpected result key: ${data.resultKey}`;
                        document.getElementById('result').innerHTML += `<span style="color: red;"><i class="fas fa-exclamation-triangle mr-2"></i>No registration found with scanned QR code.</span>`;
                    }
                } else {
                    hideLoadingIndicator();
                    console.error(`Unexpected response status: ${response.status}`);
                    document.getElementById('result').innerText += `\nError: Unexpected response status: ${response.status}`;
                }
            } catch (error) {
                // Retry logic for network or other errors
                if (retryCount > 0) {
                    console.warn(`Retrying... ${retryCount} attempts left. Error: ${error.message}`);
                    setTimeout(() => {
                        makeApiRequest(scannedValue, retryCount - 1, retryDelay);
                    }, retryDelay);
                } else {
                    // After max retries, show the error
                    hideLoadingIndicator();
                    console.error('Network or other error after retries:', error);
                    document.getElementById('result').innerText += `\nError: Network issue or timeout. ${error.message}`;
                }
            }
        }

        async function getRegistrationOrder(email, eventId = 16) {
            try {
                const response = await fetch('getRegistrationOrder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `email=${encodeURIComponent(email || '')}&event_id=${encodeURIComponent(eventId)}`
                });

                if (!response.ok) {
                    console.error(`Failed to fetch registration list: HTTP ${response.status}`);
                    return { display: null, order: null, total: 0 };
                }

                const data = await response.json();

                if (!data.success) {
                    console.warn(data.message || 'Registration order lookup failed');
                    return { display: null, order: null, total: 0 };
                }

                const total = Number(data.total || 0);
                const order = data.order !== null ? Number(data.order) : null;

                if (order === null) {
                    return { display: "Not found in list", order: null, total };
                }

                // Format with commas and ordinal suffix
                const formattedOrder = getOrdinal(order);
                const display = `${formattedOrder} Registrant`;

                return { display, order, total };

            } catch (error) {
                console.error('Error in getRegistrationOrder():', error);
                return { display: null, order: null, total: 0 };
            }
        }

        // Helper for ordinal suffix
        function getOrdinal(n) {
            if (!Number.isFinite(n)) {
                return n;
            }
            const s = ["th", "st", "nd", "rd"];
            const v = n % 100;
            const suffix = s[(v - 20) % 10] || s[v] || s[0];
            return n + suffix;
        }


        // Function to show the loading indicator
        function showLoadingIndicator() {
            const loader = document.getElementById('loading');
            if (loader) {
                loader.style.display = 'block'; // Show loading spinner or message
            }
        }

        // Function to hide the loading indicator
        function hideLoadingIndicator() {
            const loader = document.getElementById('loading');
            if (loader) {
                loader.style.display = 'none'; // Hide loading spinner or message
            }
        }


        async function makeApiRequestWithNewToken(scannedValue, newToken, retryCount = 3, retryDelay = 1000) {
            // Show loading indicator before the request starts
            showLoadingIndicator();

            try {
                const response = await fetch('getScanData', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `qrcode=${encodeURIComponent(scannedValue)}`
                });

                if (response.status === 401) {
                    // Unauthorized even with new token
                    document.getElementById('result').innerText += `\nError: Unauthorized access even after renewal.`;
                    hideLoadingIndicator();
                } else if (response.ok) {
                    const data = await response.json();
                    hideLoadingIndicator();
                    handleScanData(data); // Handle successful response
                } else {
                    hideLoadingIndicator();
                    throw new Error(`Unexpected response status: ${response.status}`);
                }
            } catch (error) {
                if (retryCount > 0) {
                    console.warn(`Retrying... ${retryCount} attempts left. Error: ${error.message}`);
                    setTimeout(() => {
                        makeApiRequestWithNewToken(scannedValue, newToken, retryCount - 1, retryDelay);
                    }, retryDelay);
                } else {
                    hideLoadingIndicator();
                    console.error('Error after retries:', error);
                    document.getElementById('result').innerText += `\nError: ${error.message}`;
                }
            }
        }

        async function renewAccessToken() {
            try {
                const response = await fetch('renewToken');
                if (!response.ok) {
                    throw new Error(`Failed to renew token. Status code: ${response.status}`);
                }
                const data = await response.json();
                if (data.access_token) {
                    return data.access_token; // Return the new token if present
                } else {
                    throw new Error('Failed to retrieve new token');
                }
            } catch (error) {
                console.error('Error renewing access token:', error);
                throw error;
            }
        }


        async function handleScanData(data) {
            if (data.resultValue && data.resultValue.length > 0) {
                const result = data.resultValue[0];

                // Update the modal with data from the result
                document.getElementById('modalFirstname').innerText = result.firstname || 'N/A';
                document.getElementById('modalLastname').innerText = result.lastname || 'N/A';
                document.getElementById('modalMiddlename').innerText = result.middlename || 'N/A';
                document.getElementById('modalEmail').innerText = result.email || 'N/A';
                document.getElementById('modalPrclicensenumber').innerText = result.prclicencenumber || 'N/A';
                document.getElementById('modalChapter').innerText = result.chapter || 'N/A';
                document.getElementById('modalQrcodeLink').href = result.qrcode || '#';
                document.getElementById('modalQrcodeLink').innerText = result.qrcode ? 'QR Code' : 'No QR Code Available';

                // Insert check-in data immediately
                insertCheckinData(result);

                // NEW: Fetch registration order (async)
                const { order, total } = await getRegistrationOrder(result.email, 16);

                // Create or update a paragraph inside modal-body
                let orderEl = document.getElementById('modalRegistrationOrder');
                if (!orderEl) {
                    orderEl = document.createElement('p');
                    orderEl.id = 'modalRegistrationOrder';
                    document.querySelector('#scanResultModal .modal-body').appendChild(orderEl);
                }

                if (order !== null) {
                    orderEl.innerHTML = `<strong>Registration Order:</strong> #${order} of ${total} registrants`;
                } else {
                    orderEl.innerHTML = `<strong>Registration Order:</strong> <span class="text-danger">Not found in list</span>`;
                }

                // Show the modal
                $('#scanResultModal').modal('show');

            } else {
                document.getElementById('result').innerText += `\nError: ${data.errorMsg || 'No valid registration data found.'}`;
            }
        }


        function editBeforePrint() {
            // Populate the edit form with current values
            document.getElementById('editFirstname').value = document.getElementById('modalFirstname').innerText;
            document.getElementById('editLastname').value = document.getElementById('modalLastname').innerText;
            document.getElementById('editMiddlename').value = document.getElementById('modalMiddlename').innerText;
            document.getElementById('editChapter').value = document.getElementById('modalChapter').innerText;

            // Show the edit modal
            $('#editPrintModal').modal('show');

            // Focus on the first input after a short delay
            setTimeout(function () {
                document.getElementById('editFirstname').focus();
            }, 500);
        }

        function saveAndPrint() {
            // Get the edited values
            const editedFirstname = document.getElementById('editFirstname').value;
            const editedLastname = document.getElementById('editLastname').value;
            const editedMiddlename = document.getElementById('editMiddlename').value;
            const editedChapter = document.getElementById('editChapter').value;

            // Debug: Log the values
            console.log('Edited values:', {
                firstname: editedFirstname,
                lastname: editedLastname,
                middlename: editedMiddlename,
                chapter: editedChapter
            });

            // Update modal values with edited values
            document.getElementById('modalFirstname').innerText = editedFirstname;
            document.getElementById('modalLastname').innerText = editedLastname;
            document.getElementById('modalMiddlename').innerText = editedMiddlename;
            document.getElementById('modalChapter').innerText = editedChapter;

            // Close the edit modal
            $('#editPrintModal').modal('hide');

            // Call the print function
            printLabel();
        }

        function printLabel() {
            const firstname = document.getElementById('modalFirstname').innerText;
            const lastname = document.getElementById('modalLastname').innerText;
            const middlename = document.getElementById('modalMiddlename').innerText;
            const chapter = document.getElementById('modalChapter').innerText;
            const qrcodeLink = document.getElementById('modalQrcodeLink').href;

            // Construct the name part with middle name included only if it is not "N/A"
            let namePart = `${lastname}, ${firstname}`;
            if (middlename !== 'N/A' && middlename.trim() !== '') {
                namePart += ` ${middlename}`;
            }

            const printContent = `
            <div style="width: 75mm; height: 105mm; position: relative; margin: 0; padding: 0; box-sizing: border-box;">
                <div style="position: absolute; width: 100mm; height: 70mm; background: #fff; transform: rotate(90deg); left: -12.5mm; top: 17.5mm; font-size: 16px; font-weight: 600; line-height: 1.1; padding: 0; box-sizing: border-box; font-family: 'Arial', 'Helvetica', sans-serif;">
                <!-- Name -->
                <p style="position: absolute; top: 4mm; left: 4mm; width: calc(100% - 8mm); margin: 0; font-size: 34px; font-weight: 700; text-align: left; padding: 2mm; box-sizing: border-box; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                    ${namePart}
                </p>

                <!-- Chapter -->
                <p style="position: absolute; bottom: 4mm; left: 4mm; width: calc(100% - 38mm); margin: 0; font-size: 20px; font-style: italic; color: #333; text-align: left; padding: 2mm; box-sizing: border-box; word-wrap: break-word; overflow-wrap: break-word; hyphens: auto;">
                    ${chapter} Chapter
                </p>

                <!-- QR Code - FIXED POSITION -->
                <div style="position: absolute; bottom: 4mm; right: 4mm; width: 35mm; height: 35mm; display: flex; justify-content: center; align-items: center; box-sizing: border-box; z-index: 10;">
                    <img src="${qrcodeLink}" alt="QR Code" style="width: 33mm; height: 33mm; box-sizing: border-box;">
                </div>
                </div>
            </div>`;

            // Print logic (e.g., open a new window and print the content)
            const printWindow = window.open('', '_blank');
            printWindow.document.open();
            printWindow.document.write(`
            <html>
            <head>
            <title>Print Label</title>
            <style>
                @page {
                size: 75mm 105mm;
                margin: 0mm !important;
                }
                html, body {
                margin: 0 !important;
                padding: 0 !important;
                width: 75mm !important;
                height: 105mm !important;
                overflow: hidden !important;
                }
                * {
                box-sizing: border-box !important;
                }
                @media print {
                    html, body {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 75mm !important;
                        height: 105mm !important;
                    }
                }
            </style>
            </head>
            <body onload="window.print(); window.close();">
            ${printContent}
            </body>
            </html>
        `);
            printWindow.document.close();
        }


        function checkIn() {
            const firstname = document.getElementById('modalFirstname').innerText;
            const lastname = document.getElementById('modalLastname').innerText;
            const middlename = document.getElementById('modalMiddlename').innerText;
            const email = document.getElementById('modalEmail').innerText;
            const prclicensenumber = document.getElementById('modalPrclicensenumber').innerText;
            const chapter = document.getElementById('modalChapter').innerText;

            // QR code is still being displayed but not included in the POST request
            const qrcode = document.getElementById('modalQrcodeLink').href;

            fetch('checkinscript', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    firstname,
                    lastname,
                    middlename,
                    email,
                    prclicensenumber,
                    chapter
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('checkinResultMessage').innerText = data.message;
                    } else {
                        // If the check-in failed (e.g., already checked in), show the message in red with a danger icon
                        document.getElementById('checkinResultMessage').innerHTML = `
                <span style="color: red;">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message}
                </span>`;
                    }
                    $('#checkinResultModal').modal('show');

                    if (data.success) {
                        const releaseKitNow = confirm('Do you want to release the kit now?');
                        if (releaseKitNow) {
                            releaseKit(email);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('checkinResultMessage').innerText = 'Check-in failed: ' + error.message;
                    $('#checkinResultModal').modal('show');
                });
        }


        async function releaseKit(email) {
            if (!email) {
                console.error('No email provided for releasing kit.');
                document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Error: No email provided for releasing the kit.</span>';
                $('#checkinResultModal').modal('show');
                return;
            }

            try {
                const response = await fetch('releasekit', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const releaseTime = new Date(data.releaseTime);
                    const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }).format(releaseTime);
                    document.getElementById('checkinResultMessage').innerText = data.message;
                } else {
                    let messageContent = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;

                    // Check if releaseTime exists before formatting it
                    if (data.releaseTime) {
                        const releaseTime = new Date(data.releaseTime);
                        const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }).format(releaseTime);
                        messageContent += ` on ${formattedReleaseTime}`;
                    }

                    // Display the error message in red
                    document.getElementById('checkinResultMessage').innerHTML = `
                    <span style="color: red;">
                        ${messageContent}
                    </span>`;
                }


                $('#checkinResultModal').modal('show');
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Kit release failed: ' + error.message + '</span>';
                $('#checkinResultModal').modal('show');
            }


        }

        async function releaseMSP(email) {
            if (!email) {
                console.error('No email provided for releasing kit.');
                document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Error: No email provided for releasing the kit.</span>';
                $('#checkinResultModal').modal('show');
                return;
            }

            try {
                const response = await fetch('releasemsp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const releaseTime = new Date(data.releaseTime);
                    const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }).format(releaseTime);
                    document.getElementById('checkinResultMessage').innerText = data.message;
                } else {
                    let messageContent = `<i class="fas fa-exclamation-triangle"></i> ${data.message}`;

                    // Check if releaseTime exists before formatting it
                    if (data.releaseTime) {
                        const releaseTime = new Date(data.releaseTime);
                        const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: true
                        }).format(releaseTime);
                        messageContent += ` on ${formattedReleaseTime}`;
                    }

                    // Display the error message in red
                    document.getElementById('checkinResultMessage').innerHTML = `
                    <span style="color: red;">
                        ${messageContent}
                    </span>`;
                }


                $('#checkinResultModal').modal('show');
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('checkinResultMessage').innerHTML = '<span style="color: red;">Kit release failed: ' + error.message + '</span>';
                $('#checkinResultModal').modal('show');
            }


        }
    </script>


</body>

</html>
