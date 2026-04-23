<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/dbConnection.php';

requireAuthenticatedUser(true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed.']);
    exit();
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

if (!is_array($input) || !isset($input['rows']) || !is_array($input['rows'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid payload. Expected rows array.']);
    exit();
}

$rows = $input['rows'];
if (count($rows) === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No rows to upload.']);
    exit();
}

if (count($rows) > 1000) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Maximum of 1000 rows per upload.']);
    exit();
}

function normalizeBool($value)
{
    if ($value === null) {
        return 0;
    }

    $normalized = strtolower(trim((string)$value));
    if ($normalized === '') {
        return 0;
    }

    return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true) ? 1 : 0;
}

function normalizeMembershipType($value)
{
    $allowed = ['1', '2', '3'];
    $normalized = trim((string)$value);

    if (!in_array($normalized, $allowed, true)) {
        return null;
    }

    return (int)$normalized;
}

function normalizePaymentMethod($value)
{
    $allowed = ['cash', 'gcash', 'bank_transfer', 'check'];
    $normalized = strtolower(trim((string)$value));

    if (!in_array($normalized, $allowed, true)) {
        return null;
    }

    return $normalized;
}

function normalizeNullableString($value)
{
    if ($value === null) {
        return null;
    }

    $normalized = trim((string)$value);
    return $normalized === '' ? null : $normalized;
}

function parseFlexibleDate($value)
{
    if ($value === null || trim((string)$value) === '') {
        return null;
    }

    $raw = trim((string)$value);
    $formats = ['Y-m-d', 'm/d/Y', 'n/j/Y', 'm-d-Y', 'n-j-Y', 'F j, Y', 'F d, Y'];

    foreach ($formats as $format) {
        $date = DateTime::createFromFormat($format, $raw);
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d');
        }
    }

    $timestamp = strtotime($raw);
    if ($timestamp !== false) {
        return date('Y-m-d', $timestamp);
    }

    return false;
}

function calculateRegistrationFee($membershipType, $isSenior, $isPwd, $isFree)
{
    if ($isFree) {
        return 0.00;
    }

    if ($membershipType === 1) {
        return ($isSenior || $isPwd) ? 3200.00 : 3500.00;
    }

    if ($membershipType === 2) {
        return 4500.00;
    }

    if ($membershipType === 3) {
        return ($isSenior || $isPwd) ? 3200.00 : 4000.00;
    }

    return 0.00;
}

function generateUniqueRegistrationNumber($db)
{
    do {
        $randomNumber = random_int(100000, 999999);
        $count = 0;

        $checkStmt = $db->prepare('SELECT COUNT(*) FROM registrations WHERE registration_id = ?');
        $checkStmt->bind_param('i', $randomNumber);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();
    } while ($count > 0);

    return $randomNumber;
}

function ensureQrDirectory()
{
    $directory = __DIR__ . '/uploads/qrcode';
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }
}

function generateQrCodeAssets($qrcode)
{
    ensureQrDirectory();

    $relativePath = 'uploads/qrcode/' . $qrcode . '.png';
    $absolutePath = __DIR__ . '/' . $relativePath;

    $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrcode);
    $qrImageData = @file_get_contents($qrApiUrl);

    if ($qrImageData !== false) {
        file_put_contents($absolutePath, $qrImageData);
    } else {
        file_put_contents($absolutePath . '.txt', $qrcode);
    }

    return [
        'qrcode' => $qrcode,
        'qrcode_url' => 'https://icto.psmeinc.org.ph/checkin/' . $relativePath,
    ];
}

$requiredHeaders = ['membership_type', 'first_name', 'last_name', 'personal_email', 'birthday', 'prc_type', 'payment_method'];
$errors = [];
$preparedRows = [];
$seenEmails = [];

$duplicateCheckStmt = $db->prepare('SELECT id FROM registrations WHERE personal_email = ? LIMIT 1');

foreach ($rows as $index => $row) {
    if (!is_array($row)) {
        $errors[] = 'Row ' . ($index + 2) . ': Invalid row structure.';
        continue;
    }

    $csvRowNumber = isset($row['__csv_row_number']) ? (int)$row['__csv_row_number'] : ($index + 2);

    foreach ($requiredHeaders as $requiredHeader) {
        if (!isset($row[$requiredHeader]) || trim((string)$row[$requiredHeader]) === '') {
            $errors[] = 'Row ' . $csvRowNumber . ': ' . $requiredHeader . ' is required.';
        }
    }

    $membershipType = normalizeMembershipType($row['membership_type'] ?? null);
    if ($membershipType === null) {
        $errors[] = 'Row ' . $csvRowNumber . ': membership_type must be 1, 2, or 3.';
    }

    $personalEmail = strtolower(trim((string)($row['personal_email'] ?? '')));
    if (!filter_var($personalEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Row ' . $csvRowNumber . ': personal_email is invalid.';
    }

    if ($personalEmail !== '') {
        if (isset($seenEmails[$personalEmail])) {
            $errors[] = 'Row ' . $csvRowNumber . ': duplicate personal_email found in CSV.';
        }
        $seenEmails[$personalEmail] = true;

        $duplicateCheckStmt->bind_param('s', $personalEmail);
        $duplicateCheckStmt->execute();
        $existingResult = $duplicateCheckStmt->get_result();
        if ($existingResult && $existingResult->num_rows > 0) {
            $errors[] = 'Row ' . $csvRowNumber . ': personal_email already exists in registrations.';
        }
    }

    $birthday = parseFlexibleDate($row['birthday'] ?? null);
    if ($birthday === false) {
        $errors[] = 'Row ' . $csvRowNumber . ': birthday has invalid date format.';
    }

    $prcExpirationDate = parseFlexibleDate($row['prc_expiration_date'] ?? null);
    if ($prcExpirationDate === false) {
        $errors[] = 'Row ' . $csvRowNumber . ': prc_expiration_date has invalid date format.';
    }

    $paymentMethod = normalizePaymentMethod($row['payment_method'] ?? null);
    if ($paymentMethod === null) {
        $errors[] = 'Row ' . $csvRowNumber . ': payment_method must be cash, gcash, bank_transfer, or check.';
    }

    $isPwd = normalizeBool($row['pwd'] ?? 0);
    $isSenior = normalizeBool($row['senior_citizen'] ?? 0);
    $isNewBoardPasser = normalizeBool($row['new_board_passer'] ?? 0);
    $isFree = normalizeBool($row['free'] ?? 0);
    $isMiddleNameNA = normalizeBool($row['middle_name_na'] ?? 0);
    $hasPrcLicense = normalizeBool($row['has_prc_license'] ?? 0);

    $freeReason = normalizeNullableString($row['freeReason'] ?? null);
    if ($isFree === 1 && $freeReason === null) {
        $errors[] = 'Row ' . $csvRowNumber . ': freeReason is required when free is 1.';
    }

    if (!empty($errors)) {
        continue;
    }

    $preparedRows[] = [
        'membership_type' => (string)$membershipType,
        'pwd' => (string)$isPwd,
        'senior_citizen' => (string)$isSenior,
        'new_board_passer' => (string)$isNewBoardPasser,
        'free' => (string)$isFree,
        'freeReason' => $freeReason,
        'first_name' => trim((string)$row['first_name']),
        'middle_name_na' => (string)$isMiddleNameNA,
        'middle_name' => normalizeNullableString($row['middle_name'] ?? null),
        'last_name' => trim((string)$row['last_name']),
        'name_suffix' => normalizeNullableString($row['name_suffix'] ?? null),
        'personal_email' => $personalEmail,
        'mobile_number' => normalizeNullableString($row['mobile_number'] ?? null),
        'birthday' => $birthday,
        'prc_type' => trim((string)$row['prc_type']),
        'prc_license_number' => normalizeNullableString($row['prc_license_number'] ?? null),
        'prc_expiration_date' => $prcExpirationDate,
        'has_prc_license' => (string)$hasPrcLicense,
        'sequence_number' => normalizeNullableString($row['sequence_number'] ?? null),
        'region' => normalizeNullableString($row['region'] ?? null),
        'chapter' => normalizeNullableString($row['chapter'] ?? null),
        'other' => normalizeNullableString($row['other'] ?? null),
        'payment_method' => $paymentMethod,
        'payment_note' => normalizeNullableString($row['payment_note'] ?? null),
        'registration_fee' => number_format(calculateRegistrationFee($membershipType, $isSenior, $isPwd, $isFree), 2, '.', ''),
    ];
}

$duplicateCheckStmt->close();

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validation failed. Fix CSV rows and try again.',
        'errors' => array_values(array_unique($errors)),
    ]);
    exit();
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid session user.']);
    exit();
}

$insertSql = 'INSERT INTO registrations (
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
    user_id,
    timestamp,
    qrcode,
    qrcode_url
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)';

$insertStmt = $db->prepare($insertSql);

if (!$insertStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to prepare insert statement.']);
    exit();
}

$insertedCount = 0;

try {
    $db->begin_transaction();

    foreach ($preparedRows as $preparedRow) {
        $registrationId = generateUniqueRegistrationNumber($db);
        $qrcode = 'ICTO-' . $registrationId;
        $qrAssets = generateQrCodeAssets($qrcode);

        $membership_type = $preparedRow['membership_type'];
        $pwd = $preparedRow['pwd'];
        $senior_citizen = $preparedRow['senior_citizen'];
        $new_board_passer = $preparedRow['new_board_passer'];
        $free = $preparedRow['free'];
        $freeReason = $preparedRow['freeReason'];
        $first_name = $preparedRow['first_name'];
        $middle_name_na = $preparedRow['middle_name_na'];
        $middle_name = $preparedRow['middle_name'];
        $last_name = $preparedRow['last_name'];
        $name_suffix = $preparedRow['name_suffix'];
        $personal_email = $preparedRow['personal_email'];
        $mobile_number = $preparedRow['mobile_number'];
        $birthday = $preparedRow['birthday'];
        $prc_type = $preparedRow['prc_type'];
        $prc_license_number = $preparedRow['prc_license_number'];
        $prc_expiration_date = $preparedRow['prc_expiration_date'];
        $has_prc_license = $preparedRow['has_prc_license'];
        $sequence_number = $preparedRow['sequence_number'];
        $region = $preparedRow['region'];
        $chapter = $preparedRow['chapter'];
        $other = $preparedRow['other'];
        $payment_method = $preparedRow['payment_method'];
        $payment_note = $preparedRow['payment_note'];
        $registration_fee = $preparedRow['registration_fee'];
        $session_user_id = (string)$userId;
        $qrcode_value = $qrAssets['qrcode'];
        $qrcode_url = $qrAssets['qrcode_url'];

        $insertStmt->bind_param(
            str_repeat('s', 28),
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
            $session_user_id,
            $qrcode_value,
            $qrcode_url
        );

        if (!$insertStmt->execute()) {
            throw new Exception('Insert failed for email ' . $personal_email . ': ' . $insertStmt->error);
        }

        $insertedCount++;
    }

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Bulk registration completed. ' . $insertedCount . ' row(s) inserted.',
        'inserted_count' => $insertedCount,
    ]);
} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Bulk upload failed and was rolled back.',
        'errors' => [$e->getMessage()],
    ]);
}

$insertStmt->close();
$db->close();
