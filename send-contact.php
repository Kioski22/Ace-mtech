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
$limitWindowSeconds = max(60, (int)(getenv('CONTACT_RATE_LIMIT_WINDOW') ?: '300'));
$limitMax = max(1, (int)(getenv('CONTACT_RATE_LIMIT_MAX') ?: '10'));
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
	$oldestInWindow = (int)($recent[0] ?? $now);
	$retryAfter = max(1, $limitWindowSeconds - ($now - $oldestInWindow));
	header('Retry-After: ' . (string)$retryAfter);
	http_response_code(429);
	echo json_encode([
		'ok' => false,
		'code' => 'rate_limited',
		'retry_after_seconds' => $retryAfter,
		'message' => 'Too many requests. Please wait before trying again.'
	]);
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

function get_primary_smtp_host(): string
{
	$configuredHost = trim((string)(getenv('APP_SMTP_HOST') ?: getenv('SMTP_HOST') ?: ''));
	if ($configuredHost === '') {
		return 'smtpout.secureserver.net';
	}

	$resolved = @gethostbyname($configuredHost);
	if ($resolved === $configuredHost) {
		error_log('Ace-mtech mail warning: SMTP host may not be resolvable (' . $configuredHost . ').');
	}

	return $configuredHost;
}

function env_bool(string $name, bool $default): bool
{
	$value = getenv($name);
	if ($value === false) {
		return $default;
	}

	$normalized = strtolower(trim((string)$value));
	if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
		return true;
	}
	if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
		return false;
	}

	return $default;
}

function smtp_encryption_mode(int $smtpPort): string
{
	$raw = strtolower(trim((string)(getenv('APP_SMTP_ENCRYPTION') ?: getenv('SMTP_ENCRYPTION') ?: 'ssl')));
	if (in_array($raw, ['tls', 'starttls'], true)) {
		return PHPMailer::ENCRYPTION_STARTTLS;
	}
	if (in_array($raw, ['ssl', 'smtps'], true)) {
		return PHPMailer::ENCRYPTION_SMTPS;
	}
	if (in_array($raw, ['none', 'off', 'plain'], true)) {
		return '';
	}

	return $smtpPort === 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
}

function normalize_error_message(string $rawError): string
{
	$singleLine = preg_replace('/\s+/', ' ', trim($rawError));
	$singleLine = is_string($singleLine) ? $singleLine : 'Unknown SMTP error.';
	if (strlen($singleLine) > 280) {
		return substr($singleLine, 0, 280) . '...';
	}

	return $singleLine;
}

function send_via_fallback(string $fromEmail, string $fromName, string $replyEmail, string $replyName, string $recipientEmail, string $subject, string $body): bool
{
	try {
		$fallbackSmtp = new PHPMailer(true);
		$fallbackSmtp->isSMTP();
		$fallbackSmtp->Host = 'localhost';
		$fallbackSmtp->SMTPAuth = false;
		$fallbackSmtp->Port = 25;
		$fallbackSmtp->SMTPSecure = '';
		$fallbackSmtp->SMTPAutoTLS = false;
		$fallbackSmtp->Timeout = 5;
		$fallbackSmtp->setFrom($fromEmail, $fromName);
		$fallbackSmtp->addReplyTo($replyEmail, $replyName);
		$fallbackSmtp->addAddress($recipientEmail);
		$fallbackSmtp->Subject = $subject;
		$fallbackSmtp->Body = $body;
		$fallbackSmtp->send();
		return true;
	} catch (Exception $localhostException) {
		error_log('Ace-mtech fallback localhost SMTP failed: ' . $localhostException->getMessage());
	}

	try {
		$fallbackMail = new PHPMailer(true);
		$fallbackMail->isMail();
		$fallbackMail->setFrom($fromEmail, $fromName);
		$fallbackMail->addReplyTo($replyEmail, $replyName);
		$fallbackMail->addAddress($recipientEmail);
		$fallbackMail->Subject = $subject;
		$fallbackMail->Body = $body;
		$fallbackMail->send();
		return true;
	} catch (Exception $mailException) {
		error_log('Ace-mtech fallback mail() failed: ' . $mailException->getMessage());
	}

	return false;
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
		'email_sent' => false,
		'saved_local' => true,
		'reason' => 'email_disabled',
		'request_id' => $requestId,
		'message' => 'Request saved, but email was not sent because notifications are disabled.'
	]);
	exit;
}

$smtpUser = trim((string)(getenv('APP_SMTP_FROM') ?: getenv('SMTP_USER') ?: 'your-full-email@yourdomain.com'));
$smtpPass = trim((string)(getenv('APP_SMTP_PASSWORD') ?: getenv('SMTP_PASS') ?: 'your-mailbox-password'));
$smtpHost = get_primary_smtp_host();
$smtpPort = (int)(getenv('APP_SMTP_PORT') ?: getenv('SMTP_PORT') ?: 465);
$smtpTimeout = max(3, (int)(getenv('APP_SMTP_TIMEOUT') ?: '8'));
$smtpAuth = env_bool('APP_SMTP_AUTH', true);
$smtpSecure = smtp_encryption_mode($smtpPort);
$smtpMode = strtolower(trim((string)(getenv('APP_SMTP_MODE') ?: 'local-only')));
$useLocalhostFirst = in_array($smtpMode, ['localhost-first', 'fallback-first', 'local-first', 'local-only', 'localhost-only'], true);
$useLocalhostOnly = in_array($smtpMode, ['local-only', 'localhost-only'], true);

$smtpFrom = trim((string)(getenv('SMTP_FROM') ?: 'your-full-email@yourdomain.com'));
$smtpFromName = trim((string)(getenv('SMTP_FROM_NAME') ?: 'Ace-mtech Website'));
$smtpTo = trim((string)(getenv('SMTP_TO') ?: 'where-you-want-to-receive@yourdomain.com'));

// Gmail app passwords are often shown with spaces in groups of 4.
$smtpPass = str_replace(' ', '', trim($smtpPass));

if ($smtpHost === '' || $smtpFrom === '' || $smtpTo === '') {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'Server email is not configured.']);
	exit;
}

if (in_array($smtpUser, ['your-full-email@yourdomain.com'], true)
	|| in_array($smtpFrom, ['your-full-email@yourdomain.com'], true)
	|| in_array($smtpTo, ['where-you-want-to-receive@yourdomain.com'], true)
	|| $smtpPass === 'your-mailbox-password') {
	http_response_code(500);
	echo json_encode([
		'ok' => false,
		'reason' => 'smtp_placeholder_config',
		'message' => 'SMTP is using placeholder values. Set APP_SMTP_FROM, APP_SMTP_PASSWORD, SMTP_FROM, and SMTP_TO to real GoDaddy mailbox addresses.'
	]);
	exit;
}

if (!$useLocalhostOnly && $smtpAuth && ($smtpUser === '' || $smtpPass === '')) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'message' => 'SMTP authentication is enabled but credentials are missing.']);
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
	$sent = false;

	if ($useLocalhostFirst) {
		$sent = send_via_fallback($fromEmail, $smtpFromName, $email, $name, $smtpTo, $subject, $body);
	}

	if (!$sent && !$useLocalhostOnly) {
		$mail = new PHPMailer(true);
		$mail->isSMTP();
		$mail->Host = $smtpHost;
		$mail->SMTPAuth = $smtpAuth;
		$mail->Username = $smtpUser;
		$mail->Password = $smtpPass;
		$mail->SMTPSecure = $smtpSecure;
		$mail->Port = $smtpPort;
		$mail->Timeout = $smtpTimeout;
		$mail->Timelimit = $smtpTimeout;
		$mail->SMTPAutoTLS = true;
		$mail->SMTPKeepAlive = false;
		$mail->SMTPOptions = [
			'ssl' => [
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true,
			],
		];

		$mail->setFrom($fromEmail, $smtpFromName);
		$mail->addAddress($smtpTo);
		$mail->addReplyTo($email, $name);
		$mail->Subject = $subject;
		$mail->Body = $body;
		$mail->send();
		$sent = true;
	}

	if (!$sent) {
		throw new Exception('Email send did not complete.');
	}

	echo json_encode([
		'ok' => true,
		'email_sent' => true,
		'saved_local' => true,
		'reason' => $useLocalhostOnly ? 'local_relay_sent' : 'smtp_sent',
		'request_id' => $requestId,
		'message' => $useLocalhostOnly ? 'Request saved and email sent via local server relay.' : 'Request saved and email sent successfully.'
	]);
} catch (Exception $e) {
	$rawError = isset($mail) && $mail instanceof PHPMailer && $mail->ErrorInfo !== '' ? $mail->ErrorInfo : $e->getMessage();
	$normalizedError = normalize_error_message($rawError);
	error_log('Ace-mtech mail error: ' . $rawError);

	$fallbackSent = send_via_fallback($fromEmail, $smtpFromName, $email, $name, $smtpTo, $subject, $body);
	if ($fallbackSent) {
		echo json_encode([
			'ok' => true,
			'email_sent' => true,
			'saved_local' => true,
			'reason' => 'smtp_primary_failed_fallback_sent',
			'request_id' => $requestId,
			'message' => 'Primary SMTP failed (' . $normalizedError . '). Request saved and sent via fallback transport.'
		]);
		exit;
	}

	echo json_encode([
		'ok' => false,
		'email_sent' => false,
		'saved_local' => true,
		'reason' => 'smtp_failed',
		'smtp_error' => $normalizedError,
		'request_id' => $requestId,
		'message' => 'Request saved, but email was not sent. SMTP error: ' . $normalizedError
	]);
}
