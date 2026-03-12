<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'message' => 'Method not allowed.']);
	exit;
}

session_start();

require __DIR__ . '/vendor/autoload.php';

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

$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = getenv('SMTP_PORT') ?: '587';
$smtpUser = getenv('SMTP_USER') ?: 'acemtech781@gmail.com';
$smtpPass = getenv('SMTP_PASS') ?: 'tftt lpmq pzzk mobh';
$smtpFrom = getenv('SMTP_FROM') ?: 'ACE-mtech Website <acemtech781@gmail.com>';
$smtpTo = getenv('SMTP_TO') ?: $smtpUser;

if ($smtpHost === '' || $smtpUser === '' || $smtpPass === '' || $smtpFrom === '' || $smtpTo === '') {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server email is not configured.']);
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

	$mail->setFrom($smtpFrom, 'Ace-mtech Website');
	$mail->addAddress($smtpTo);
	$mail->addReplyTo($email, $name);

	$mail->Subject = $subject;
	$mail->Body = $body;

	$mail->send();
	echo json_encode(['ok' => true]);
} catch (Exception $e) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Failed to send. Please try again later.']);
}
