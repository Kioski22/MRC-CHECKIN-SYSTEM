<?php
session_start();
include 'dbConnection.php';
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

include 'functions.php';

// Check for registration success and set a flag
$registration_success = isset($_SESSION['registration_success']) && $_SESSION['registration_success'] === true;
if ($registration_success) {
    // Unset the session variable so the message is only shown once
    unset($_SESSION['registration_success']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Register Individual | MINDANAO REGIONAL CONFERENCE</title>
    <link rel="icon" href="img/73rdNatCon.jpg" type="image/jpeg">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link href="css/custom.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Include jQuery first -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
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
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Bulk Registration (CSV)</h6>
                            <a href="download_bulk_registration_template.php" class="btn btn-sm btn-outline-primary">Download CSV Template</a>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="bulkCsvFile">Upload CSV File</label>
                                <input type="file" class="form-control-file" id="bulkCsvFile" accept=".csv">
                                <small class="form-text text-muted">Use the template file to ensure headers match exactly.</small>
                            </div>
                            <div id="bulkPreviewSummary" class="mb-3 text-muted"></div>
                            <div class="table-responsive" style="max-height: 360px; overflow-y: auto; display: none;" id="bulkPreviewWrapper">
                                <table class="table table-sm table-bordered" id="bulkPreviewTable">
                                    <thead class="thead-light"></thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                            <div id="bulkPreviewErrors" class="mt-3"></div>
                            <button type="button" class="btn btn-success mt-3" id="approveBulkUpload" disabled>Approve and Upload to Database</button>
                        </div>
                    </div>

                    <form method="post" action="submit_registration" id="singleRegistrationForm"> <!-- Unified form starts here -->
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card shadow mb-4">
                                            <div class="card-header py-3">
                                                <h6 class="m-0 font-weight-bold text-primary">Classification</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label for="membership_type">Please choose one</label>
                                                    <select class="form-control" id="membership_type" name="membership_type" required onchange="updatePricing()">
                                                        <option value="" disabled selected>Select membership type</option>
                                                        <option value="1">Life / Associate Member</option>
                                                        <option value="2">Delegate</option>
                                                        <option value="3">Regular Member</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="card shadow mb-4" id="other-details-card" style="display: none;">
                                            <div class="card-header py-3">
                                                <h6 class="m-0 font-weight-bold text-primary">Other Details</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="pwd" name="pwd" value="1" onchange="updatePWD()">
                                                    <label class="form-check-label" for="pwd">Person with Disability</label>
                                                </div>
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="senior_citizen" name="senior_citizen" value="1" onchange="updateSeniorCitizenPrice()">
                                                    <label class="form-check-label" for="senior_citizen">Senior Citizen</label>
                                                </div>
                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="new_board_passer" name="new_board_passer" value="1" onchange="updateNewBoardPasser()">
                                                    <label class="form-check-label" for="new_board_passer">New Board Passer</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="free" name="free" value="1" onchange="updateFree();">
                                                    <label class="form-check-label" for="free">Free Registration</label>
                                                </div>
                                                <!-- Reason for Free (initially hidden, now a textarea) -->
                                                <div class="form-group" id="freeReasonGroup" style="display: none;">
                                                    <textarea class="form-control mt-4" id="freeReason" name="freeReason" placeholder="Please provide the reason for free registration" rows="4" style="resize: none; white-space: pre-wrap;"></textarea>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="card shadow mb-4" id="personal-details-card" style="display: none;">
                                    <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Personal Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="first_name">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="middle_name">Middle Name <small class="text-muted">(Tick the checkbox if not applicable)</small></label>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check ml-3 mr-2" style="display: flex; align-items: center;">
                                                    <input type="checkbox" class="form-check-input" id="middle_name_na" name="middle_name_na" onclick="toggleMiddleName()">
                                                </div>
                                                <input type="text" class="form-control" id="middle_name" name="middle_name" required>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="last_name">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="name_suffix">Name Suffix <small class="text-muted">(Tick the checkbox if not applicable)</small></label>
                                            <div class="d-flex align-items-center">
                                                <div class="form-check ml-3 mr-2" style="display: flex; align-items: center;">
                                                    <input type="checkbox" class="form-check-input" id="suffix_na" onclick="toggleSuffix()">
                                                </div>
                                                <select class="form-control" id="name_suffix" name="name_suffix" required>
                                                    <option value="" selected disabled>Select suffix</option>
                                                    <option value="Jr.">Jr.</option>
                                                    <option value="Sr.">Sr.</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="personal_email">Email Address <small class="text-muted">(Personal recommended)</small></label>
                                            <input type="email" class="form-control" id="personal_email" name="personal_email" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="mobile_number">Mobile Number <small class="text-muted">(Use country code e.g. +63 for PH)</small></label>
                                            <input type="tel" class="form-control" id="mobile_number" name="mobile_number" placeholder="+63">
                                        </div>
                                        <div class="form-group">
                                            <label for="birthday">Birthday</label>
                                            <input type="text" class="form-control" id="birthday" name="birthday" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="card shadow mb-4" id="professional-details-card" style="display: none;">
                                            <div class="card-header py-3">
                                                <h6 class="m-0 font-weight-bold text-primary">Professional Details</h6>
                                            </div>
                                            <div class="card-body">
                                                <!-- PRC Type -->
                                                <div class="form-group">
                                                    <label for="prc_type">Type of PRC</label>
                                                    <select class="form-control" id="prc_type" name="prc_type" required>
                                                        <option value="" disabled selected>Select PRC Type</option>
                                                        <!-- Dynamically populated options here -->
                                                    </select>
                                                </div>

                                                <!-- PRC License Number -->
                                                <div class="form-group" id="prc_license_number_group" style="display: none;">
                                                    <label for="prc_license_number">PRC License Number</label>
                                                    <input type="text" class="form-control" id="prc_license_number" name="prc_license_number">
                                                </div>

                                                <!-- PRC Expiration Date -->
                                                <div class="form-group" id="prc_expiration_date_group" style="display: none;">
                                                    <label for="prc_expiration_date">PRC Expiration Date</label>
                                                    <input type="date" class="form-control" id="prc_expiration_date" name="prc_expiration_date">
                                                </div>

                                                <!-- PRC License Checkbox (for New Board Passers) -->
                                                <div class="form-group" id="prc_license_checkbox_group" style="display: none;">
                                                    <input type="checkbox" id="has_prc_license" name="has_prc_license">
                                                    <label for="has_prc_license">I already have a PRC License Number</label>
                                                </div>

                                                <!-- Sequence Number (for New Board Passers without PRC License) -->
                                                <div class="form-group" id="sequence_number_group" style="display: none;">
                                                    <label for="sequence_number">Sequence Number from the Official Passers List</label>
                                                    <input type="text" class="form-control" id="sequence_number" name="sequence_number">
                                                </div>

                                                <!-- Region -->
                                                <div class="form-group" id="region_group" style="display: none;">
                                                    <label for="region">Region</label>
                                                    <select class="form-control" id="region" name="region">
                                                        <option value="" disabled selected>Select Region</option>
                                                        <option value="NCR">NCR</option>
                                                        <option value="Luzon">Luzon</option>
                                                        <option value="Visayas">Visayas</option>
                                                        <option value="Mindanao">Mindanao</option>
                                                        <option value="International">International</option>
                                                    </select>
                                                </div>

                                                <!-- Chapter -->
                                                <div class="form-group" id="chapter_group" style="display: none;">
                                                    <label for="chapter">Chapter</label>
                                                    <select class="form-control" id="chapter" name="chapter" required>
                                                        <option value="" disabled selected>Choose region first</option>
                                                    </select>
                                                </div>

                                                <!-- Other -->
                                                <div class="form-group" id="other_group" style="display: none;">
                                                    <label for="other">Other profession <small class="text-muted">(e.g. Civil Engineer, Electrical Engineer, etc.)</label>
                                                    <input type="text" class="form-control" id="other" name="other">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="card shadow mb-4" id="proceed-card" style="display: none;">
                                            <div class="card-header py-3">
                                                <h6 class="m-0 font-weight-bold text-primary">Payment Method</h6>
                                            </div>
                                            <div class="card-body">
                                                <!-- Payment Method Selection -->
                                                <div class="form-group" id="pricing">
                                                    <p><strong>Registration Fee:</strong></p>
                                                    <strong>
                                                        <h1 id="price_detail">â‚±0.00</h1>
                                                    </strong>
                                                </div>
                                                <div class="form-group">
                                                    <label for="payment_method">Select Payment Method</label>
                                                    <select class="form-control" id="payment_method" name="payment_method" required>
                                                        <option value="cash" selected>Cash</option>
                                                        <option value="gcash">GCash</option>
                                                        <option value="bank_transfer">Bank Transfer</option>
                                                        <option value="check">Check</option>
                                                    </select>
                                                    <div class="form-group" id="payment_note_group">
                                                        <label for="payment_note" class="mt-2">Note / Reference No.</label>
                                                        <input type="text" class="form-control" id="payment_note" name="payment_note">
                                                    </div>
                                                </div>

                                                <div class="form-group form-check">
                                                    <input type="checkbox" class="form-check-input" id="payment_confirmation" required>
                                                    <label class="form-check-label" for="payment_confirmation" id="confirmation_label">
                                                        I confirm that the payment (cash, GCash, or bank transfer) has been received, and I acknowledge that my name will be recorded as the cashier for this transaction.
                                                    </label>
                                                </div>

                                                <!-- Accept Payment Button -->
                                                <button type="submit" class="btn btn-primary" id="accept_payment_button" disabled>Register</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form> <!-- Unified form ends here -->
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

    <!-- Include jQuery first (from local vendor or CDN) -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Core plugin JavaScript -->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Include Bootstrap JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Include Select2 JavaScript (after jQuery) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    <!-- Include Toastr JavaScript (after jQuery) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Custom scripts for all pages -->
    <script src="js/sb-admin-2.min.js"></script>

    <script>
        const registrationSuccess = <?php echo json_encode($registration_success); ?>;

        if (registrationSuccess) {
            // Call the showDiscountToast function with the message and title
            showDiscountToast("Registration successful.", "Success");
        }

        $(document).ready(function() {
            // Initialize Select2 on the chapter dropdown only
            $('#chapter').select2({
                placeholder: 'Choose region first',
                width: '100%' // Ensure the dropdown stretches to 100% width
            });


            // Fetch chapters dynamically when region is selected
            $('#region').on('change', function() {
                const selectedRegion = $(this).val();
                const chapterSelect = $('#chapter');

                // Clear current chapter options and set placeholder
                chapterSelect.html('<option value="" disabled selected>Loading chapters...</option>');

                // Fetch chapters via AJAX
                $.ajax({
                    url: 'fetch_chapters',
                    type: 'GET',
                    data: {
                        region: selectedRegion
                    },
                    dataType: 'json',
                    success: function(data) {
                        // Clear the chapter dropdown
                        chapterSelect.html('<option value="" disabled selected>Select Chapter</option>');

                        // Populate the chapter dropdown with new options
                        $.each(data, function(index, chapter) {
                            chapterSelect.append(new Option(chapter, chapter.toLowerCase().replace(/\s+/g, '-')));
                        });

                        // Trigger Select2 re-initialization on the updated chapter dropdown
                        chapterSelect.trigger('change');
                    },
                    error: function() {
                        chapterSelect.html('<option value="" disabled selected>Error loading chapters</option>');
                    }
                });
            });
        });

        const prcTypes = {
            1: [ // Associate / Life Member
                {
                    value: 'certified_plant_mechanic',
                    text: 'Certified Plant Mechanic'
                },
                {
                    value: 'registered_mechanical_engineer',
                    text: 'Registered Mechanical Engineer'
                },
                {
                    value: 'professional_mechanical_engineer',
                    text: 'Professional Mechanical Engineer'
                },
                {
                    value: 'me_graduate',
                    text: 'ME Graduate'
                }
            ],
            2: [ // Delegate
                {
                    value: 'certified_plant_mechanic',
                    text: 'Certified Plant Mechanic'
                },
                {
                    value: 'registered_mechanical_engineer',
                    text: 'Registered Mechanical Engineer'
                },
                {
                    value: 'professional_mechanical_engineer',
                    text: 'Professional Mechanical Engineer'
                },
                {
                    value: 'other',
                    text: 'Other'
                },
                {
                    value: 'not_applicable',
                    text: 'Not Applicable'
                }
            ],
            3: [ // Regular Member
                {
                    value: 'certified_plant_mechanic',
                    text: 'Certified Plant Mechanic'
                },
                {
                    value: 'registered_mechanical_engineer',
                    text: 'Registered Mechanical Engineer'
                },
                {
                    value: 'professional_mechanical_engineer',
                    text: 'Professional Mechanical Engineer'
                }
            ]
        };

        // Function to update PRC Type dropdown based on classification
        function updatePrcTypeOptions() {
            const classification = document.getElementById('membership_type').value;
            const prcSelect = document.getElementById('prc_type');

            // Clear existing options
            prcSelect.innerHTML = '<option value="" disabled selected>Select PRC Type</option>';

            // Add the relevant options based on classification
            if (classification && prcTypes[classification]) {
                prcTypes[classification].forEach(function(prc) {
                    const option = document.createElement('option');
                    option.value = prc.value;
                    option.text = prc.text;
                    prcSelect.appendChild(option);
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            const suffixSelect = document.getElementById("name_suffix");
            const romanNumerals = [
                "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII",
                "XIII", "XIV", "XV", "XVI", "XVII", "XVIII", "XIX", "XX"
            ];

            romanNumerals.forEach(numeral => {
                const option = document.createElement("option");
                option.value = numeral;
                option.textContent = numeral;
                suffixSelect.appendChild(option);
            });


            // Initialize flatpickr configuration
            flatpickr("#birthday", {
                dateFormat: "F j, Y", // Format month as word, then day and year
                disableMobile: true,
                onChange: function(selectedDates, dateStr, instance) {
                    const birthDate = new Date(selectedDates[0]);
                    const today = new Date();
                    let age = today.getFullYear() - birthDate.getFullYear();
                    const monthDifference = today.getMonth() - birthDate.getMonth();
                    const dayDifference = today.getDate() - birthDate.getDate();

                    if (monthDifference < 0 || (monthDifference === 0 && dayDifference < 0)) {
                        age--;
                    }

                    if (age >= 60) {
                        document.getElementById("senior_citizen").checked = true;
                        updateSeniorCitizenPrice();
                        //showDiscountToast('The participant is eligible for a senior citizen discount.', 'Discount Eligibility'); // Call the function to show the toast using Toastr
                    }
                }
            });

            const paymentMethodSelect = document.getElementById("payment_method");
            const confirmationLabel = document.getElementById("confirmation_label");
            const confirmationCheckbox = document.getElementById("payment_confirmation");
            const acceptPaymentButton = document.getElementById("accept_payment_button");

            // Update the confirmation text based on the selected payment method
            function updateConfirmationText() {
                const selectedPaymentMethod = paymentMethodSelect.value;
                let confirmationText = "I confirm that I have collected the ";

                if (selectedPaymentMethod === "cash") {
                    confirmationText += "Cash payment ";
                } else if (selectedPaymentMethod === "gcash") {
                    confirmationText += "GCash payment ";
                } else if (selectedPaymentMethod === "bank_transfer") {
                    confirmationText += "Bank Transfer payment ";
                } else if (selectedPaymentMethod === "check") {
                    confirmationText += "Check payment ";
                }

                confirmationText += "and I understand that my name as cashier is tagged with this transaction.";
                confirmationLabel.textContent = confirmationText;
            }

            // Enable or disable the accept payment button based on the checkbox
            confirmationCheckbox.addEventListener("change", function() {
                acceptPaymentButton.disabled = !confirmationCheckbox.checked;
            });

            // Update the confirmation label when the payment method changes
            paymentMethodSelect.addEventListener("change", function() {
                updateConfirmationText();
            });

            // Initial setup
            updateConfirmationText();
        });

        flatpickr("#prc_expiration_date", {
            dateFormat: "F j, Y", // Format month as word, then day and year
            disableMobile: true
        });

        function showDiscountToast($detail, $title) {
            console.log("Showing discount toast...");

            // Use Toastr to display the notification
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": false,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            toastr.success($detail, $title);
        }

        function updateDiscount(discountType, discountName) {
            const membershipSelect = document.getElementById("membership_type");
            const selectedValue = membershipSelect.value;

            // Define which membership types allow which discounts
            const allowedDiscounts = {
                "3": ["senior_citizen", "pwd", "free"],
                "2": ["free"],
                "1": ["senior_citizen", "pwd", "free"],
            };

            // Check if the discount can be applied based on selected membership type
            if (allowedDiscounts[selectedValue]?.includes(discountType)) {
                const discountCheckbox = document.getElementById(discountType);
                if (discountCheckbox.checked) {
                    if (discountType === 'free') {
                        showDiscountToast(`Registration fee waived for this participant.`, `Free Registration`);
                    } else {
                        showDiscountToast(`${discountName} discount applied.`, 'Registration Fee Updated');
                    }
                } else {
                    showDiscountToast(`${discountName} discount removed.`, 'Registration Fee Updated');
                }

                updatePricing();
            } else {
                const discountCheckbox = document.getElementById(discountType);
                if (discountCheckbox) {
                    discountCheckbox.checked = false;
                }
                // Show a toast if discount is not applicable for selected membership type
                showDiscountToast(`${discountName} discount is not applicable for this membership type.`, 'Discount Not Applicable');
                updatePricing();
            }
        }

        function updateSeniorCitizenPrice() {
            updateDiscount("senior_citizen", "Senior citizen");
        }

        function updatePWD() {
            updateDiscount("pwd", "PWD");
        }

        function updateNewBoardPasser() {
            updateDiscount("new_board_passer", "New Board Passer");
        }

        function updateFree() {
            const isFree = document.getElementById('free').checked;
            const freeReasonGroup = document.getElementById('freeReasonGroup');
            const freeReasonInput = document.getElementById('freeReason');

            if (isFree) {
                // Show the reason input and make it required
                freeReasonGroup.style.display = 'block';
                freeReasonInput.required = true;

                // Set the price to 0.00 and disable payment button
                document.getElementById('price_detail').textContent = "â‚±0.00";
                //document.getElementById('accept_payment_button').disabled = true;
            } else {
                // Hide the reason input and remove the required attribute
                freeReasonGroup.style.display = 'none';
                freeReasonInput.required = false;
                freeReasonInput.value = ''; // Clear the input field

                // Recalculate the pricing based on other discounts
                updatePricing(); // Call the function to update the pricing without Free
            }
        }



        // Main pricing function that adjusts prices based on membership type and discount
        function updatePricing() {
            const membershipType = document.getElementById("membership_type").value;
            const priceDetail = document.getElementById("price_detail");
            const isSenior = document.getElementById("senior_citizen")?.checked;
            const isPwd = document.getElementById("pwd")?.checked;
            const isFree = document.getElementById("free")?.checked;

            // Show the details card sections if a membership type is selected
            if (membershipType) {
                document.getElementById("other-details-card").style.display = "block";
                document.getElementById("personal-details-card").style.display = "block";
                document.getElementById("professional-details-card").style.display = "block";
                document.getElementById("proceed-card").style.display = "block";
            }

            if (isFree) {
                priceDetail.textContent = "â‚±0.00";
                updatePrcTypeOptions();
                return;
            }

            // Set the pricing based on the membership type and discount status
            switch (membershipType) {
                case "1": // Life / Associate Member
                    priceDetail.textContent = (isSenior || isPwd) ? "â‚±3,200.00" : "â‚±3,500.00";
                    break;
                case "2": // Delegate
                    priceDetail.textContent = "â‚±4,500.00";
                    break;
                case "3": // Regular Member
                    priceDetail.textContent = (isSenior || isPwd) ? "â‚±3,200.00" : "â‚±4,000.00";
                    break;
                default:
                    priceDetail.textContent = "â‚±0.00"; // Default price if nothing is selected
            }

            //document.getElementById('accept_payment_button').disabled = false; // Enable payment button when not Free
            updatePrcTypeOptions(); // If PRC type options should also change based on membership type
        }


        function toggleMiddleName() {
            const middleNameInput = document.getElementById("middle_name");
            const middleNameCheckbox = document.getElementById("middle_name_na");

            if (middleNameCheckbox.checked) {
                middleNameInput.value = "";
                middleNameInput.disabled = true;
                middleNameInput.required = false;
            } else {
                middleNameInput.disabled = false;
                middleNameInput.required = true;
            }
        }

        function toggleSuffix() {
            const suffixSelect = document.getElementById("name_suffix");
            const suffixCheckbox = document.getElementById("suffix_na");

            if (suffixCheckbox.checked) {
                suffixSelect.value = "";
                suffixSelect.disabled = true;
                suffixSelect.required = false;
            } else {
                suffixSelect.disabled = false;
                suffixSelect.required = true;
            }
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const prcTypeSelect = document.getElementById('prc_type');
            const newBoardPasserCheckbox = document.getElementById('new_board_passer');

            // Add debug logs to check if elements are present
            console.log(prcTypeSelect, newBoardPasserCheckbox);

            // Attach event listener to PRC Type select element
            if (prcTypeSelect) {
                prcTypeSelect.addEventListener('change', updateProfessionalDetails);
            }

            // Also run this function when "New Board Passer" checkbox changes
            if (newBoardPasserCheckbox) {
                newBoardPasserCheckbox.addEventListener('change', updateProfessionalDetails);
            }
        });

        function updateProfessionalDetails() {
            const prcType = document.getElementById('prc_type').value;
            const isNewBoardPasser = document.getElementById('new_board_passer').checked;

            // Elements to hide/show and require/not require
            const prcLicenseNumberGroup = document.getElementById('prc_license_number_group');
            const prcExpirationDateGroup = document.getElementById('prc_expiration_date_group');
            const regionGroup = document.getElementById('region_group');
            const chapterGroup = document.getElementById('chapter_group');
            const professionGroup = document.getElementById('other_group'); // Seems not defined in the HTML
            const sequenceNumberGroup = document.getElementById('sequence_number_group');
            const prcLicenseCheckboxGroup = document.getElementById('prc_license_checkbox_group');

            const prcLicenseNumber = document.getElementById('prc_license_number');
            const prcExpirationDate = document.getElementById('prc_expiration_date');
            const region = document.getElementById('region');
            const chapter = document.getElementById('chapter');
            const sequenceNumber = document.getElementById('sequence_number');
            const hasPrcLicense = document.getElementById('has_prc_license');

            // Ensure elements exist before trying to modify them
            if (!prcLicenseNumberGroup || !prcExpirationDateGroup || !regionGroup || !chapterGroup || !sequenceNumberGroup || !prcLicenseCheckboxGroup) {
                console.error("Some required elements are missing from the DOM.");
                return; // Stop execution if elements are missing
            }

            // Reset visibility and required attributes
            prcLicenseNumberGroup.style.display = 'none';
            prcExpirationDateGroup.style.display = 'none';
            regionGroup.style.display = 'none';
            chapterGroup.style.display = 'none';
            if (professionGroup) professionGroup.style.display = 'none'; // Additional check if profession group exists
            prcLicenseCheckboxGroup.style.display = 'none';
            sequenceNumberGroup.style.display = 'none';

            prcLicenseNumber.required = false;
            prcExpirationDate.required = false;
            region.required = false;
            chapter.required = false;
            sequenceNumber.required = false;

            // Check PRC Type
            if (prcType === 'certified_plant_mechanic' || prcType === 'registered_mechanical_engineer') {
                if (isNewBoardPasser) {
                    // Show PRC License Checkbox
                    prcLicenseCheckboxGroup.style.display = 'block';

                    // Handle PRC License checkbox logic
                    hasPrcLicense.addEventListener('change', function() {
                        if (this.checked) {
                            prcLicenseNumberGroup.style.display = 'block';
                            prcExpirationDateGroup.style.display = 'block';
                            prcLicenseNumber.required = true;
                            prcExpirationDate.required = true;
                            sequenceNumberGroup.style.display = 'none'; // Hide sequence number
                            sequenceNumber.required = false;
                        } else {
                            sequenceNumberGroup.style.display = 'block';
                            sequenceNumber.required = true;
                            prcLicenseNumberGroup.style.display = 'none'; // Hide PRC fields
                            prcExpirationDateGroup.style.display = 'none';
                        }
                    });

                    // Trigger the change event to handle the initial state
                    hasPrcLicense.dispatchEvent(new Event('change'));
                } else {
                    // Show normal PRC License fields if New Board Passer is not checked
                    prcLicenseNumberGroup.style.display = 'block';
                    prcExpirationDateGroup.style.display = 'block';
                    prcLicenseNumber.required = true;
                    prcExpirationDate.required = true;
                }

                // Show Region and Chapter fields
                regionGroup.style.display = 'block';
                chapterGroup.style.display = 'block';
                region.required = true;
                chapter.required = true;
            } else if (prcType === 'professional_mechanical_engineer') {
                prcLicenseNumberGroup.style.display = 'block';
                prcExpirationDateGroup.style.display = 'block';
                regionGroup.style.display = 'block';
                chapterGroup.style.display = 'block';

                prcLicenseNumber.required = true;
                prcExpirationDate.required = true;
                region.required = true;
                chapter.required = true;
            } else if (prcType === 'me_graduate') {
                regionGroup.style.display = 'block';
                chapterGroup.style.display = 'block';

                region.required = true;
                chapter.required = true;
            } else if (prcType === 'other') {
                if (professionGroup) professionGroup.style.display = 'block'; // Ensure profession group exists
                profession.required = true;
            } else if (prcType === 'not_applicable') {
                // Hide all input fields when not applicable
                prcLicenseNumberGroup.style.display = 'none';
                prcExpirationDateGroup.style.display = 'none';
                regionGroup.style.display = 'none';
                chapterGroup.style.display = 'none';
                if (professionGroup) professionGroup.style.display = 'none'; // Ensure profession group exists
            }
        }

        function submitFormWithRegistrationFee() {
            // Get the current price detail
            const priceDetail = document.getElementById("price_detail").innerText;

            // Extract the numerical value from the price detail (removing the currency symbol)
            const registrationFee = parseFloat(priceDetail.replace(/[â‚±,]/g, ''));

            // Set the registration fee as a hidden input value
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'registration_fee';
            hiddenInput.value = registrationFee.toFixed(2); // Keep two decimal places

            // Append the hidden input to the form
            document.querySelector("form").appendChild(hiddenInput);

            // Submit the form
            document.querySelector("form").submit();
        }

        // Attach an event listener to the single registration form submission
        document.getElementById("singleRegistrationForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            // Call the function to submit the form with registration fee
            submitFormWithRegistrationFee();
        });

        const BULK_REQUIRED_HEADERS = [
            'membership_type',
            'first_name',
            'last_name',
            'personal_email',
            'birthday',
            'prc_type',
            'payment_method'
        ];

        const BULK_HEADER_LABELS = {
            membership_type: 'Membership Type',
            pwd: 'PWD',
            senior_citizen: 'Senior Citizen',
            new_board_passer: 'New Board Passer',
            free: 'Free Registration',
            freeReason: 'Reason if Free',
            first_name: 'First Name',
            middle_name_na: 'No Middle Name',
            middle_name: 'Middle Name',
            last_name: 'Last Name',
            name_suffix: 'Suffix',
            personal_email: 'Email Address',
            mobile_number: 'Mobile Number',
            birthday: 'Birth Date',
            prc_type: 'PRC Type',
            prc_license_number: 'PRC License Number',
            prc_expiration_date: 'PRC Expiration Date',
            has_prc_license: 'Has PRC License',
            sequence_number: 'Sequence Number',
            region: 'Region',
            chapter: 'Chapter',
            other: 'Other',
            payment_method: 'Payment Method',
            payment_note: 'Payment Note'
        };

        const BULK_HEADER_ALIASES = {
            'membership_type': 'membership_type',
            'membership type (1=regular, 2=affiliate, 3=student)': 'membership_type',
            'membership type': 'membership_type',
            'pwd': 'pwd',
            'pwd (0=no, 1=yes)': 'pwd',
            'senior_citizen': 'senior_citizen',
            'senior citizen (0=no, 1=yes)': 'senior_citizen',
            'new_board_passer': 'new_board_passer',
            'new board passer (0=no, 1=yes)': 'new_board_passer',
            'free': 'free',
            'free registration (0=no, 1=yes)': 'free',
            'freereason': 'freeReason',
            'reason if free': 'freeReason',
            'first_name': 'first_name',
            'first name': 'first_name',
            'middle_name_na': 'middle_name_na',
            'no middle name (0=no, 1=yes)': 'middle_name_na',
            'middle_name': 'middle_name',
            'middle name': 'middle_name',
            'last_name': 'last_name',
            'last name': 'last_name',
            'name_suffix': 'name_suffix',
            'suffix (optional)': 'name_suffix',
            'personal_email': 'personal_email',
            'email address': 'personal_email',
            'mobile_number': 'mobile_number',
            'mobile number': 'mobile_number',
            'birthday': 'birthday',
            'birth date (yyyy-mm-dd)': 'birthday',
            'prc_type': 'prc_type',
            'prc type': 'prc_type',
            'prc_license_number': 'prc_license_number',
            'prc license number': 'prc_license_number',
            'prc_expiration_date': 'prc_expiration_date',
            'prc expiration date (yyyy-mm-dd)': 'prc_expiration_date',
            'has_prc_license': 'has_prc_license',
            'has prc license (0=no, 1=yes)': 'has_prc_license',
            'sequence_number': 'sequence_number',
            'sequence number (optional)': 'sequence_number',
            'region': 'region',
            'chapter': 'chapter',
            'other': 'other',
            'other (optional)': 'other',
            'payment_method': 'payment_method',
            'payment method (cash/gcash/bank_transfer/check)': 'payment_method',
            'payment_note': 'payment_note',
            'payment note (optional)': 'payment_note'
        };

        function normalizeCsvHeader(headerText) {
            return String(headerText || '').trim().toLowerCase();
        }

        function toCanonicalHeader(headerText) {
            const normalized = normalizeCsvHeader(headerText);
            return BULK_HEADER_ALIASES[normalized] || normalized;
        }

        function getHeaderLabel(headerKey) {
            return BULK_HEADER_LABELS[headerKey] || headerKey;
        }

        let bulkRowsForUpload = [];

        function parseCsvLine(line) {
            const result = [];
            let current = '';
            let inQuotes = false;

            for (let i = 0; i < line.length; i++) {
                const char = line[i];
                const nextChar = line[i + 1];

                if (char === '"') {
                    if (inQuotes && nextChar === '"') {
                        current += '"';
                        i++;
                    } else {
                        inQuotes = !inQuotes;
                    }
                } else if (char === ',' && !inQuotes) {
                    result.push(current.trim());
                    current = '';
                } else {
                    current += char;
                }
            }

            result.push(current.trim());
            return result;
        }

        function parseCsvText(csvText) {
            const lines = csvText
                .replace(/\r\n/g, '\n')
                .replace(/\r/g, '\n')
                .split('\n')
                .filter(line => line.trim() !== '');

            if (!lines.length) {
                return {
                    headers: [],
                    rows: []
                };
            }

            const headers = parseCsvLine(lines[0]).map(h => toCanonicalHeader(h));
            const rows = [];

            for (let i = 1; i < lines.length; i++) {
                const columns = parseCsvLine(lines[i]);
                const rowObj = {};

                headers.forEach((header, idx) => {
                    rowObj[header] = (columns[idx] || '').trim();
                });

                rowObj.__csv_row_number = i + 1;
                rows.push(rowObj);
            }

            return {
                headers,
                rows
            };
        }

        function validateBulkRows(headers, rows) {
            const errors = [];
            const headerSet = new Set(headers);

            BULK_REQUIRED_HEADERS.forEach(requiredHeader => {
                if (!headerSet.has(requiredHeader)) {
                    errors.push(`Missing required column: ${getHeaderLabel(requiredHeader)}`);
                }
            });

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            rows.forEach(row => {
                BULK_REQUIRED_HEADERS.forEach(requiredHeader => {
                    if (!row[requiredHeader]) {
                        errors.push(`Row ${row.__csv_row_number}: ${getHeaderLabel(requiredHeader)} is required.`);
                    }
                });

                if (row.personal_email && !emailRegex.test(row.personal_email)) {
                    errors.push(`Row ${row.__csv_row_number}: Email Address is invalid.`);
                }
            });

            return errors;
        }

        function renderBulkPreview(headers, rows, errors) {
            const summaryEl = document.getElementById('bulkPreviewSummary');
            const wrapperEl = document.getElementById('bulkPreviewWrapper');
            const tableHead = document.querySelector('#bulkPreviewTable thead');
            const tableBody = document.querySelector('#bulkPreviewTable tbody');
            const errorsEl = document.getElementById('bulkPreviewErrors');
            const approveBtn = document.getElementById('approveBulkUpload');

            if (!rows.length || !headers.length) {
                summaryEl.textContent = 'No data to preview.';
                wrapperEl.style.display = 'none';
                errorsEl.innerHTML = '';
                approveBtn.disabled = true;
                return;
            }

            summaryEl.textContent = `Parsed ${rows.length} row(s). Review below before upload.`;

            tableHead.innerHTML = `<tr>${headers.map(h => `<th>${getHeaderLabel(h)}</th>`).join('')}</tr>`;
            tableBody.innerHTML = rows.slice(0, 100).map(row => {
                const tds = headers.map(h => `<td>${(row[h] || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</td>`).join('');
                return `<tr>${tds}</tr>`;
            }).join('');
            wrapperEl.style.display = 'block';

            if (rows.length > 100) {
                summaryEl.textContent += ' Showing first 100 row(s) only.';
            }

            if (errors.length) {
                errorsEl.innerHTML = `<div class="alert alert-danger mb-0"><strong>Fix these first:</strong><ul class="mb-0 mt-2">${errors.slice(0, 20).map(e => `<li>${e}</li>`).join('')}</ul></div>`;
                approveBtn.disabled = true;
                bulkRowsForUpload = [];
            } else {
                errorsEl.innerHTML = '<div class="alert alert-success mb-0">CSV looks good. You can now approve and upload.</div>';
                approveBtn.disabled = false;
                bulkRowsForUpload = rows;
            }
        }

        document.getElementById('bulkCsvFile').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const csvText = e.target.result || '';
                const parsed = parseCsvText(csvText);
                const errors = validateBulkRows(parsed.headers, parsed.rows);
                renderBulkPreview(parsed.headers, parsed.rows, errors);
            };
            reader.readAsText(file);
        });

        document.getElementById('approveBulkUpload').addEventListener('click', async function() {
            if (!bulkRowsForUpload.length) {
                return;
            }

            const approveBtn = document.getElementById('approveBulkUpload');
            approveBtn.disabled = true;
            approveBtn.textContent = 'Uploading...';

            try {
                const response = await fetch('bulk_register_upload.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        rows: bulkRowsForUpload
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    const errorMessages = (result.errors || [result.message || 'Upload failed'])
                        .slice(0, 20)
                        .map(err => `<li>${String(err).replace(/</g, '&lt;').replace(/>/g, '&gt;')}</li>`)
                        .join('');

                    document.getElementById('bulkPreviewErrors').innerHTML = `<div class="alert alert-danger mb-0"><strong>Upload failed:</strong><ul class="mb-0 mt-2">${errorMessages}</ul></div>`;
                    return;
                }

                document.getElementById('bulkPreviewErrors').innerHTML = `<div class="alert alert-success mb-0">${result.message}</div>`;
                document.getElementById('bulkCsvFile').value = '';
                document.getElementById('bulkPreviewSummary').textContent = '';
                document.getElementById('bulkPreviewWrapper').style.display = 'none';
                bulkRowsForUpload = [];
            } catch (error) {
                document.getElementById('bulkPreviewErrors').innerHTML = `<div class="alert alert-danger mb-0">Unexpected error while uploading CSV.</div>`;
            } finally {
                approveBtn.textContent = 'Approve and Upload to Database';
                approveBtn.disabled = false;
            }
        });
    </script>

</body>

</html>
