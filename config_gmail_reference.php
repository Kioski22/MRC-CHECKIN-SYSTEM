<?php

/**
 * Read a config value from environment first, then optional local secrets file.
 */
if (!function_exists('ticket_config_value')) {
    function ticket_config_value(string $key, string $default = ''): string
    {
        $value = getenv($key);
        if ($value === false || $value === null || trim((string) $value) === '') {
            $value = $_ENV[$key] ?? ($_SERVER[$key] ?? null);
        }
        if ((($value === null || trim((string) $value) === '') && function_exists('apache_getenv'))) {
            $apacheValue = apache_getenv($key, true);
            if ($apacheValue !== false && trim((string) $apacheValue) !== '') {
                $value = $apacheValue;
            }
        }

        static $localSecrets = null;
        if ($localSecrets === null) {
            $localSecretsFile = __DIR__ . '/ticket_secrets.php';
            $localSecrets = is_file($localSecretsFile)
                ? (require $localSecretsFile)
                : [];
            if (!is_array($localSecrets)) {
                $localSecrets = [];
            }
        }

        if (($value === null || trim((string) $value) === '') && array_key_exists($key, $localSecrets)) {
            $value = $localSecrets[$key];
        }

        $resolved = trim((string) ($value ?? ''));
        if ($resolved === '') {
            $resolved = $default;
        }

        return $resolved;
    }
}

date_default_timezone_set(ticket_config_value('TICKET_APP_TIMEZONE', 'Asia/Manila'));

return [
    'db' => [
        'host' => ticket_config_value('TICKET_DB_HOST', 'localhost'),
        'username' => ticket_config_value('TICKET_DB_USERNAME', 'root'),
        'password' => ticket_config_value('TICKET_DB_PASSWORD', ''),
        'database' => ticket_config_value('TICKET_DB_NAME', 'rafflev2'),
    ],
    'app' => [
        'base_url' => ticket_config_value('TICKET_APP_BASE_URL', 'http://localhost/ticketV2'),
        'activation_cutoff' => ticket_config_value('TICKET_ACTIVATION_CUTOFF', '2025-10-31 17:00:00'),
    ],
    'activation_portal' => [
        'base_url' => ticket_config_value('TICKET_ACTIVATION_PORTAL_URL', ticket_config_value('TICKET_APP_BASE_URL', 'http://localhost/ticketV2') . '/verification.php'),
        'token_ttl_seconds' => (int) ticket_config_value('TICKET_ACTIVATION_TOKEN_TTL', '604800'),
    ],
    'gmail_api' => [
        'credentials_path' => ticket_config_value('TICKET_GMAIL_CREDENTIALS_PATH', __DIR__ . '/jisonbago.json'),
        'refresh_token' => ticket_config_value('TICKET_GMAIL_REFRESH_TOKEN', ''),
        'user_id' => ticket_config_value('TICKET_GMAIL_USER_ID', 'me'),
    ],
    'mail' => [
        'host' => ticket_config_value('TICKET_SMTP_HOST', 'smtp.gmail.com'),
        'port' => (int) ticket_config_value('TICKET_SMTP_PORT', '587'),
        'username' => ticket_config_value('TICKET_SMTP_USERNAME', 'noreplyraffle@psmeinc.org.ph'),
        'password' => ticket_config_value('TICKET_SMTP_PASSWORD', 'dpmo fvjo ezfp uweo'),
        'encryption' => ticket_config_value('TICKET_SMTP_ENCRYPTION', 'tls'),
        'from_address' => ticket_config_value('TICKET_MAIL_FROM', 'noreplyraffle@psmeinc.org.ph'),
        'from_name' => ticket_config_value('TICKET_MAIL_FROM_NAME', 'No-Reply Raffle'),
    ],
    'email_template' => [
        'assignment_format' => ticket_config_value('TICKET_ASSIGNMENT_EMAIL_FORMAT', 'html'),
        'assignment_subject' => ticket_config_value('TICKET_ASSIGNMENT_EMAIL_SUBJECT', 'Your Ticket Assignment - {{ticket_count}} Ticket(s)'),
        'assignment_body_html' => ticket_config_value(
            'TICKET_ASSIGNMENT_EMAIL_HTML',
            '<p>Hello {{full_name}},</p><p>Your ticket assignment is ready.</p><p><strong>Tickets:</strong> {{ticket_list}}</p><p><strong>Ranges:</strong> {{ticket_ranges}}</p><p><strong>Count:</strong> {{ticket_count}}</p><p><strong>Activation Link:</strong> <a href="{{activation_link}}">Activate your tickets</a></p><p><strong>Portal Expires:</strong> {{portal_expires_at}}</p><p><strong>Chapter:</strong> {{chapter}}</p><p><strong>Date:</strong> {{current_date}}</p><p>Please keep this email for your records.</p>'
        ),
        'assignment_body_text' => ticket_config_value(
            'TICKET_ASSIGNMENT_EMAIL_TEXT',
            "Hello {{full_name}},\n\nYour ticket assignment is ready.\n\nTickets: {{ticket_list}}\nRanges: {{ticket_ranges}}\nCount: {{ticket_count}}\nActivation Link: {{activation_link}}\nPortal Expires: {{portal_expires_at}}\nChapter: {{chapter}}\nDate: {{current_date}}\n\nPlease keep this email for your records."
        ),
        'activation_thankyou_format' => ticket_config_value('TICKET_ACTIVATION_THANKYOU_FORMAT', 'html'),
        'activation_thankyou_subject' => ticket_config_value('TICKET_ACTIVATION_THANKYOU_SUBJECT', 'Thank You for Activating {{activated_ticket_count}} Ticket(s)'),
        'activation_thankyou_body_html' => ticket_config_value(
            'TICKET_ACTIVATION_THANKYOU_HTML',
            '<p>Hello {{full_name}},</p><p>Thank you for activating your raffle ticket(s).</p><p><strong>Activated Ticket(s):</strong> {{activated_ticket_list}}</p><p><strong>Total Activated This Submission:</strong> {{activated_ticket_count}}</p><p><strong>Activation Date:</strong> {{activation_date}}</p><p>We appreciate your participation.</p>'
        ),
        'activation_thankyou_body_text' => ticket_config_value(
            'TICKET_ACTIVATION_THANKYOU_TEXT',
            "Hello {{full_name}},\n\nThank you for activating your raffle ticket(s).\nActivated Ticket(s): {{activated_ticket_list}}\nTotal Activated This Submission: {{activated_ticket_count}}\nActivation Date: {{activation_date}}\n\nWe appreciate your participation."
        ),
        'winner_contact_format' => ticket_config_value('TICKET_WINNER_CONTACT_FORMAT', 'html'),
        'winner_contact_subject' => ticket_config_value('TICKET_WINNER_CONTACT_SUBJECT', 'Winner Notification - {{prize_name}}'),
        'winner_contact_body_html' => ticket_config_value(
            'TICKET_WINNER_CONTACT_HTML',
            '<p>Hello {{full_name}},</p><p>Your ticket has been selected for <strong>{{prize_name}}</strong>.</p><p><strong>Claim Deadline:</strong> {{claim_deadline}}</p><p><strong>Claim Contact:</strong> {{claim_contact}}</p><p>Please keep your activated ticket details ready.</p>'
        ),
        'winner_contact_body_text' => ticket_config_value(
            'TICKET_WINNER_CONTACT_TEXT',
            "Hello {{full_name}},\n\nYour ticket has been selected for {{prize_name}}.\nClaim Deadline: {{claim_deadline}}\nClaim Contact: {{claim_contact}}\n\nPlease keep your activated ticket details ready."
        ),
    ],
];
