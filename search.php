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
    <script src="js/html5-qrcode.min.js"></script>
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
                                    <button id="searchButton" type="button" class="btn btn-primary">Search</button>
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

    <script src="js/search.js"></script>
</body>

</html>
