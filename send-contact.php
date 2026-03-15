<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

// Prevent stray warnings/notices from corrupting JSON output.
ini_set('display_errors', '0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
	exit;
}

session_start();

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server dependency missing. Run composer install.']);
	exit;
}

require $autoloadPath;

$honeypot = trim((string)($_POST['website'] ?? ''));
if ($honeypot !== '') {
	echo json_encode(['ok' => true]);
	exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$limitWindowSeconds = 600;
$limitMax = 3;
$rateFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'acemtech_rate_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $ip) . '.json';

$now = time();
$recent = [];
if (is_file($rateFile)) {
	$raw = file_get_contents($rateFile);
	$decoded = json_decode($raw, true);
	if (is_array($decoded)) {
		foreach ($decoded as $ts) {
			if (is_int($ts) && ($now - $ts) < $limitWindowSeconds) {
				$recent[] = $ts;
			}
		}
	}
}

if (count($recent) >= $limitMax) {
	http_response_code(429);
	echo json_encode(['ok' => false, 'message' => 'Too many requests. Please try again shortly.']);
	exit;
}

$recent[] = $now;
@file_put_contents($rateFile, json_encode($recent), LOCK_EX);

$name = trim((string)($_POST['name'] ?? ''));
$company = trim((string)($_POST['company'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$message = trim((string)($_POST['message'] ?? ''));

if ($name === '' || $email === '' || $message === '') {
	http_response_code(422);
	echo json_encode(['ok' => false, 'message' => 'Please fill out all required fields.']);
	exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'message' => 'Please enter a valid email address.']);
	exit;
}

$smtpHost = trim((string)(getenv('SMTP_HOST') ?: 'localhost'));
$smtpPort = (int)(getenv('SMTP_PORT') ?: '25');
$smtpUser = trim((string)(getenv('SMTP_USER') ?: ''));
$smtpPass = trim((string)(getenv('SMTP_PASS') ?: ''));
$smtpFrom = trim((string)(getenv('SMTP_FROM') ?: ''));
$smtpFromName = getenv('SMTP_FROM_NAME') ?: 'Ace-mtech Website';
$smtpTo = trim((string)(getenv('SMTP_TO') ?: ''));

if ($smtpHost === '' || $smtpFrom === '' || $smtpTo === '') {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server email is not configured.']);
	exit;
}

if (!filter_var($smtpTo, FILTER_VALIDATE_EMAIL)) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server recipient email is invalid.']);
	exit;
}

// Allow SMTP_FROM to be either "email@domain" or "Name <email@domain>".
$fromEmail = $smtpFrom;
if (preg_match('/^\s*([^<]+)\s*<\s*([^>]+)\s*>\s*$/', $smtpFrom, $matches)) {
	$smtpFromName = trim($matches[1]);
	$fromEmail = trim($matches[2]);
}

if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server sender email is invalid.']);
	exit;
}

$thirdPartyMailboxDomains = [
	'gmail.com',
	'yahoo.com',
	'outlook.com',
	'hotmail.com',
	'icloud.com',
	'aol.com',
];

$fromDomain = strtolower((string)substr(strrchr($fromEmail, '@') ?: '', 1));
if (in_array($fromDomain, $thirdPartyMailboxDomains, true)) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server sender email must be a domain mailbox, not a third-party mailbox provider.']);
	exit;
}

$subject = 'Project inquiry' . ($company !== '' ? ' - ' . $company : '');
$body =
	"Name: {$name}\n" .
	"Company: " . ($company !== '' ? $company : '-') . "\n" .
	"Email: {$email}\n\n" .
	$message . "\n";

try {
	$mail = new PHPMailer(true);
	$mail->isSMTP();
	$mail->Host = $smtpHost;
	$mail->SMTPAuth = false;
	$mail->SMTPSecure = '';
	$mail->SMTPAutoTLS = false;
	$mail->Port = $smtpPort;
	$mail->Timeout = 8;
	$mail->Timelimit = 8;

	$mail->setFrom($fromEmail, $smtpFromName);
	$mail->addAddress($smtpTo);
	$mail->addReplyTo($email, $name);

	$mail->Subject = $subject;
	$mail->Body = $body;

	$mail->send();
	echo json_encode([
		'ok' => true,
		'message' => 'Request submitted successfully. Email sent via local mail relay.'
	]);
} catch (Exception $e) {
	$rawError = $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage();
	error_log('Ace-mtech mail error: ' . $rawError);
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => $rawError]);
}
