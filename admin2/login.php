<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

$error = "";
$needsSetup = false;

try {
	$needsSetup = admin_count() === 0;
} catch (Throwable $e) {
	$error = "Database connection failed. Check your database settings.";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$user = trim($_POST["username"] ?? "");
	$pass = $_POST["password"] ?? "";

	if (attempt_admin_login($user, $pass)) {
		header("Location: /Ace-mtech/admin2/index.php");
		exit;
	}

	$error = "Invalid username or password.";
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Admin Login</title>
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
	</style>
</head>
<body>
	<div class="loginShell">
		<h1 style="margin-bottom:10px">Admin login</h1>
		<p class="sectionDesc" style="margin-bottom:22px">Sign in to manage clients and partners.</p>

		<div class="loginCard">
			<?php if ($error): ?>
				<div class="alert error"><?php echo htmlspecialchars($error); ?></div>
			<?php endif; ?>

			<form method="post">
				<div class="formRow">
					<label for="username">Username</label>
					<input id="username" name="username" type="text" required />
				</div>
				<div class="formRow">
					<label for="password">Password</label>
					<input id="password" name="password" type="password" required />
				</div>
				<button class="cta" type="submit"><span>Sign in</span><span aria-hidden="true">→</span></button>
			</form>

			<?php if ($needsSetup): ?>
				<p class="sectionDesc" style="margin-top:16px">
					No admin account yet. <a href="setup.php">Create an admin account</a>.
				</p>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
