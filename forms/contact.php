<?php
/**
 * Contact form handler — sends submissions straight to your inbox.
 * No external library needed; uses PHP's built-in mail().
 */

header('Content-Type: text/plain');

$receiving_email_address = 'tanyasusan92@gmail.com'; // <-- your inbox

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Invalid request method.';
    exit;
}

// Honeypot spam trap — real users never fill this hidden field
if (!empty($_POST['website'])) {
    echo 'OK';
    exit;
}

// Collect + sanitize fields
$name    = isset($_POST['name'])    ? trim(strip_tags($_POST['name']))    : '';
$email   = isset($_POST['email'])   ? trim($_POST['email'])               : '';
$subject = isset($_POST['subject']) ? trim(strip_tags($_POST['subject'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Basic validation
$errors = [];
if ($name === '')    $errors[] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if ($subject === '') $errors[] = 'Subject is required.';
if ($message === '') $errors[] = 'Message is required.';

if (!empty($errors)) {
    http_response_code(400);
    echo implode(' ', $errors);
    exit;
}

// Build the email
$to           = $receiving_email_address;
$mail_subject = 'Portfolio contact: ' . $subject;

$body  = "You have a new message from your portfolio site.\n\n";
$body .= "Name: {$name}\n";
$body .= "Email: {$email}\n";
$body .= "Subject: {$subject}\n\n";
$body .= "Message:\n{$message}\n";

// From uses a no-reply address on your own domain (avoids spam filters
// rejecting spoofed senders); Reply-To is the visitor's real email so
// you can just hit "Reply" in your inbox.
$domain       = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
$from_address = 'no-reply@' . preg_replace('/^www\./', '', $domain);

$headers   = [];
$headers[] = 'From: Portfolio Contact Form <' . $from_address . '>';
$headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';
$headers[] = 'MIME-Version: 1.0';
$headers[] = 'Content-Type: text/plain; charset=UTF-8';

$sent = mail($to, $mail_subject, $body, implode("\r\n", $headers));

if ($sent) {
    echo 'OK';
} else {
    http_response_code(500);
    echo 'Sorry, something went wrong on the server. Please email me directly instead.';
}