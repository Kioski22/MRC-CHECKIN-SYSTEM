<?php

require_once __DIR__ . '/gmail_api_mailer.php';

function build_registration_full_name(array $registration)
{
    $first = trim((string) ($registration['first_name'] ?? ''));
    $middle = trim((string) ($registration['middle_name'] ?? ''));
    $last = trim((string) ($registration['last_name'] ?? ''));
    $suffix = trim((string) ($registration['name_suffix'] ?? ''));

    if (strcasecmp($middle, 'N/A') === 0) {
        $middle = '';
    }
    if (strcasecmp($suffix, 'N/A') === 0) {
        $suffix = '';
    }

    return trim($first . ' ' . $middle . ' ' . $last . ' ' . $suffix);
}

function send_registration_confirmation_email(array $registration, &$error)
{
    $toEmail = trim((string) ($registration['personal_email'] ?? ''));
    if ($toEmail === '') {
        $error = 'Missing recipient email address.';
        return false;
    }

    $name = build_registration_full_name($registration);
    $mobileNumber = trim((string) ($registration['mobile_number'] ?? ''));
    $qrcode = trim((string) ($registration['qrcode'] ?? ''));
    $qrcodeUrl = trim((string) ($registration['qrcode_url'] ?? ''));
    $issuedOnRaw = trim((string) ($registration['timestamp'] ?? ''));

    if ($qrcodeUrl === '' && $qrcode !== '') {
        $qrcodeUrl = 'https://icto.psmeinc.org.ph/checkin/uploads/qrcode/' . rawurlencode($qrcode) . '.png';
    }

    $issuedOn = $issuedOnRaw;
    if ($issuedOnRaw !== '') {
        $time = strtotime($issuedOnRaw);
        if ($time !== false) {
            $issuedOn = date('F d, Y H:i:s', $time);
        }
    }

    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $safeMobile = htmlspecialchars($mobileNumber, ENT_QUOTES, 'UTF-8');
    $safeEmail = htmlspecialchars($toEmail, ENT_QUOTES, 'UTF-8');
    $safeQrcode = htmlspecialchars($qrcode, ENT_QUOTES, 'UTF-8');
    $safeIssuedOn = htmlspecialchars($issuedOn, ENT_QUOTES, 'UTF-8');

    $logoUrl = 'https://icto.psmeinc.org.ph/checkin/img/73rdNatCon.jpg';
    $subject = 'Your e-Ticket for the PSME 14TH MINDANAO REGIONAL CONFERENCE';

    $htmlBody = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='text-align: center;'>
            <img src='{$logoUrl}' alt='PSME Logo' style='width: 150px;'><br>
            <h2>You're going to the PSME 14TH MINDANAO REGIONAL CONFERENCE!</h2>
            <h4>Reference No. {$safeQrcode}</h4>
            <p>PSME 14TH MINDANAO REGIONAL CONFERENCE</p>
            <p>23-25 APRIL 2026</p>
            <p>SM CDO DOWNTOWN, 5TH FLOOR EVENT HALL CAGAYAN DE ORO CITY</p>
        </div>
        <hr>
        <div style='text-align: center;'>
            <h4>e-Ticket QR Code</h4>
            <img src='{$qrcodeUrl}' alt='QR Code' style='width: 150px;'><br>
        </div>
        <hr>
        <div>
            <h4>Ticket Holder:</h4>
            <p>Name: {$safeName}</p>
            <p>Mobile: {$safeMobile}</p>
            <p>Email: {$safeEmail}</p>
            <p>Reference No. {$safeQrcode}</p>
            <p>Issued On: {$safeIssuedOn}</p>
            <hr>
            <h3>e-Ticket</h3>
            <p>(Print this ticket and bring it with you to the event.)</p>
            <p>Reference No. {$safeQrcode}</p>
        </div>
    </body>
    </html>";

    $textBody = "You're going to the PSME 14TH MINDANAO REGIONAL CONFERENCE!\n"
        . "Reference No. {$qrcode}\n"
        . "PSME 14TH MINDANAO REGIONAL CONFERENCE\n"
        . "23-25 APRIL 2026\n"
        . "SM CDO DOWNTOWN, 5TH FLOOR EVENT HALL CAGAYAN DE ORO CITY\n\n"
        . "e-Ticket QR Code: {$qrcodeUrl}\n\n"
        . "Ticket Holder:\n"
        . "Name: {$name}\n"
        . "Mobile: {$mobileNumber}\n"
        . "Email: {$toEmail}\n"
        . "Reference No. {$qrcode}\n"
        . "Issued On: {$issuedOn}\n";

    $mailDefaults = gmail_api_mail_defaults();
    $fromEmail = (string) ($mailDefaults['from_address'] ?? '');
    $fromName = (string) ($mailDefaults['from_name'] ?? 'PSME ICTO');

    return send_via_gmail_api($toEmail, $name, $subject, $htmlBody, $textBody, $fromEmail, $fromName, $error);
}
