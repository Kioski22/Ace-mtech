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
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Admin Login</title>
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
		.adminBtnPrimary{background:linear-gradient(135deg,#16a34a 0%,#059669 100%);color:#fff;border:none;padding:14px 28px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;transition:all .3s;box-shadow:0 4px 12px rgba(22,163,74,0.3);width:100%;display:inline-flex;align-items:center;justify-content:center;gap:8px}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(22,163,74,0.4)}
		.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;font-size:14px}
		.alert.error{background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b}
		.loginCard a{color:#16a34a;text-decoration:none;font-weight:600;transition:color .2s}
		.loginCard a:hover{color:#059669;text-decoration:underline}
	</style>
</head>
<body>
	<div class="loginShell">
		<div class="loginCard">
			<h1>Admin Login</h1>
			<p class="sectionDesc">Sign in to manage clients and partners.</p>
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
				<button class="adminBtnPrimary" type="submit"><span>Sign in</span><span aria-hidden="true">→</span></button>
			</form>

				<?php if ($needsSetup): ?>
					<p class="sectionDesc" style="margin-top:20px;margin-bottom:0;text-align:center">
						No admin account yet. <a href="setup.php">Create an admin account</a>.
					</p>
				<?php endif; ?>
			</form>
		</div>
	</div>
</body>
</html>
