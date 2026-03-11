<?php
// send_mail.php - send form submissions using Microsoft 365 SMTP

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
$toAddress = 'vedaads.com@gmail.com';
$subject   = 'New website enquiry: ' . $source;

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

$bodyText = implode("\n", $lines);

// Load Composer's autoloader for PHPMailer
require __DIR__ . '/vendor/autoload.php';

try {
    $mail = new PHPMailer(true);

    // SMTP configuration for Microsoft 365
    $mail->isSMTP();
    $mail->Host       = 'smtp.office365.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'connect@vedaads.com';              // your Microsoft 365 mailbox
    $mail->Password   = getenv('VEDAADS_SMTP_PASSWORD') ?: 'CHANGE_ME_LOCALLY'; // set via env on server
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender & recipients
    $mail->setFrom('connect@vedaads.com', 'Veda Ads Website');
    $mail->addAddress($toAddress);
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(false);
    $mail->Subject = $subject;
    $mail->Body    = $bodyText;

    $mail->send();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to send email via SMTP.']);
}
