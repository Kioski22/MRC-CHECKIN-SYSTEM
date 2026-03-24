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

    <title>Register Individual | 73rd National Convention</title>
    <link rel="icon" href="img/logo.ico" type="image/icon type">

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
                    <form method="post" action="submit_registration"> <!-- Unified form starts here -->
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
                                                        <option value="2">Guest / Non-member</option>
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
                                                        <h1 id="price_detail">₱0.00</h1>
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
            2: [ // Guest / Non-member
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
            const priceDetail = document.getElementById("price_detail");
            const currentPrice = parseFloat(priceDetail.textContent.replace(/[₱,]/g, '')); // Parse the current price as a number

            // Define which membership types allow which discounts
            const allowedDiscounts = {
                "3": ["senior_citizen", "pwd", "new_board_passer", "free"], // Regular Member can apply all discounts
                "2": ["new_board_passer", "free"], // Guest/Non-member can apply New Board Passer discount and Free
                "1": ["free"], // Life / Associate Member can only apply Free discount
            };

            // Check if the discount can be applied based on selected membership type
            if (allowedDiscounts[selectedValue]?.includes(discountType)) {
                const discountCheckbox = document.getElementById(discountType);
                const otherDiscountTypes = allowedDiscounts[selectedValue].filter(type => type !== discountType);

                if (discountCheckbox.checked) {
                    if (discountType === 'free') {
                        // If Free is checked, set price to 0 and disable payment
                        priceDetail.textContent = "₱0.00";
                        //document.getElementById('accept_payment_button').disabled = true;
                        showDiscountToast(`Registration fee waived for this participant.`, `Free Registration`);
                    } else {
                        // Handle other discounts only if Free is not checked
                        if (!document.getElementById('free').checked) {
                            if (currentPrice === 2800) {
                                // If the current price is already 2800, show a message and stop applying further discounts
                                showDiscountToast('No further discounts can be applied. Maximum discount reached.', 'Discount Not Applicable');
                            } else {
                                // Apply discount and reduce price to 2800
                                priceDetail.textContent = "₱2,800.00";
                                showDiscountToast(`${discountName} discount applied.`, 'Registration Fee Updated');
                            }
                        }
                    }
                } else {
                    // Handle discount removal and ensure Free is prioritized if still checked
                    const anyOtherChecked = otherDiscountTypes.some(type => document.getElementById(type).checked);
                    if (!anyOtherChecked) {
                        updatePricing(); // Recalculate price
                    }
                    showDiscountToast(`${discountName} discount removed.`, 'Registration Fee Updated');
                }
            } else {
                // Show a toast if discount is not applicable for selected membership type
                showDiscountToast(`${discountName} discount is not applicable for this membership type.`, 'Discount Not Applicable');
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
                document.getElementById('price_detail').textContent = "₱0.00";
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
            const discountCheckboxes = ["senior_citizen", "pwd", "new_board_passer", "free"];
            let discountApplied = false;

            // Show the details card sections if a membership type is selected
            if (membershipType) {
                document.getElementById("other-details-card").style.display = "block";
                document.getElementById("personal-details-card").style.display = "block";
                document.getElementById("professional-details-card").style.display = "block";
                document.getElementById("proceed-card").style.display = "block";
            }

            // Check if any discount checkboxes are already checked
            discountCheckboxes.forEach((checkboxId) => {
                const checkbox = document.getElementById(checkboxId);
                if (checkbox && checkbox.checked) {
                    discountApplied = true;
                }
            });

            // Set the pricing based on the membership type and discount status
            switch (membershipType) {
                case "1": // Life / Associate Member
                    priceDetail.textContent = discountApplied ? "₱2,800.00" : "₱2,800.00";
                    break;
                case "2": // Guest / Non-member
                    if (document.getElementById("new_board_passer").checked) {
                        priceDetail.textContent = "₱2,800.00"; // Discounted price for Guest / Non-member if New Board Passer discount is applied
                    } else {
                        priceDetail.textContent = "₱4,500.00"; // Regular price for Guest / Non-member
                    }
                    break;
                case "3": // Regular Member
                    if (discountApplied) {
                        priceDetail.textContent = "₱2,800.00"; // Discounted price for Regular Member if discount is applied
                    } else {
                        priceDetail.textContent = "₱3,500.00"; // Regular price for Regular Member
                    }
                    break;
                default:
                    priceDetail.textContent = "₱0.00"; // Default price if nothing is selected
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
            const registrationFee = parseFloat(priceDetail.replace(/[₱,]/g, ''));

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

        // Attach an event listener to the form submission
        document.querySelector("form").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            // Call the function to submit the form with registration fee
            submitFormWithRegistrationFee();
        });
    </script>

</body>

</html>