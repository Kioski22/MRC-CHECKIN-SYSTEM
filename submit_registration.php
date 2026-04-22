<?php
session_start();
require('dbConnection.php');  // Database connection
require('libs/phpqrcode/qrlib.php');
require 'gmail_api_mailer.php';

// Function to generate a unique registration ID number
function generateUniqueRegistrationNumber($db)
{
    do {
        // Generate a random 6-digit number
        $random_number = rand(100000, 999999);

        $count = 0;

        // Check if the generated number already exists in the registration_id column
        $check_query = $db->prepare("SELECT COUNT(*) FROM registrations WHERE registration_id = ?");
        $check_query->bind_param("i", $random_number);
        $check_query->execute();
        $check_query->bind_result($count);
        $check_query->fetch();
        $check_query->close();

        // Repeat if the ID already exists
    } while ($count > 0);

    return $random_number;
}

// Generate the unique 6-digit registration ID number
$registration_id = generateUniqueRegistrationNumber($db);

// Set the $qrcode using the pneumonic prefix and the generated registration number
$qrcode = "ICTO-" . $registration_id;

$qrcode_file_path = 'uploads/qrcode/' . $qrcode . '.png';

// Ensure the upload directory exists
if (!file_exists('uploads/qrcode')) {
    mkdir('uploads/qrcode', 0777, true);
}

// Generate the QR code image using online service (temporary fix)
// QRcode::png($qrcode, $qrcode_file_path, QR_ECLEVEL_L, 6, 2);

// Alternative QR code generation using external API
$qr_api_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrcode);
$qr_image_data = file_get_contents($qr_api_url);

if ($qr_image_data !== false) {
    file_put_contents($qrcode_file_path, $qr_image_data);
} else {
    // Fallback: create a simple text file if QR generation fails
    file_put_contents($qrcode_file_path . '.txt', $qrcode);
}

$qrcode_url = "https://icto.psmeinc.org.ph/checkin/" . $qrcode_file_path;

// Initialize variables to hold sanitized input data
$membership_type = isset($_POST['membership_type']) ? intval($_POST['membership_type']) : null;
$pwd = isset($_POST['pwd']) ? intval($_POST['pwd']) : null;
$senior_citizen = isset($_POST['senior_citizen']) ? intval($_POST['senior_citizen']) : null;
$new_board_passer = isset($_POST['new_board_passer']) ? intval($_POST['new_board_passer']) : null;
$free = isset($_POST['free']) ? intval($_POST['free']) : null;
$freeReason = isset($_POST['freeReason']) ? $db->real_escape_string(trim($_POST['freeReason'])) : null;
$first_name = $db->real_escape_string(trim($_POST['first_name']));
$middle_name_na = isset($_POST['middle_name_na']) ? intval($_POST['middle_name_na']) : null;
$middle_name = isset($_POST['middle_name']) ? $db->real_escape_string(trim($_POST['middle_name'])) : null;
$last_name = $db->real_escape_string(trim($_POST['last_name']));
$name_suffix = isset($_POST['name_suffix']) ? $db->real_escape_string(trim($_POST['name_suffix'])) : null;
$personal_email = $db->real_escape_string(trim($_POST['personal_email']));
$mobile_number = isset($_POST['mobile_number']) ? $db->real_escape_string(trim($_POST['mobile_number'])) : null;
if (!empty($_POST['birthday'])) {
    $birthday = DateTime::createFromFormat('F j, Y', $_POST['birthday'])->format('Y-m-d');
} else {
    $birthday = null;
}
$prc_type = $db->real_escape_string(trim($_POST['prc_type']));
$prc_license_number = isset($_POST['prc_license_number']) ? $db->real_escape_string(trim($_POST['prc_license_number'])) : null;
if (!empty($_POST['prc_expiration_date'])) {
    $prc_expiration_date = DateTime::createFromFormat('F j, Y', $_POST['prc_expiration_date'])->format('Y-m-d');
} else {
    $prc_expiration_date = null;
}
$has_prc_license = isset($_POST['has_prc_license']) ? intval($_POST['has_prc_license']) : null;
$sequence_number = isset($_POST['sequence_number']) ? $db->real_escape_string(trim($_POST['sequence_number'])) : null;
$region = isset($_POST['region']) ? $db->real_escape_string(trim($_POST['region'])) : null;
$chapter = isset($_POST['chapter']) ? $db->real_escape_string(trim($_POST['chapter'])) : null;
$other = isset($_POST['other']) ? $db->real_escape_string(trim($_POST['other'])) : null;
$payment_method = $db->real_escape_string(trim($_POST['payment_method']));
$payment_note = isset($_POST['payment_note']) ? $db->real_escape_string(trim($_POST['payment_note'])) : null;
$registration_fee = isset($_POST['registration_fee']) ? $db->real_escape_string(trim($_POST['registration_fee'])) : null;
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null; // Assuming you have user_id in session

// Prepare and bind the SQL statement
$stmt = $db->prepare("INSERT INTO registrations (
    membership_type,
    pwd,
    senior_citizen,
    new_board_passer,
    free,
    freeReason,
    first_name,
    middle_name_na,
    middle_name,
    last_name,
    name_suffix,
    personal_email,
    mobile_number,
    birthday,
    prc_type,
    prc_license_number,
    prc_expiration_date,
    has_prc_license,
    sequence_number,
    region,
    chapter,
    other,
    payment_method,
    payment_note,
    registration_fee,
    user_id, timestamp, qrcode, qrcode_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)");

// Bind parameters
$stmt->bind_param(
    "iiiiisssssssisssssssssssiiss",
    $membership_type,
    $pwd,
    $senior_citizen,
    $new_board_passer,
    $free,
    $freeReason,
    $first_name,
    $middle_name_na,
    $middle_name,
    $last_name,
    $name_suffix,
    $personal_email,
    $mobile_number,
    $birthday,
    $prc_type,
    $prc_license_number,
    $prc_expiration_date,
    $has_prc_license,
    $sequence_number,
    $region,
    $chapter,
    $other,
    $payment_method,
    $payment_note,
    $registration_fee,
    $user_id,
    $qrcode,
    $qrcode_url
);

// Execute the statement
if ($stmt->execute()) {
    // Define placeholders
    $logo_url = 'https://icto.psmeinc.org.ph/checkin/img/73rdNatCon.jpg';

    // Set timezone to Asia/Manila for Philippine time
    date_default_timezone_set('Asia/Manila');
    $timestamp = date('F d, Y H:i:s');

    if ($middle_name == 'N/A' || $middle_name == NULL) {
        $middle_name = '';
    }
    if ($name_suffix == 'N/A' || $name_suffix == NULL) {
        $name_suffix = '';
    }
    $name = trim($first_name . ' ' . $middle_name . ' ' . $last_name . ' ' . $name_suffix);

    $subject = 'Your e-Ticket for the PSME 14TH MINDANAO REGIONAL CONFERENCE';
    $htmlBody = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='text-align: center;'>
            <img src='{$logo_url}' alt='PSME Logo' style='width: 150px;'><br>
            <h2>You're going to the PSME 14TH MINDANAO REGIONAL CONFERENCE!</h2>
            <h4>Reference No. {$qrcode}</h4>
            <p>PSME 14TH MINDANAO REGIONAL CONFERENCE</p>
            <p>23-25 APRIL 2026</p>
            <p>SM CDO DOWNTOWN, 5TH FLOOR EVENT HALL CAGAYAN DE ORO CITY</p>
        </div>
        <hr>
        <div style='text-align: center;'>
            <h4>e-Ticket QR Code</h4>
            <img src='{$qrcode_url}' alt='QR Code' style='width: 150px;'><br>
        </div>
        <hr>
        <div>
            <h4>Ticket Holder:</h4>
            <p>Name: {$name}</p>
            <p>Mobile: {$mobile_number}</p>
            <p>Email: {$personal_email}</p>
            <p>Reference No. {$qrcode}</p>
            <p>Issued On: {$timestamp}</p>
            <hr>
            <h3>e-Ticket</h3>
            <p>(Print this ticket and bring it with you to the event.)</p>
            <p>Reference No. {$qrcode}</p>
        </div>
    </body>
    </html>";

    $textBody = "You're going to the PSME 14TH MINDANAO REGIONAL CONFERENCE!\n"
        . "Reference No. {$qrcode}\n"
        . "PSME 14TH MINDANAO REGIONAL CONFERENCE\n"
        . "23-25 APRIL 2026\n"
        . "SM CDO DOWNTOWN, 5TH FLOOR EVENT HALL CAGAYAN DE ORO CITY\n\n"
        . "e-Ticket QR Code: {$qrcode_url}\n\n"
        . "Ticket Holder:\n"
        . "Name: {$name}\n"
        . "Mobile: {$mobile_number}\n"
        . "Email: {$personal_email}\n"
        . "Reference No. {$qrcode}\n"
        . "Issued On: {$timestamp}\n";

    $mailDefaults = gmail_api_mail_defaults();
    $fromEmail = $mailDefaults['from_address'];
    $fromName = $mailDefaults['from_name'];
    $mailError = '';

    if (!send_via_gmail_api($personal_email, $name, $subject, $htmlBody, $textBody, $fromEmail, $fromName, $mailError)) {
        echo "Message could not be sent. Mailer Error: {$mailError}";
        exit();
    }

    // Set a session variable to indicate successful registration
    $_SESSION['registration_success'] = true;

    // Redirect to registerone.php
    header('Location: registerone.php');
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$db->close();

