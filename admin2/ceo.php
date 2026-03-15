<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";
require_once __DIR__ . "/../includes/image-upload.php";

require_admin_login();

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$action = (string)($_POST["action"] ?? "save_content");

	if ($action === "save_portrait") {
		$file = $_FILES["ceo_portrait"] ?? null;
		if (!$file || (int)($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
			$errors[] = "Please choose a portrait image to upload.";
		} else {
			$previousPortraitPath = get_ceo_portrait_path();
			$newPortraitPath = upload_image_file(
				$file,
				__DIR__ . "/../source_img/ceo",
				"source_img/ceo",
				"ceo-portrait",
				$errors
			);

			if (!$errors && $newPortraitPath !== null) {
				if (set_ceo_portrait_path($newPortraitPath)) {
					$success = "CEO portrait updated successfully.";
					if (
						$previousPortraitPath !== $newPortraitPath
						&& strpos($previousPortraitPath, "source_img/ceo/") === 0
					) {
						delete_uploaded_image_file($previousPortraitPath);
					}
				} else {
					$errors[] = "Failed to save CEO portrait setting.";
					delete_uploaded_image_file($newPortraitPath);
				}
			}
		}
	} else {
		$defaults = ceo_settings_defaults();
		$payload = [];
		foreach ($defaults as $key => $_defaultValue) {
			if ($key === "ceo_portrait_path") {
				continue;
			}
			$payload[$key] = trim((string)($_POST[$key] ?? ""));
		}

		if (set_ceo_settings($payload)) {
			$success = "CEO content updated successfully.";
		} else {
			$errors[] = "Failed to save CEO content.";
		}
	}
}

$ceo = get_ceo_settings();
$ceoPortraitPath = get_ceo_portrait_path();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>CEO Content - Ace-mtech Admin</title>
	<style>
		*{box-sizing:border-box;margin:0;padding:0}
		body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:#f8fafc;color:#0f172a;line-height:1.6}
		.adminLayout{display:flex;min-height:100vh}
		.adminSidebar{position:fixed;left:0;top:0;bottom:0;width:260px;background:linear-gradient(180deg,#1e293b 0%,#0f172a 100%);color:#fff;padding:0;overflow-y:auto;z-index:100;box-shadow:4px 0 24px rgba(0,0,0,0.2)}
		.adminBrand{padding:28px 24px;border-bottom:1px solid rgba(255,255,255,0.1);background:rgba(0,0,0,0.2)}
		.adminBrand h2{margin:0 0 6px;font-size:22px;font-weight:800;color:#fff;letter-spacing:-0.5px}
		.adminBrand p{margin:0;font-size:11px;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:1px;font-weight:600}
		.adminNav{list-style:none;padding:16px 0;margin:0}
		.adminNav li{margin:0}
		.adminNav a{display:flex;align-items:center;gap:12px;padding:12px 24px;color:rgba(255,255,255,0.75);text-decoration:none;font-weight:500;transition:all 0.2s ease;font-size:14px;position:relative}
		.adminNav a:hover{background:rgba(255,255,255,0.08);color:#fff}
		.adminNav a.active{background:linear-gradient(90deg,rgba(22,163,74,0.2),transparent);color:#fff;font-weight:600}
		.adminNav a.active::before{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:linear-gradient(180deg,#22c55e,#16a34a);box-shadow:0 0 12px rgba(34,197,94,0.5)}
		.adminNav svg{width:20px;height:20px;flex-shrink:0;stroke:currentColor;fill:none;stroke-width:2.2;stroke-linecap:round;stroke-linejoin:round}
		.adminNavDivider{height:1px;background:rgba(255,255,255,0.08);margin:12px 16px}
		.adminMain{flex:1;margin-left:260px;padding:40px;max-width:calc(100% - 260px);min-height:100vh}
		.adminPageHeader{margin-bottom:32px;padding-bottom:24px;border-bottom:2px solid #e2e8f0}
		.adminPageHeader h1{margin:0 0 10px;font-size:36px;font-weight:800;color:#0f172a;letter-spacing:-1px}
		.adminPageHeader p{margin:0;font-size:16px;color:#64748b;line-height:1.7}
		.alert{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-size:14px;line-height:1.6}
		.alert.error{background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b}
		.alert.success{background:#dcfce7;border:1.5px solid #86efac;color:#166534}
		.adminCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:24px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:22px}
		.adminCard h3{margin:0 0 14px;font-size:18px;color:#0f172a}
		.grid2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
		.grid3{display:grid;grid-template-columns:100px 1.3fr 2fr;gap:12px}
		.field{display:grid;gap:6px}
		.field label{font-size:12px;text-transform:uppercase;letter-spacing:.4px;color:#64748b;font-weight:700}
		.field input,.field textarea{width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all .2s}
		.field textarea{min-height:92px;resize:vertical}
		.field input:focus,.field textarea:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,.1)}
		.adminBtnPrimary{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px;transition:all .2s;box-shadow:0 2px 8px rgba(22,163,74,.2);border:none;cursor:pointer}
		.adminBtnPrimary:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,.3)}
		.logoSettingRow{display:grid;grid-template-columns:140px 1fr;gap:18px;align-items:center}
		.logoPreview{width:120px;height:120px;border:1.5px solid #e2e8f0;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;padding:10px}
		.logoPreview img{max-width:100%;max-height:100%;object-fit:cover;border-radius:8px}
		.logoHint{margin:0;font-size:13px;color:#64748b}
		.formActions{display:flex;justify-content:flex-end;gap:12px;margin-top:16px}
		@media (max-width:1024px){.adminSidebar{width:220px}.adminMain{margin-left:220px;max-width:calc(100% - 220px)}}
		@media (max-width:900px){.grid2,.grid3,.logoSettingRow{grid-template-columns:1fr}}
		@media (max-width:768px){.adminSidebar{width:70px}.adminSidebar .adminBrand h2,.adminSidebar .adminBrand p,.adminNav span{display:none}.adminNav a{justify-content:center;padding:14px}.adminMain{margin-left:70px;max-width:calc(100% - 70px);padding:24px 16px}.adminPageHeader h1{font-size:26px}}
	</style>
</head>
<body>
	<div class="adminLayout">
		<aside class="adminSidebar">
			<div class="adminBrand">
				<h2>Ace-mtech</h2>
				<p>Admin Panel</p>
			</div>
			<ul class="adminNav">
				<li><a href="index.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg><span>Dashboard</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="clients.php"><svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg><span>Clients</span></a></li>
				<li><a href="partners.php"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg><span>Partners</span></a></li>
				<li><a href="products.php"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Products</span></a></li>
				<li><a href="gallery.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a></li>
				<li><a href="news.php"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>News</span></a></li>
				<li><a href="social-links.php"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg><span>Social Links</span></a></li>
				<li><a href="ceo.php" class="active"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg><span>CEO Content</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="../admin/messages/admin.html"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>Messages</span></a></li>
				<li><a href="../index.php"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><span>View Site</span></a></li>
				<li><a href="logout.php"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Log Out</span></a></li>
			</ul>
		</aside>
		<main class="adminMain">
			<div class="adminPageHeader">
				<h1>CEO Content</h1>
				<p>Update all content displayed on the public Meet Our CEO page.</p>
			</div>

			<?php if ($errors): ?>
				<div class="alert error">
					<?php foreach ($errors as $error): ?>
						<div><?php echo htmlspecialchars($error); ?></div>
					<?php endforeach; ?>
				</div>
			<?php elseif ($success): ?>
				<div class="alert success"><?php echo htmlspecialchars($success); ?></div>
			<?php endif; ?>

			<div class="adminCard">
				<h3>CEO Portrait</h3>
				<div class="logoSettingRow">
					<div class="logoPreview">
						<img src="../<?php echo htmlspecialchars($ceoPortraitPath); ?>" alt="Current CEO portrait" />
					</div>
					<form method="post" enctype="multipart/form-data" class="field">
						<input type="hidden" name="action" value="save_portrait" />
						<input type="file" name="ceo_portrait" accept="<?php echo htmlspecialchars(supported_image_accept_value()); ?>" required />
						<p class="logoHint">Recommended: portrait image with good lighting and a clean background.</p>
						<div><button class="adminBtnPrimary" type="submit">Update Portrait</button></div>
					</form>
				</div>
			</div>

			<form method="post">
				<input type="hidden" name="action" value="save_content" />

				<div class="adminCard">
					<h3>Hero Section</h3>
					<div class="grid2">
						<div class="field">
							<label for="ceo_badge">Badge</label>
							<input id="ceo_badge" type="text" name="ceo_badge" value="<?php echo htmlspecialchars((string)$ceo["ceo_badge"]); ?>" />
						</div>
						<div class="field">
							<label for="ceo_heading">Heading</label>
							<input id="ceo_heading" type="text" name="ceo_heading" value="<?php echo htmlspecialchars((string)$ceo["ceo_heading"]); ?>" />
						</div>
					</div>
					<div class="field" style="margin-top:12px">
						<label for="ceo_description">Description</label>
						<textarea id="ceo_description" name="ceo_description"><?php echo htmlspecialchars((string)$ceo["ceo_description"]); ?></textarea>
					</div>
				</div>

				<div class="adminCard">
					<h3>CEO Identity</h3>
					<div class="grid2">
						<div class="field">
							<label for="ceo_name">Name</label>
							<input id="ceo_name" type="text" name="ceo_name" value="<?php echo htmlspecialchars((string)$ceo["ceo_name"]); ?>" />
						</div>
						<div class="field">
							<label for="ceo_role">Title</label>
							<input id="ceo_role" type="text" name="ceo_role" value="<?php echo htmlspecialchars((string)$ceo["ceo_role"]); ?>" />
						</div>
					</div>
					<div class="field" style="margin-top:12px">
						<label for="ceo_quote">Quote</label>
						<textarea id="ceo_quote" name="ceo_quote"><?php echo htmlspecialchars((string)$ceo["ceo_quote"]); ?></textarea>
					</div>
				</div>

				<div class="adminCard">
					<h3>Stats</h3>
					<?php for ($i = 1; $i <= 4; $i++): ?>
						<div class="grid2" style="margin-top:<?php echo $i === 1 ? "0" : "12px"; ?>">
							<div class="field">
								<label for="ceo_stat_<?php echo $i; ?>_value">Stat <?php echo $i; ?> Value</label>
								<input id="ceo_stat_<?php echo $i; ?>_value" type="text" name="ceo_stat_<?php echo $i; ?>_value" value="<?php echo htmlspecialchars((string)$ceo["ceo_stat_{$i}_value"]); ?>" />
							</div>
							<div class="field">
								<label for="ceo_stat_<?php echo $i; ?>_label">Stat <?php echo $i; ?> Label</label>
								<input id="ceo_stat_<?php echo $i; ?>_label" type="text" name="ceo_stat_<?php echo $i; ?>_label" value="<?php echo htmlspecialchars((string)$ceo["ceo_stat_{$i}_label"]); ?>" />
							</div>
						</div>
					<?php endfor; ?>
				</div>

				<div class="adminCard">
					<h3>Achievements Section</h3>
					<div class="grid2">
						<div class="field">
							<label for="ceo_achievements_heading">Section Heading</label>
							<input id="ceo_achievements_heading" type="text" name="ceo_achievements_heading" value="<?php echo htmlspecialchars((string)$ceo["ceo_achievements_heading"]); ?>" />
						</div>
						<div class="field">
							<label for="ceo_achievements_description">Section Description</label>
							<textarea id="ceo_achievements_description" name="ceo_achievements_description"><?php echo htmlspecialchars((string)$ceo["ceo_achievements_description"]); ?></textarea>
						</div>
					</div>
				</div>

				<?php for ($i = 1; $i <= 4; $i++): ?>
					<div class="adminCard">
						<h3>Achievement <?php echo $i; ?></h3>
						<div class="grid3">
							<div class="field">
								<label for="ceo_achievement_<?php echo $i; ?>_year">Year</label>
								<input id="ceo_achievement_<?php echo $i; ?>_year" type="text" name="ceo_achievement_<?php echo $i; ?>_year" value="<?php echo htmlspecialchars((string)$ceo["ceo_achievement_{$i}_year"]); ?>" />
							</div>
							<div class="field">
								<label for="ceo_achievement_<?php echo $i; ?>_title">Title</label>
								<input id="ceo_achievement_<?php echo $i; ?>_title" type="text" name="ceo_achievement_<?php echo $i; ?>_title" value="<?php echo htmlspecialchars((string)$ceo["ceo_achievement_{$i}_title"]); ?>" />
							</div>
							<div class="field">
								<label for="ceo_achievement_<?php echo $i; ?>_description">Description</label>
								<textarea id="ceo_achievement_<?php echo $i; ?>_description" name="ceo_achievement_<?php echo $i; ?>_description"><?php echo htmlspecialchars((string)$ceo["ceo_achievement_{$i}_description"]); ?></textarea>
							</div>
						</div>
					</div>
				<?php endfor; ?>

				<div class="formActions">
					<button class="adminBtnPrimary" type="submit">Save CEO Content</button>
				</div>
			</form>
		</main>
	</div>
	<script type="module" src="messages-unread-badge.js"></script>
</body>
</html>
