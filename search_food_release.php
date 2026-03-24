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
include 'test_config.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Food Release Search | 73rd National Convention</title>
    <link rel="icon" href="img/logo.ico" type="image/icon type">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <style>
        .food-release-card {
            border-left: 4px solid #28a745;
        }
        
        .release-status-badge {
            font-size: 0.8em;
            padding: 0.25rem 0.5rem;
            margin: 0.1rem;
        }
        
        .current-date-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .convention-date {
            font-weight: bold;
            color: #ffc107;
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
                        <h1 class="h3 mb-0 text-gray-800">Food Release Search</h1>
                    </div>

                    <!-- Current Date Information -->
                    <div class="current-date-info">
                        <h5 class="mb-2"><i class="fas fa-calendar-day"></i> Convention Status</h5>
                        <p class="mb-1">Today: <span class="convention-date"><?php echo date('F j, Y', strtotime(getCurrentDate())); ?></span></p>
                        <?php
                        $current_date = getCurrentDate();
                        $convention_dates = [
                            '2025-10-16' => 'Day 1',
                            '2025-10-17' => 'Day 2', 
                            '2025-10-18' => 'Day 3'
                        ];
                        
                        if (isset($convention_dates[$current_date])) {
                            echo '<p class="mb-0">Convention: <span class="convention-date">' . $convention_dates[$current_date] . ' - Food Distribution Active</span></p>';
                        } else if ($current_date < '2025-10-16') {
                            echo '<p class="mb-0">Status: <span class="badge badge-info">Pre-Convention - Food Distribution Not Yet Started</span></p>';
                        } else {
                            echo '<p class="mb-0">Status: <span class="badge badge-secondary">Post-Convention - Food Distribution Ended</span></p>';
                        }
                        ?>
                    </div>

                    <!-- Search Card -->
                    <div class="row">
                        <div class="col-xl-12 col-lg-12">
                            <div class="card shadow mb-4 food-release-card">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-success">Search Food Registrant</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="searchInput" placeholder="Enter email address, name, or PRC license number...">
                                                <div class="input-group-append">
                                                    <button class="btn btn-success" type="button" id="searchButton">
                                                        <i class="fas fa-search"></i> Search
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Search by email address, name, or PRC license number to find registrants
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center">
                                                <i class="fas fa-utensils fa-3x text-success mb-2"></i>
                                                <p class="text-muted mb-0">Food Distribution</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search Results Card -->
                    <div class="card shadow mb-4" id="searchResultsCard" style="display: none;">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Search Results</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="searchResultsTableContainer">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Chapter</th>
                                            <th>Release Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="searchResultsTable">
                                        <!-- Results will be populated here -->
                                    </tbody>
                                </table>
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

    <!-- Food Release Details Modal -->
    <div class="modal fade" id="foodReleaseModal" tabindex="-1" role="dialog" aria-labelledby="foodReleaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="foodReleaseModalLabel">
                        <i class="fas fa-utensils"></i> Food Release Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><strong>Registrant Information</strong></h6>
                            <p><strong>Name:</strong> <span id="modalFullName"></span></p>
                            <p><strong>Email:</strong> <span id="modalEmail"></span></p>
                            <p><strong>PRC License:</strong> <span id="modalPrcLicense"></span></p>
                            <p><strong>Chapter:</strong> <span id="modalChapter"></span></p>
                        </div>
                        <div class="col-md-6">
                            <h6><strong>Release Status</strong></h6>
                            <div id="modalReleaseStatus">
                                <!-- Release status will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6><strong>Today's Action</strong></h6>
                            <div id="modalActionSection">
                                <!-- Action buttons will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Food Release Result Modal -->
    <div class="modal fade" id="foodReleaseResultModal" tabindex="-1" role="dialog" aria-labelledby="foodReleaseResultModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="foodReleaseResultModalLabel">Food Release Result</h5>
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
                    <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Modal-->
    <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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

    <script>
        let searchResultsGlobal = [];
        let currentSelectedRegistrant = null;

        document.getElementById('searchButton').addEventListener('click', async () => {
            const searchValue = document.getElementById('searchInput').value.trim();
            if (searchValue) {
                await searchForFoodRegistrant(searchValue);
            } else {
                alert('Please enter a search term!');
            }
        });

        // Allow Enter key to trigger search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchButton').click();
            }
        });

        async function searchForFoodRegistrant(searchValue) {
            try {
                // Show loading state
                const searchButton = document.getElementById('searchButton');
                const originalText = searchButton.innerHTML;
                searchButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
                searchButton.disabled = true;

                // Search in internal food database and external API
                const response = await fetch('searchFoodRegistrant', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        search: searchValue
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    handleSearchResults(data);
                } else {
                    throw new Error('Search request failed');
                }

            } catch (error) {
                console.error('Error:', error);
                alert('Error during search: ' + error.message);
            } finally {
                // Restore button state
                const searchButton = document.getElementById('searchButton');
                searchButton.innerHTML = '<i class="fas fa-search"></i> Search';
                searchButton.disabled = false;
            }
        }

        function handleSearchResults(data) {
            if (data.success && data.results && data.results.length > 0) {
                searchResultsGlobal = data.results;
                const tableBody = document.getElementById('searchResultsTable');
                tableBody.innerHTML = '';
                
                searchResultsGlobal.forEach((result, index) => {
                    const row = document.createElement('tr');
                    
                    // Build full name
                    let fullName = `${result.first_name || ''} ${result.middle_name || ''} ${result.last_name || ''}`.trim();
                    if (result.suffix) fullName += ` ${result.suffix}`;
                    
                    // Release status badges
                    let statusBadges = '';
                    if (result.release_status) {
                        const status = result.release_status;
                        statusBadges += status.day1_released ? '<span class="badge badge-success release-status-badge">Day 1 ✓</span>' : '<span class="badge badge-light release-status-badge">Day 1</span>';
                        statusBadges += status.day2_released ? '<span class="badge badge-success release-status-badge">Day 2 ✓</span>' : '<span class="badge badge-light release-status-badge">Day 2</span>';
                        statusBadges += status.day3_released ? '<span class="badge badge-success release-status-badge">Day 3 ✓</span>' : '<span class="badge badge-light release-status-badge">Day 3</span>';
                    } else {
                        statusBadges = '<span class="badge badge-secondary">Not Registered for Food</span>';
                    }
                    
                    row.innerHTML = `
                        <td>${fullName}</td>
                        <td>${result.email || result.email_address || 'N/A'}</td>
                        <td>${result.chapter || 'N/A'}</td>
                        <td>${statusBadges}</td>
                        <td><button class="btn btn-primary btn-sm" onclick="viewFoodReleaseDetails(${index})">
                            <i class="fas fa-eye"></i> View Details
                        </button></td>
                    `;
                    tableBody.appendChild(row);
                });
                
                document.getElementById('searchResultsCard').style.display = 'block';
            } else {
                alert(data.message || 'No registrants found for the search term');
                document.getElementById('searchResultsCard').style.display = 'none';
            }
        }

        async function viewFoodReleaseDetails(index) {
            const registrant = searchResultsGlobal[index];
            currentSelectedRegistrant = registrant;
            
            // Populate modal with registrant information
            document.getElementById('modalFullName').textContent = 
                `${registrant.first_name || ''} ${registrant.middle_name || ''} ${registrant.last_name || ''}`.trim() + 
                (registrant.suffix ? ` ${registrant.suffix}` : '');
            document.getElementById('modalEmail').textContent = registrant.email || registrant.email_address || 'N/A';
            document.getElementById('modalPrcLicense').textContent = registrant.prclicencenumber || 'N/A';
            document.getElementById('modalChapter').textContent = registrant.chapter || 'N/A';
            
            // Get detailed release status if registrant has food registration
            if (registrant.food_id) {
                await loadDetailedReleaseStatus(registrant.food_id);
            } else {
                // Show that this person is not registered for food
                document.getElementById('modalReleaseStatus').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> This person is not registered for food distribution.
                    </div>
                `;
                document.getElementById('modalActionSection').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No food release actions available - person not registered.
                    </div>
                `;
            }
            
            $('#foodReleaseModal').modal('show');
        }

        async function loadDetailedReleaseStatus(foodId) {
            try {
                // Store the current registrant ID for later use
                window.currentRegistrantId = foodId;
                
                const response = await fetch(`getFoodReleaseStatus?id=${foodId}`);
                if (response.ok) {
                    const data = await response.json();
                    if (data.success) {
                        displayDetailedReleaseStatus(data.data);
                    } else {
                        throw new Error(data.message);
                    }
                } else {
                    throw new Error('Failed to load release status');
                }
            } catch (error) {
                console.error('Error loading release status:', error);
                document.getElementById('modalReleaseStatus').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Error loading release status: ${error.message}
                    </div>
                `;
                document.getElementById('modalActionSection').innerHTML = '';
            }
        }

        function displayDetailedReleaseStatus(releaseData) {
            const { daily_status, release_summary, release_history } = releaseData;
            
            // Display release status
            let statusHtml = `
                <div class="mb-3">
                    <p><strong>Total Releases:</strong> ${release_summary.total_releases}/3 days</p>
                </div>
                <div class="row">
            `;
            
            Object.keys(daily_status).forEach(day => {
                const dayInfo = daily_status[day];
                const badgeClass = dayInfo.released ? 'success' : 'light';
                const icon = dayInfo.released ? 'check-circle' : 'circle';
                
                statusHtml += `
                    <div class="col-4 text-center mb-2">
                        <div class="badge badge-${badgeClass} p-2" style="display: block;">
                            <i class="fas fa-${icon}"></i><br>
                            ${day.toUpperCase()}<br>
                            <small>${dayInfo.date}</small>
                        </div>
                        ${dayInfo.released ? `<small class="text-muted">Released</small>` : `<small class="text-muted">Not Released</small>`}
                    </div>
                `;
            });
            
            statusHtml += '</div>';
            
            // Add release history section if there are any releases
            if (release_history && release_history.length > 0) {
                statusHtml += `
                    <hr>
                    <h6><strong>Release History</strong></h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Day</th>
                                    <th>Released By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                `;
                
                release_history.forEach(release => {
                    const releaseDateTime = new Date(release.release_time);
                    const formattedDate = releaseDateTime.toLocaleDateString();
                    const formattedTime = releaseDateTime.toLocaleTimeString();
                    
                    statusHtml += `
                        <tr>
                            <td>${formattedDate}</td>
                            <td>${formattedTime}</td>
                            <td>${release.convention_day}</td>
                            <td>${release.released_by_username || 'Unknown'}</td>
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
            
            document.getElementById('modalReleaseStatus').innerHTML = statusHtml;
            
            // Display action section
            const currentDate = '<?php echo getCurrentDate(); ?>';
            const conventionDates = ['2025-10-16', '2025-10-17', '2025-10-18'];
            const isConventionPeriod = conventionDates.includes(currentDate);
            
            // Check if food can actually be released today based on the daily status
            let canReleaseToday = false;
            if (isConventionPeriod) {
                const todayKey = currentDate === '2025-10-16' ? 'day1' : 
                                currentDate === '2025-10-17' ? 'day2' : 
                                currentDate === '2025-10-18' ? 'day3' : null;
                
                if (todayKey && daily_status[todayKey]) {
                    canReleaseToday = !daily_status[todayKey].released;
                }
            }
            
            if (isConventionPeriod && canReleaseToday) {
                document.getElementById('modalActionSection').innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-utensils"></i> Food can be released today!
                    </div>
                    <button class="btn btn-success" onclick="releaseFoodToday()">
                        <i class="fas fa-utensils"></i> Release Food for Today
                    </button>
                `;
            } else if (isConventionPeriod && !canReleaseToday) {
                document.getElementById('modalActionSection').innerHTML = `
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i> Food has already been released today for this registrant.
                    </div>
                `;
            } else if (!isConventionPeriod) {
                document.getElementById('modalActionSection').innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-calendar-times"></i> Food release is only available during convention dates (Oct 16-18, 2025).
                    </div>
                `;
            }
        }

        async function releaseFoodToday() {
            if (!currentSelectedRegistrant || !currentSelectedRegistrant.food_id) {
                alert('Error: No registrant selected');
                return;
            }
            
            if (!confirm('Are you sure you want to release food to this registrant for today?')) {
                return;
            }
            
            try {
                const response = await fetch('releaseFoodToday', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        food_id: currentSelectedRegistrant.food_id
                    })
                });
                
                const data = await response.json();
                
                // Close the details modal
                $('#foodReleaseModal').modal('hide');
                
                // Show result modal
                if (data.success) {
                    document.getElementById('foodReleaseResultMessage').innerHTML = `
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> ${data.message}
                        </div>
                    `;
                } else {
                    document.getElementById('foodReleaseResultMessage').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> ${data.message}
                        </div>
                    `;
                }
                
                $('#foodReleaseResultModal').modal('show');
                
                // Refresh search results if successful
                if (data.success) {
                    const searchValue = document.getElementById('searchInput').value.trim();
                    if (searchValue) {
                        setTimeout(() => {
                            searchForFoodRegistrant(searchValue);
                        }, 1000);
                    }
                }
                
            } catch (error) {
                console.error('Error:', error);
                document.getElementById('foodReleaseResultMessage').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> Network error: ${error.message}
                    </div>
                `;
                $('#foodReleaseModal').modal('hide');
                $('#foodReleaseResultModal').modal('show');
            }
        }
        
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
                        await loadDetailedReleaseStatus(currentRegistrantId);
                    }
                    
                    // Refresh search results
                    const searchValue = document.getElementById('searchInput').value.trim();
                    if (searchValue) {
                        setTimeout(() => {
                            searchForFoodRegistrant(searchValue);
                        }, 500);
                    }
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