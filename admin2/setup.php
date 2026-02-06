<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

$error = "";
$success = "";
$canSetup = true;

try {
	$hasAdmin = admin_count() > 0;
	if ($hasAdmin && !is_admin_logged_in()) {
		$canSetup = false;
	}
} catch (Throwable $e) {
	$canSetup = false;
	$error = "Database connection failed. Check your database settings.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && $canSetup) {
	$user = trim($_POST["username"] ?? "");
	$pass = $_POST["password"] ?? "";
	$confirm = $_POST["confirm_password"] ?? "";

	if ($user === "") {
		$error = "Username is required.";
	} elseif (strlen($pass) < 8) {
		$error = "Password must be at least 8 characters.";
	} elseif ($pass !== $confirm) {
		$error = "Passwords do not match.";
	} else {
		if (create_admin($user, $pass)) {
			$success = "Admin account created. You can sign in now.";
		} else {
			$error = "Failed to create admin account.";
		}
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Create Admin</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		body{background:var(--bg-soft)}
		.loginShell{max-width:520px;margin:0 auto;padding:48px 24px}
		.loginCard{border:1px solid var(--stroke);background:var(--panel);border-radius:20px;padding:28px;box-shadow:var(--shadow-sm)}
		.formRow{margin-bottom:14px}
		.formRow label{display:block;font-weight:600;margin-bottom:6px}
		.formRow input{width:100%;padding:12px 14px;border:1px solid var(--stroke);border-radius:12px;background:#fff}
		.alert{padding:12px 14px;border-radius:12px;margin-bottom:16px}
		.alert.error{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}
		.alert.success{background:#dcfce7;border:1px solid #bbf7d0;color:#166534}
	</style>
</head>
<body>
	<div class="loginShell">
		<h1 style="margin-bottom:10px">Create admin</h1>
		<p class="sectionDesc" style="margin-bottom:22px">Set up an admin account for the dashboard.</p>

		<div class="loginCard">
			<?php if ($error): ?>
				<div class="alert error"><?php echo htmlspecialchars($error); ?></div>
			<?php elseif ($success): ?>
				<div class="alert success"><?php echo htmlspecialchars($success); ?></div>
			<?php endif; ?>

			<?php if ($canSetup): ?>
				<form method="post">
					<div class="formRow">
						<label for="username">Username</label>
						<input id="username" name="username" type="text" required />
					</div>
					<div class="formRow">
						<label for="password">Password</label>
						<input id="password" name="password" type="password" minlength="8" required />
					</div>
					<div class="formRow">
						<label for="confirm_password">Confirm password</label>
						<input id="confirm_password" name="confirm_password" type="password" minlength="8" required />
					</div>
					<button class="cta" type="submit"><span>Create admin</span><span aria-hidden="true">→</span></button>
				</form>
			<?php else: ?>
				<p class="sectionDesc" style="margin:0">Admin setup is disabled. Please sign in.</p>
				<div style="margin-top:16px">
					<a class="cta" href="login.php"><span>Go to login</span><span aria-hidden="true">→</span></a>
				</div>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
