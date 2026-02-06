<?php

declare(strict_types=1);

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
