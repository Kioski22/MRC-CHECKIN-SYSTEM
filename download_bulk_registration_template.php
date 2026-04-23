<?php
session_start();
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="registration_bulk_template.csv"');

$output = fopen('php://output', 'w');

$headers = [
    'membership_type',
    'pwd',
    'senior_citizen',
    'new_board_passer',
    'free',
    'freeReason',
    'first_name',
    'middle_name_na',
    'middle_name',
    'last_name',
    'name_suffix',
    'personal_email',
    'mobile_number',
    'birthday',
    'prc_type',
    'prc_license_number',
    'prc_expiration_date',
    'has_prc_license',
    'sequence_number',
    'region',
    'chapter',
    'other',
    'payment_method',
    'payment_note'
];

fputcsv($output, $headers);

$sampleRow = [
    '3',
    '0',
    '0',
    '0',
    '0',
    '',
    'Juan',
    '0',
    'Dela',
    'Cruz',
    '',
    'juan.delacruz@example.com',
    '+639123456789',
    '1990-01-15',
    'registered_mechanical_engineer',
    '1234567',
    '2027-12-31',
    '1',
    '',
    'Mindanao',
    'Cagayan de Oro',
    '',
    'cash',
    ''
];

fputcsv($output, $sampleRow);

fclose($output);
