<?php
session_start();
require_once __DIR__ . '/auth.php';

requireAuthenticatedUser();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="registration_bulk_template.csv"');

$output = fopen('php://output', 'w');

$headers = [
    'Membership Type (1=Regular, 2=Affiliate, 3=Student)',
    'PWD (0=No, 1=Yes)',
    'Senior Citizen (0=No, 1=Yes)',
    'New Board Passer (0=No, 1=Yes)',
    'Free Registration (0=No, 1=Yes)',
    'Reason if Free',
    'First Name',
    'No Middle Name (0=No, 1=Yes)',
    'Middle Name',
    'Last Name',
    'Suffix (Optional)',
    'Email Address',
    'Mobile Number',
    'Birth Date (YYYY-MM-DD)',
    'PRC Type',
    'PRC License Number',
    'PRC Expiration Date (YYYY-MM-DD)',
    'Has PRC License (0=No, 1=Yes)',
    'Sequence Number (Optional)',
    'Region',
    'Chapter',
    'Other (Optional)',
    'Payment Method (cash/gcash/bank_transfer/check)',
    'Payment Note (Optional)'
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
