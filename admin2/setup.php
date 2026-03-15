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
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Create Admin</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		*{margin:0;padding:0;box-sizing:border-box}
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
		.loginShell{max-width:520px;width:100%}
		.loginCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:20px;padding:40px;box-shadow:0 20px 60px rgba(0,0,0,0.3)}
		.loginCard h1{font-size:32px;font-weight:700;color:#1e293b;margin-bottom:8px;letter-spacing:-0.5px}
		.loginCard .sectionDesc{color:#64748b;font-size:15px;margin-bottom:32px}
		.formRow{margin-bottom:20px}
		.formRow label{display:block;font-weight:600;margin-bottom:8px;color:#334155;font-size:14px}
		.formRow input{width:100%;padding:14px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:15px;transition:all .2s}
		.formRow input:focus{outline:none;border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.1)}
		.adminBtnPrimary{background:linear-gradient(135deg,#16a34a 0%,#059669 100%);color:#fff;border:none;padding:14px 28px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;transition:all .3s;box-shadow:0 4px 12px rgba(22,163,74,0.3);width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px;text-decoration:none}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(22,163,74,0.4)}
		.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px}
		.alert.error{background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b}
		.alert.success{background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46}
	</style>
</head>
<body>
	<div class="loginShell">
		<div class="loginCard">
			<h1>Create Admin</h1>
			<p class="sectionDesc">Set up an admin account for the dashboard.</p>
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
					<button class="adminBtnPrimary" type="submit"><span>Create admin</span><span aria-hidden="true">→</span></button>
				</form>
				</form>
			<?php else: ?>
				<p class="sectionDesc" style="margin-bottom:20px">Admin setup is disabled. Please sign in.</p>
				<a class="adminBtnPrimary" href="login.php"><span>Go to login</span><span aria-hidden="true">→</span></a>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
