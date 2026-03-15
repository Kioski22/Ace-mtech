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

function persist_contact_request(array $requestPayload): bool
{
	$storageDir = __DIR__ . '/data';
	if (!is_dir($storageDir) && !@mkdir($storageDir, 0775, true) && !is_dir($storageDir)) {
		return false;
	}

	$storagePath = $storageDir . '/contact-requests.jsonl';
	$line = json_encode($requestPayload, JSON_UNESCAPED_UNICODE);
	if ($line === false) {
		return false;
	}

	return @file_put_contents($storagePath, $line . PHP_EOL, FILE_APPEND | LOCK_EX) !== false;
}

$requestId = 'RQ-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(4)), 0, 8);
$savedLocally = persist_contact_request([
	'request_id' => $requestId,
	'created_at' => date('c'),
	'name' => $name,
	'company' => $company,
	'email' => $email,
	'message' => $message,
	'ip' => (string)$ip,
	'user_agent' => (string)($_SERVER['HTTP_USER_AGENT'] ?? ''),
]);

if (!$savedLocally) {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'message' => 'Unable to record your request right now. Please try again shortly.'
	]);
	exit;
}

$contactEmailMode = strtolower(trim((string)(getenv('CONTACT_EMAIL_MODE') ?: 'sync')));
if ($contactEmailMode !== 'sync') {
	echo json_encode([
		'ok' => true,
		'saved_local' => true,
		'request_id' => $requestId,
		'message' => 'Request recorded locally. Email notification is currently disabled, so no email was sent.'
	]);
	exit;
}

$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = getenv('SMTP_PORT') ?: '587';
$smtpUser = getenv('SMTP_USER') ?: 'acemtech781@gmail.com';
$smtpPass = getenv('SMTP_PASS') ?: 'itrd piwc dvwb tiwc';
$smtpFrom = getenv('SMTP_FROM') ?: 'acemtech781@gmail.com';
$smtpFromName = getenv('SMTP_FROM_NAME') ?: 'Ace-mtech Website';
$smtpTo = getenv('SMTP_TO') ?: $smtpUser;

// Gmail app passwords are often shown with spaces in groups of 4.
$smtpPass = str_replace(' ', '', trim($smtpPass));

if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $smtpFrom === '' || $smtpTo === '') {
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
	$mail->SMTPAuth = true;
	$mail->Username = $smtpUser;
	$mail->Password = $smtpPass;
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = (int)$smtpPort;
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
		'request_id' => $requestId,
		'message' => 'Request submitted successfully. We will contact you shortly.'
	]);
} catch (Exception $e) {
	$rawError = $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage();
	error_log('Ace-mtech mail error: ' . $rawError);

	echo json_encode([
		'ok' => true,
		'saved_local' => true,
		'request_id' => $requestId,
		'message' => 'Request received. Email notification is temporarily delayed, but your request has been recorded.'
	]);
}
