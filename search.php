<?php
session_start();
include 'dbConnection.php';

if (!isset($_SESSION['access_token'])) {
    header("Location: login2.php");
    exit();
}

if (!isset($_SESSION['user_id']) || (!isset($_SESSION['username']))) {
    header("Location: login2.php");
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

    <title>Search | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">
    <script src="js\html5-qrcode.min.js"></script>
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

                            <!-- Search Box and Search Button -->
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Search Registration</h6>
                                </div>
                                <div class="card-body">
                                    <input type="text" id="searchInput" class="form-control mb-3" placeholder="Enter name, email address, or PRC ID">
                                    <button id="searchButton" type="button" class="btn btn-primary" onclick="handleSearchClick()">Search</button>
                                    <div id="searchStatus" class="mt-3"></div>
                                </div>
                            </div>

                            <div class="card shadow mb-4" id="searchResultsCard" style="display: none;">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Search Results</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead>
                                                <tr>
                                                    <th>First Name</th>
                                                    <th>Last Name</th>
                                                    <th>Email</th>
                                                    <th>Chapter</th>
                                                    <th>Registration Type</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="searchResultsTable">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                    <!-- Modal for Scan Result -->
                    <div class="modal fade" id="scanResultModal" tabindex="-1" role="dialog" aria-labelledby="scanResultModalLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="scanResultModalLabel">Scan Result</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
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
                                    <p><strong>QR Code:</strong> <a id="modalQrcodeLink" href="" target="_blank">QR Code</a></p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" onclick="printLabel()">Print Only</button>
                                    <button type="button" class="btn btn-primary" onclick="editBeforePrint()">Edit before Print</button>
                                    <button type="button" class="btn btn-success" onclick="checkIn()">Check In Only</button>
                                    <button type="button" class="btn btn-warning" onclick="releaseKit(document.getElementById('modalEmail').innerText)">Release Kit Only</button>
                                    <button type="button" class="btn btn-warning" onclick="releaseMSP(document.getElementById('modalEmail').innerText)">Release MSP Only</button>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Before Print Modal -->
                    <div class="modal fade" id="editPrintModal" tabindex="-1" role="dialog" aria-labelledby="editPrintModalLabel" aria-hidden="true">
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
                                            <label for="editMiddlename">Middle Name: <small class="text-muted">(N/A will not be printed)</small></label>
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
                                    <button type="button" class="btn btn-primary" onclick="saveAndPrint()">Print</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Check-In and Release Kit Result Modal -->
                    <div class="modal fade" id="checkinResultModal" tabindex="-1" role="dialog" aria-labelledby="checkinResultModalLabel" aria-hidden="true">
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
        let resultValuesGlobal = [];

        function setSearchStatus(message, type = 'info') {
            const searchStatus = document.getElementById('searchStatus');
            if (!searchStatus) {
                return;
            }

            const alertClass = {
                info: 'alert-info',
                success: 'alert-success',
                warning: 'alert-warning',
                danger: 'alert-danger'
            }[type] || 'alert-info';

            searchStatus.innerHTML = `<div class="alert ${alertClass} mb-0" role="alert">${message}</div>`;
        }

        function clearSearchStatus() {
            const searchStatus = document.getElementById('searchStatus');
            if (searchStatus) {
                searchStatus.innerHTML = '';
            }
        }

        function clearSearchResults() {
            resultValuesGlobal = [];
            document.getElementById('searchResultsTable').innerHTML = '';
            document.getElementById('searchResultsCard').style.display = 'none';
        }

        async function handleSearchClick() {
            const searchValue = document.getElementById('searchInput').value;
            if (searchValue) {
                await makeApiRequest(searchValue);
            } else {
                clearSearchResults();
                setSearchStatus('Search box is empty.', 'warning');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            if (searchButton && !searchButton.dataset.listenerAttached) {
                searchButton.addEventListener('click', handleSearchClick);
                searchButton.dataset.listenerAttached = 'true';
            }

            if (searchInput) {
                searchInput.addEventListener('keydown', async (event) => {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                        await handleSearchClick();
                    }
                });
            }
        });

        function insertCheckinData(data) {
            fetch('insertCheckinData.php', {
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
                        console.error('Error inserting check-in data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        async function makeApiRequest(searchValue) {
            const searchButton = document.getElementById('searchButton');
            try {
                if (searchButton) {
                    searchButton.disabled = true;
                    searchButton.innerText = 'Searching...';
                }

                clearSearchStatus();
                clearSearchResults();
                setSearchStatus('Searching registration records...', 'info');

                const response = await fetch('ictosearch.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `search_term=${encodeURIComponent(searchValue)}`
                });

                if (!response.ok) {
                    throw new Error(`Search request failed with status ${response.status}`);
                }

                const data = await response.json();
                handleScanData(data);
            } catch (error) {
                console.error('Error:', error);
                clearSearchResults();
                setSearchStatus(`Search failed: ${error.message}`, 'danger');
            } finally {
                if (searchButton) {
                    searchButton.disabled = false;
                    searchButton.innerText = 'Search';
                }
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
                const response = await fetch('releasemsp.php', {
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


        function handleScanData(data) {
            if (data.resultValue && data.resultValue.length > 0) {
                resultValuesGlobal = data.resultValue;
                const tableBody = document.getElementById('searchResultsTable');
                tableBody.innerHTML = '';
                resultValuesGlobal.forEach((result, index) => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${result.firstname || 'N/A'}</td>
                    <td>${result.lastname || 'N/A'}</td>
                    <td>${result.email || 'N/A'}</td>
                    <td>${result.chapter || 'N/A'}</td>
                    <td>${result.RegistrationType || 'N/A'}</td>
                    <td><button class="btn btn-primary" onclick="showModalWithIndex(${index})">View</button></td>
                `;
                    tableBody.appendChild(row);
                });
                document.getElementById('searchResultsCard').style.display = 'block';
                setSearchStatus(`Found ${data.resultValue.length} matching registration record(s).`, 'success');
            } else {
                clearSearchResults();
                setSearchStatus(data.errorMsg || 'No valid data found.', 'warning');
            }
        }

        function showModalWithIndex(index) {
            const result = resultValuesGlobal[index];

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

            // Show the modal with the scan result details
            $('#scanResultModal').modal('show');
        }

        function editBeforePrint() {
            // Populate the edit form with current values
            document.getElementById('editFirstname').value = document.getElementById('modalFirstname').innerText;
            document.getElementById('editLastname').value = document.getElementById('modalLastname').innerText;
            document.getElementById('editMiddlename').value = document.getElementById('modalMiddlename').innerText;
            document.getElementById('editChapter').value = document.getElementById('modalChapter').innerText;

            // Show the edit modal
            $('#editPrintModal').modal('show');
        }

        function saveAndPrint() {
            // Update modal values with edited values
            document.getElementById('modalFirstname').innerText = document.getElementById('editFirstname').value;
            document.getElementById('modalLastname').innerText = document.getElementById('editLastname').value;
            document.getElementById('modalMiddlename').innerText = document.getElementById('editMiddlename').value;
            document.getElementById('modalChapter').innerText = document.getElementById('editChapter').value;

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
            let qrcodeLink = document.getElementById('modalQrcodeLink').href;

            // Debug: Log the QR code link
            console.log('Original QR Code Link:', qrcodeLink);

            // Handle QR code URL for print window
            if (qrcodeLink && qrcodeLink !== '#') {
                // Check if it's an external AWS S3 URL
                if (qrcodeLink.includes('psmefiles.s3.ap-southeast-1.amazonaws.com')) {
                    console.log('Detected AWS S3 QR code, checking for local alternative...');
                    
                    // Try to find the email to construct a local ICTO QR code path
                    const email = document.getElementById('modalEmail').innerText;
                    if (email && email !== 'N/A') {
                        // Try to construct local QR code path based on email or other identifier
                        // For now, we'll use the external URL but encoded
                        qrcodeLink = qrcodeLink.replace(/\|/g, '%7C');
                        console.log('Using encoded AWS URL:', qrcodeLink);
                    }
                } else if (qrcodeLink.includes('http://') || qrcodeLink.includes('https://')) {
                    // Other external URLs - encode problematic characters
                    qrcodeLink = qrcodeLink.replace(/\|/g, '%7C');
                    console.log('Using encoded external URL:', qrcodeLink);
                } else {
                    // Local relative path - convert to absolute URL
                    const baseUrl = window.location.origin + window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
                    
                    // Ensure the path starts with uploads/qrcode/ if it's a QR file
                    if (qrcodeLink.includes('.png') && !qrcodeLink.startsWith('uploads/qrcode/')) {
                        const filename = qrcodeLink.split('/').pop();
                        qrcodeLink = 'uploads/qrcode/' + filename;
                    }
                    
                    // Create absolute URL for the print window
                    qrcodeLink = baseUrl + qrcodeLink;
                    console.log('Converted to absolute local URL:', qrcodeLink);
                }
            }

            // If QR code link is missing or invalid, use a placeholder
            if (!qrcodeLink || qrcodeLink === '#' || qrcodeLink === '') {
                console.warn('No valid QR code link found, using placeholder');
                qrcodeLink = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2Y4ZjlmYSIvPjx0ZXh0IHg9IjUwIiB5PSI1MCIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEyIiBmaWxsPSIjNjc4IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+Tm8gUVIgQ29kZTwvdGV4dD48L3N2Zz4=';
            }

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
                    <img src="${qrcodeLink}" alt="QR Code" style="width: 33mm; height: 33mm; box-sizing: border-box;" 
                         onload="console.log('QR Code loaded successfully');"
                         onerror="this.style.display='none'; this.parentNode.innerHTML='<div style=&quot;width:33mm;height:33mm;border:1px solid #ccc;display:flex;align-items:center;justify-content:center;font-size:8px;color:#666;text-align:center;&quot;>QR Code<br>Not Available</div>';">
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
                    // Display error message with red text and danger icon
                    const releaseTime = new Date(data.releaseTime);
                    const formattedReleaseTime = new Intl.DateTimeFormat('en-PH', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    }).format(releaseTime);

                    document.getElementById('checkinResultMessage').innerHTML = `
                <span style="color: red;">
                    <i class="fas fa-exclamation-triangle"></i> ${data.message} on ${formattedReleaseTime}
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

    <script>
        if (window.feather && typeof window.feather.replace === 'function') {
            window.feather.replace();
        }
    </script>
</body>

</html>
