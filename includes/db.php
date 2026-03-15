<?php

declare(strict_types=1);

function conn(): mysqli
{
	static $conn = null;
	if ($conn instanceof mysqli) {
		return $conn;
	}

	$config = require __DIR__ . "/config.php";
	$db = $config["db"] ?? [];
	$host = (string)($db["host"] ?? "localhost");
	$name = (string)($db["name"] ?? "ace-mtech");
	$user = (string)($db["user"] ?? "icto_randy3");
	$pass = (string)($db["pass"] ?? "091902Randsf2..");
	$charset = (string)($db["charset"] ?? "utf8mb4");

	$conn = new mysqli($host, $user, $pass, $name);
	if ($conn->connect_error) {
		throw new RuntimeException("Database connection failed: " . $conn->connect_error);
	}

	if (!$conn->set_charset($charset)) {
		throw new RuntimeException("Failed to set charset to {$charset}");
	}

	return $conn;
}

// Expose a conventional mysqli handle for code that expects $conn.
$conn = conn();

function db(): PDO
{
	static $pdo = null;
	if ($pdo instanceof PDO) {
		return $pdo;
	}

	$config = require __DIR__ . "/config.php";
	$db = $config["db"] ?? [];
	$host = $db["host"] ?? "127.0.0.1";
	$name = $db["name"] ?? "ace_mtech";
	$user = $db["user"] ?? "root";
	$pass = $db["pass"] ?? "";
	$charset = $db["charset"] ?? "utf8mb4";

	$dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
	$options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES => false,
	];

	$pdo = new PDO($dsn, $user, $pass, $options);
	return $pdo;
}
