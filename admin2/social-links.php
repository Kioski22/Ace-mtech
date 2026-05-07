<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";
require_once __DIR__ . "/../includes/image-upload.php";

require_admin_login();

$errors = [];
$success = "";
$socialLinks = get_social_links(true);
$contactSettings = get_contact_settings();
$socialLinksById = [];
foreach ($socialLinks as $socialLink) {
	$socialLinksById[(int)$socialLink["id"]] = $socialLink;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$action = (string)($_POST["action"] ?? "save_social");

	if ($action === "save_logo") {
		$file = $_FILES["site_logo"] ?? null;
		if (!$file || (int)($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
			$errors[] = "Please choose a logo image to upload.";
		} else {
			$previousLogoPath = get_site_logo_path();
			$newLogoPath = upload_image_file(
				$file,
				__DIR__ . "/../source_img/logos",
				"source_img/logos",
				"site-logo",
				$errors
			);

			if (!$errors && $newLogoPath !== null) {
				if (set_site_logo_path($newLogoPath)) {
					$success = "Website logo updated successfully.";
					if ($previousLogoPath !== $newLogoPath && strpos($previousLogoPath, "source_img/logos/") === 0) {
						delete_uploaded_image_file($previousLogoPath);
					}
				} else {
					$errors[] = "Failed to save website logo setting.";
					delete_uploaded_image_file($newLogoPath);
				}
			}
		}
	} elseif ($action === "save_social_icon") {
		$id = (int)($_POST["id"] ?? 0);
		if ($id <= 0 || !isset($socialLinksById[$id])) {
			$errors[] = "Invalid social link for icon update.";
		} else {
			$file = $_FILES["social_icon"] ?? null;
			if (!$file || (int)($file["error"] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
				$errors[] = "Please choose an icon image to upload.";
			} else {
				$platformKey = (string)$socialLinksById[$id]["platform_key"];
				$previousIconPath = get_social_icon_path($platformKey);
				$newIconPath = upload_image_file(
					$file,
					__DIR__ . "/../source_img/icons",
					"source_img/icons",
					"social-" . $platformKey,
					$errors
				);

				if (!$errors && $newIconPath !== null) {
					if (set_social_icon_path($platformKey, $newIconPath)) {
						$success = "Social icon updated.";
						if (
							$previousIconPath !== ""
							&& $previousIconPath !== $newIconPath
							&& strpos($previousIconPath, "source_img/icons/") === 0
						) {
							delete_uploaded_image_file($previousIconPath);
						}
					} else {
						$errors[] = "Failed to save social icon setting.";
						delete_uploaded_image_file($newIconPath);
					}
				}
			}
		}
	} elseif ($action === "save_contact") {
		$phone = trim((string)($_POST["contact_phone"] ?? ""));
		$email = trim((string)($_POST["contact_email"] ?? ""));
		$location = trim((string)($_POST["contact_location"] ?? ""));
		$website = trim((string)($_POST["contact_website"] ?? ""));

		if ($phone === "") {
			$errors[] = "Phone is required.";
		}

		if ($email === "") {
			$errors[] = "Email is required.";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$errors[] = "Please enter a valid email address.";
		}

		if ($location === "") {
			$errors[] = "Location is required.";
		}

		if ($website === "") {
			$errors[] = "Website is required.";
		} else {
			if (!preg_match('#^https?://#i', $website)) {
				$website = "https://" . $website;
			}

			if (!filter_var($website, FILTER_VALIDATE_URL)) {
				$errors[] = "Please enter a valid website URL.";
			}
		}

		if (!$errors) {
			if (set_contact_settings([
				"contact_phone" => $phone,
				"contact_email" => $email,
				"contact_location" => $location,
				"contact_website" => $website,
			])) {
				$success = "Contact details updated.";
			} else {
				$errors[] = "Failed to update contact details.";
			}
		}
	} else {
		$id = (int)($_POST["id"] ?? 0);
		$url = trim($_POST["url"] ?? "");
		$displayOrder = (int)($_POST["display_order"] ?? 0);
		$isVisible = isset($_POST["is_visible"]);

		if ($id <= 0) {
			$errors[] = "Invalid social link.";
		}

		if ($url === "") {
			$errors[] = "URL is required.";
		} elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
			$errors[] = "Please enter a valid URL.";
		} else {
			$scheme = strtolower((string)parse_url($url, PHP_URL_SCHEME));
			if ($scheme !== "http" && $scheme !== "https") {
				$errors[] = "URL must start with http:// or https://.";
			}
		}

		if (!$errors) {
			if (update_social_link_settings($id, $url, $displayOrder, $isVisible)) {
				$success = "Social link updated.";
			} else {
				$errors[] = "Failed to update social link.";
			}
		}
	}

	$socialLinks = get_social_links(true);
	$contactSettings = get_contact_settings();
}

$siteLogoPath = get_site_logo_path();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Social Links · Ace-mtech Admin</title>
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
		.adminCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:28px}
		.adminCard h3{margin:0 0 20px;font-size:18px;font-weight:700;color:#0f172a}
		.adminCard p{margin:0 0 16px;font-size:14px;color:#64748b}
		.alert{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-size:14px;line-height:1.6}
		.alert.error{background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b}
		.alert.success{background:#dcfce7;border:1.5px solid #86efac;color:#166534}
		.list{display:grid;gap:16px}
		.item{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:18px;transition:all 0.2s}
		.item:hover{box-shadow:0 4px 16px rgba(0,0,0,0.08);border-color:#16a34a}
		.itemHead{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px}
		.itemTitle{margin:0;font-weight:700;font-size:16px;color:#0f172a;display:flex;align-items:center;gap:10px}
		.itemDesc{margin:0;color:#64748b;font-size:13px;line-height:1.6}
		.formRow{display:grid;grid-template-columns:2fr 130px auto auto;gap:12px;margin-top:14px;align-items:center}
		.formRow input[type="url"],.formRow input[type="number"]{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s}
		.formRow input:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.1)}
		.formRow label{display:flex;align-items:center;gap:8px;font-size:13px;color:#0f172a;font-weight:500;cursor:pointer}
		.formRow label input{cursor:pointer}
		.formRow button{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s;box-shadow:0 2px 8px rgba(22,163,74,0.2);border:none;cursor:pointer}
		.formRow button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,0.3)}
		.badge{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;border-radius:999px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;font-weight:700;font-size:16px}
		.badge img{width:20px;height:20px;object-fit:contain;display:block}
		.iconUploadRow{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:12px}
		.iconUploadRow input[type="file"]{max-width:260px;padding:8px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-size:12px}
		.iconUploadRow button{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:12px;transition:all 0.2s;box-shadow:0 2px 8px rgba(22,163,74,0.2);border:none;cursor:pointer}
		.iconUploadRow button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,0.3)}
		.logoSettingRow{display:grid;grid-template-columns:140px 1fr;gap:18px;align-items:center}
		.logoPreview{width:120px;height:120px;border:1.5px solid #e2e8f0;border-radius:14px;background:#fff;display:flex;align-items:center;justify-content:center;padding:10px}
		.logoPreview img{max-width:100%;max-height:100%;object-fit:contain}
		.logoForm{display:grid;gap:12px}
		.logoForm input[type="file"]{padding:10px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff}
		.logoHint{margin:0;font-size:13px;color:#64748b}
		.logoForm button{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s;box-shadow:0 2px 8px rgba(22,163,74,0.2);border:none;cursor:pointer}
		.logoForm button:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(22,163,74,0.3)}
		@media (max-width:1024px){.adminSidebar{width:220px}.adminMain{margin-left:220px;max-width:calc(100% - 220px)}}
		@media (max-width:900px){.formRow{grid-template-columns:1fr}}
		@media (max-width:900px){.logoSettingRow{grid-template-columns:1fr}.logoPreview{width:100px;height:100px}}
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
				<li><a href="social-links.php" class="active"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg><span>Social Links</span></a></li>
				<li><a href="ceo.php"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg><span>CEO Content</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="../admin/messages/admin.html"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>Messages</span></a></li>
				<li><a href="../index.php"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><span>View Site</span></a></li>
				<li><a href="logout.php"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Log Out</span></a></li>
			</ul>
		</aside>
		<main class="adminMain">
			<div class="adminPageHeader">
				<h1>Social Links</h1>
				<p>Manage URLs shown in the <strong>Connect with us</strong> section on the products page.</p>
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
			<h3>Website Logo</h3>
			<p>Upload the logo used in the public site header. This updates all public pages automatically.</p>
			<div class="logoSettingRow">
				<div class="logoPreview">
					<img src="../<?php echo htmlspecialchars($siteLogoPath); ?>" alt="Current website logo" />
				</div>
				<form class="logoForm" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action" value="save_logo" />
					<input type="file" name="site_logo" accept="<?php echo htmlspecialchars(supported_image_accept_value()); ?>" required />
					<p class="logoHint">Recommended: transparent PNG or SVG. Maximum clarity at around 300x300 px.</p>
					<div>
						<button type="submit"><span>Update Logo</span></button>
					</div>
				</form>
			</div>
		</div>

		<div class="adminCard">
			<h3>Contact Details</h3>
			<p>Updates the Phone, Email, Location, and Website shown on the public contact page.</p>
			<form method="post">
				<input type="hidden" name="action" value="save_contact" />
				<div class="list" style="gap:14px">
					<div>
						<label style="display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px" for="contact_phone">Phone</label>
						<input id="contact_phone" name="contact_phone" type="text" value="<?php echo htmlspecialchars((string)$contactSettings["contact_phone"]); ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s" />
					</div>
					<div>
						<label style="display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px" for="contact_email">Email</label>
						<input id="contact_email" name="contact_email" type="email" value="<?php echo htmlspecialchars((string)$contactSettings["contact_email"]); ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s" />
					</div>
					<div>
						<label style="display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px" for="contact_location">Location</label>
						<textarea id="contact_location" name="contact_location" rows="3" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s;resize:vertical"><?php echo htmlspecialchars((string)$contactSettings["contact_location"]); ?></textarea>
					</div>
					<div>
						<label style="display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px" for="contact_website">Website URL</label>
						<input id="contact_website" name="contact_website" type="text" value="<?php echo htmlspecialchars((string)$contactSettings["contact_website"]); ?>" required style="width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s" />
					</div>
					<div>
						<button type="submit" style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s;box-shadow:0 2px 8px rgba(22,163,74,0.2);border:none;cursor:pointer"><span>Save Contact Details</span></button>
					</div>
				</div>
			</form>
		</div>

		<div class="adminCard">
			<h3>Connect With Us Entries</h3>
			<?php if (!$socialLinks): ?>
				<p>No social links found.</p>
			<?php else: ?>
				<div class="list">
					<?php foreach ($socialLinks as $link): ?>
						<div class="item">
							<form method="post" enctype="multipart/form-data">
								<input type="hidden" name="action" value="save_social_icon" />
								<input type="hidden" name="id" value="<?php echo (int)$link["id"]; ?>" />
								<div class="itemHead">
									<div>
										<h4 class="itemTitle">
											<span class="badge" aria-hidden="true">
												<?php if (!empty($link["icon_path"])): ?>
													<img src="../<?php echo htmlspecialchars((string)$link["icon_path"]); ?>" alt="<?php echo htmlspecialchars($link["name"]); ?> icon" />
												<?php else: ?>
													<?php echo $link["icon"]; ?>
												<?php endif; ?>
											</span>
											<?php echo htmlspecialchars($link["name"]); ?>
										</h4>
										<p class="itemDesc"><?php echo htmlspecialchars($link["description"]); ?></p>
									</div>
								</div>
								<div class="iconUploadRow">
									<input type="file" name="social_icon" accept="<?php echo htmlspecialchars(supported_image_accept_value()); ?>" required />
									<button type="submit"><span>Update Icon</span></button>
								</div>
							</form>

							<form method="post">
							<input type="hidden" name="action" value="save_social" />
							<input type="hidden" name="id" value="<?php echo (int)$link["id"]; ?>" />
							<div class="formRow">
								<input type="url" name="url" value="<?php echo htmlspecialchars((string)$link["url"]); ?>" required />
								<input type="number" name="display_order" value="<?php echo (int)$link["display_order"]; ?>" />
								<label><input type="checkbox" name="is_visible" <?php echo (int)$link["is_visible"] === 1 ? "checked" : ""; ?> /> Visible</label>
								<button class="formRow button" type="submit"><span>Save</span></button>
							</div>
							</form>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		</main>
	</div>
	<script type="module" src="messages-unread-badge.js"></script>
</body>
</html>
