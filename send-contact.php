<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', '0');

function respond_json(int $statusCode, array $payload): void
{
	http_response_code($statusCode);
	echo json_encode($payload);
	exit;
}

function post_string(string $key): string
{
	return trim((string) ($_POST[$key] ?? ''));
}

function env_value(string $key, string $default = ''): string
{
	$value = getenv($key);
	if ($value === false) {
		return $default;
	}

	return trim((string) $value);
}

function load_env_file(string $filePath): void
{
	if (!is_file($filePath) || !is_readable($filePath)) {
		return;
	}

	$lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	if (!is_array($lines)) {
		return;
	}

	foreach ($lines as $line) {
		$trimmed = trim($line);
		if ($trimmed === '' || str_starts_with($trimmed, '#')) {
			continue;
		}

		$separatorPos = strpos($trimmed, '=');
		if ($separatorPos === false) {
			continue;
		}

		$key = trim(substr($trimmed, 0, $separatorPos));
		if ($key === '' || getenv($key) !== false) {
			continue;
		}

		$value = trim(substr($trimmed, $separatorPos + 1));
		if (
			strlen($value) >= 2 &&
			(($value[0] === '"' && $value[strlen($value) - 1] === '"') || ($value[0] === '\'' && $value[strlen($value) - 1] === '\''))
		) {
			$value = substr($value, 1, -1);
		}

		putenv($key . '=' . $value);
		$_ENV[$key] = $value;
		$_SERVER[$key] = $value;
	}
}

function env_flag(string $key, bool $default): bool
{
	$value = strtolower(env_value($key, $default ? 'true' : 'false'));
	return !in_array($value, ['0', 'false', 'no', 'off'], true);
}

function resolve_sender(string $rawFrom, string $defaultName): array
{
	$fromEmail = $rawFrom;
	$fromName = $defaultName;

	if (preg_match('/^\s*([^<]+)\s*<\s*([^>]+)\s*>\s*$/', $rawFrom, $matches)) {
		$fromName = trim($matches[1]);
		$fromEmail = trim($matches[2]);
	}

	return [$fromEmail, $fromName];
}

function load_rate_window(string $rateFile, int $windowSeconds, int $now): array
{
	$recent = [];
	if (!is_file($rateFile)) {
		return $recent;
	}

	$raw = file_get_contents($rateFile);
	$decoded = json_decode((string) $raw, true);
	if (!is_array($decoded)) {
		return $recent;
	}

	foreach ($decoded as $timestamp) {
		if (is_int($timestamp) && ($now - $timestamp) < $windowSeconds) {
			$recent[] = $timestamp;
		}
	}

	return $recent;
}

function apply_smtp_transport(PHPMailer $mail, array $config): void
{
	$mail->isSMTP();
	$mail->Host = $config['host'];
	$mail->Port = $config['port'];
	$mail->Timeout = $config['timeout'];
	$mail->CharSet = 'UTF-8';
	$mail->SMTPAuth = $config['auth'];

	if ($config['auth']) {
		$mail->Username = $config['user'];
		$mail->Password = $config['pass'];
	}

	if ($config['secure'] === 'ssl' || $config['secure'] === 'smtps') {
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->SMTPAutoTLS = false;
		return;
	}

	if ($config['secure'] === 'tls' || $config['secure'] === 'starttls') {
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->SMTPAutoTLS = true;
		return;
	}

	$mail->SMTPSecure = false;
	$mail->SMTPAutoTLS = false;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
	respond_json(405, ['ok' => false, 'message' => 'Method not allowed.']);
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (!is_file($autoloadPath)) {
	respond_json(500, ['ok' => false, 'message' => 'Server dependency missing. Run composer install.']);
}

require $autoloadPath;

load_env_file(__DIR__ . '/.env');

// Load recipient settings from DB (TO and CC), with env var as fallback.
$dbNotificationTo = '';
$dbNotificationCc = '';
try {
	require_once __DIR__ . '/includes/db.php';
	require_once __DIR__ . '/includes/repository.php';
	$_msgSettings = get_msg_notification_settings();
	$dbNotificationTo = trim((string)($_msgSettings['msg_notification_to'] ?? ''));
	$dbNotificationCc = trim((string)($_msgSettings['msg_notification_cc'] ?? ''));
} catch (Throwable $dbErr) {
	error_log('Ace-mtech contact: DB notification lookup failed – ' . $dbErr->getMessage());
}

$honeypot = post_string('website');
if ($honeypot !== '') {
	respond_json(200, ['ok' => true]);
}

$limitWindowSeconds = 600;
$limitMax = 3;
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'acemtech_rate_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $ipAddress) . '.json';
$now = time();
$recentAttempts = load_rate_window($rateFile, $limitWindowSeconds, $now);

if (count($recentAttempts) >= $limitMax) {
	respond_json(429, ['ok' => false, 'message' => 'Too many requests. Please try again shortly.']);
}

$recentAttempts[] = $now;
@file_put_contents($rateFile, json_encode($recentAttempts), LOCK_EX);

$name = post_string('name');
$company = post_string('company');
$email = post_string('email');
$message = post_string('message');

if ($name === '' || $email === '' || $message === '') {
	respond_json(422, ['ok' => false, 'message' => 'Please fill out all required fields.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	respond_json(422, ['ok' => false, 'message' => 'Please enter a valid email address.']);
}

$smtpConfig = [
	'host' => env_value('SMTP_HOST', 'localhost'),
	'port' => (int) env_value('SMTP_PORT', '25'),
	'user' => env_value('SMTP_USER', 'no-reply1@ace-mtech.ph'),
	'pass' => env_value('SMTP_PASS', ''),
	'from' => env_value('SMTP_FROM', 'no-reply1@ace-mtech.ph'),
	'from_name' => env_value('SMTP_FROM_NAME', 'Ace-mtech Website'),
	'to' => $dbNotificationTo !== '' ? $dbNotificationTo : env_value('SMTP_TO', ''),
	'cc' => $dbNotificationCc,
	'secure' => strtolower(env_value('SMTP_SECURE', 'none')),
	'auth' => env_flag('SMTP_AUTH', false),
	'timeout' => max(5, (int) env_value('SMTP_TIMEOUT', '15')),
];

if ($smtpConfig['host'] === '' || $smtpConfig['from'] === '' || $smtpConfig['to'] === '') {
	respond_json(500, ['ok' => false, 'message' => 'Server email is not configured.']);
}

if ($smtpConfig['auth'] && ($smtpConfig['user'] === '' || $smtpConfig['pass'] === '')) {
	respond_json(500, ['ok' => false, 'message' => 'SMTP authentication is enabled but SMTP_USER or SMTP_PASS is missing.']);
}

if (!filter_var($smtpConfig['to'], FILTER_VALIDATE_EMAIL)) {
	respond_json(500, ['ok' => false, 'message' => 'Server recipient email is invalid.']);
}

[$fromEmail, $fromName] = resolve_sender($smtpConfig['from'], $smtpConfig['from_name']);
if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
	respond_json(500, ['ok' => false, 'message' => 'Server sender email is invalid.']);
}

$subject = 'Project inquiry' . ($company !== '' ? ' - ' . $company : '');
$body =
	"Name: {$name}\n" .
	'Company: ' . ($company !== '' ? $company : '-') . "\n" .
	"Email: {$email}\n\n" .
	$message . "\n";

try {
	$mail = null;
	$mail = new PHPMailer(true);
	apply_smtp_transport($mail, $smtpConfig);
	$mail->setFrom($fromEmail, $fromName);
	$mail->addAddress($smtpConfig['to']);
	if ($smtpConfig['cc'] !== '' && filter_var($smtpConfig['cc'], FILTER_VALIDATE_EMAIL)) {
		$mail->addCC($smtpConfig['cc']);
	}
	$mail->addReplyTo($email, $name);
	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->send();

	respond_json(200, ['ok' => true, 'message' => 'Request submitted. We will get back to you shortly.']);
} catch (Exception $exception) {
	$rawError = ($mail instanceof PHPMailer && $mail->ErrorInfo !== '') ? $mail->ErrorInfo : $exception->getMessage();
	$transportSummary = sprintf(
		'host=%s port=%d secure=%s auth=%s from=%s to=%s',
		$smtpConfig['host'],
		$smtpConfig['port'],
		$smtpConfig['secure'],
		$smtpConfig['auth'] ? 'true' : 'false',
		$fromEmail,
		$smtpConfig['to']
	);
	error_log('Ace-mtech mail error: ' . $rawError . ' | ' . $transportSummary);

	if (stripos($rawError, 'authenticate') !== false) {
		respond_json(500, ['ok' => false, 'message' => 'SMTP authentication failed. Check the cPanel mailbox username and password.']);
	}

	respond_json(500, ['ok' => false, 'message' => 'Failed to send. Please try again later.']);
}
