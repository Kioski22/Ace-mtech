<?php

declare(strict_types=1);

require_once __DIR__ . "/repository.php";

session_start();

function is_admin_logged_in(): bool
{
	return !empty($_SESSION["admin_logged_in"]);
}

function require_admin_login(): void
{
	if (!is_admin_logged_in()) {
		header("Location: login.php");
		exit;
	}
}

function attempt_admin_login(string $user, string $pass): bool
{
	$admin = verify_admin_credentials($user, $pass);
	if (!$admin) {
		return false;
	}

	$_SESSION["admin_logged_in"] = true;
	$_SESSION["admin_id"] = $admin["id"] ?? null;
	$_SESSION["admin_username"] = $admin["username"] ?? $user;
	return true;
}

function admin_logout(): void
{
	$_SESSION = [];
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
	}
	session_destroy();
}
