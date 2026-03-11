<?php
// send_mail.php
// Simple backend endpoint to send form submissions as email.
// NOTE: This uses PHP's mail() function. Your hosting must be configured
// to send mail; otherwise you'll need an SMTP-based solution.

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Helper to safely get POST fields
function post_field(string $key): string {
    return isset($_POST[$key]) ? trim((string)$_POST[$key]) : '';
}

$name    = post_field('name');
$email   = post_field('email');
$phone   = post_field('phone');
$website = post_field('website');
$source  = post_field('source');
if ($source === '') {
    $source = 'main-form';
}

if ($name === '' || $email === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Name and email are required.']);
    exit;
}

// Where to send
$to      = 'vedaads.com@gmail.com';
$subject = 'New website enquiry: ' . $source;

$lines = [];
$lines[] = 'Name:    ' . $name;
$lines[] = 'Email:   ' . $email;
if ($phone !== '') {
    $lines[] = 'Mobile:  ' . $phone;
}
if ($website !== '') {
    $lines[] = 'Website: ' . $website;
}
$lines[] = '';
$lines[] = '[Submitted from ' . $source . ']';

$body = implode("\n", $lines);

// Basic headers. You can change the From address to a domain mailbox you own.
$fromAddress = 'no-reply@vedaads.com';
$fromName    = 'Veda Ads Website';

$headers  = 'From: ' . sprintf('"%s" <%s>', $fromName, $fromAddress) . "\r\n";
$headers .= 'Reply-To: ' . $email . "\r\n";
$headers .= 'X-Mailer: PHP/' . phpversion();

$sent = @mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to send email.']);
}

