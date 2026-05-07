<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";
require_once __DIR__ . "/../includes/image-upload.php";

require_admin_login();

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$action = (string)($_POST["action"] ?? "add");

	if ($action === "delete") {
		$id = (int)($_POST["id"] ?? 0);
		$partner = $id > 0 ? get_partner($id) : null;

		if (!$partner) {
			$errors[] = "Invalid partner.";
		} elseif (!delete_partner($id)) {
			$errors[] = "Failed to delete partner.";
		} else {
			if (!empty($partner["image_path"])) {
				delete_uploaded_image_file((string)$partner["image_path"]);
			}
			$success = "Partner deleted successfully.";
		}
	} else {
		$name = trim($_POST["name"] ?? "");
		if ($name === "") {
			$errors[] = "Partner name is required.";
		}

		if (!isset($_FILES["photo"]) || $_FILES["photo"]["error"] !== UPLOAD_ERR_OK) {
			$errors[] = "Partner photo is required.";
		}

		if (!$errors) {
			$uploadDir = __DIR__ . "/../source_img/partners";
			if (!is_dir($uploadDir)) {
				mkdir($uploadDir, 0775, true);
			}

			$file = $_FILES["photo"];
			$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
			$allowed = ["jpg", "jpeg", "png", "webp"];
			if (!in_array($ext, $allowed, true)) {
				$errors[] = "Photo must be a JPG, PNG, or WEBP file.";
			}

			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime = $finfo ? finfo_file($finfo, $file["tmp_name"]) : "";
			if ($finfo) {
				finfo_close($finfo);
			}
			$allowedMime = ["image/jpeg", "image/png", "image/webp"];
			if ($mime && !in_array($mime, $allowedMime, true)) {
				$errors[] = "Photo must be a valid image file.";
			}

			if (!$errors) {
				$filename = "partner-" . date("YmdHis") . "-" . bin2hex(random_bytes(4)) . "." . $ext;
				$targetPath = $uploadDir . "/" . $filename;
				if (move_uploaded_file($file["tmp_name"], $targetPath)) {
					$relativePath = "source_img/partners/" . $filename;
					if (add_partner($name, $relativePath)) {
						$success = "Partner added successfully.";
					} else {
						$errors[] = "Failed to save partner list.";
					}
				} else {
					$errors[] = "Failed to upload photo.";
				}
			}
		}
	}
}

$partners = get_partners();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Partners · Ace-mtech Admin</title>
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
		.frontendIndicator{margin-top:14px;padding:12px 14px;background:#ecfdf5;border:1.5px solid #86efac;border-radius:10px;color:#14532d;font-size:13px;line-height:1.6}
		.frontendIndicator strong{display:block;font-size:12px;letter-spacing:0.3px;text-transform:uppercase;color:#166534;margin-bottom:4px}
		.frontendIndicator a{color:#166534;text-decoration:underline;font-weight:700}
		.frontendIndicator a:hover{color:#14532d}
		.adminCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:28px}
		.adminCard h3{margin:0 0 20px;font-size:18px;font-weight:700;color:#0f172a}
		.adminCard p{margin:0 0 16px;font-size:14px;color:#64748b}
		.formGrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
		.formGrid label{display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px}
		.formGrid input,.formGrid textarea{width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s}
		.formGrid input:focus,.formGrid textarea:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.1)}
		.adminBtnPrimary{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px;transition:all 0.3s;box-shadow:0 4px 12px rgba(22,163,74,0.25);border:none;cursor:pointer;margin-top:16px}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(22,163,74,0.35)}
		.adminBtnSecondary{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#fff;color:#0f172a;text-decoration:none;border:1.5px solid #e2e8f0;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s}
		.adminBtnSecondary:hover{background:#f8fafc;border-color:#16a34a}
		.adminBtnDanger{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#fff;color:#991b1b;text-decoration:none;border:1.5px solid #fca5a5;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s;cursor:pointer}
		.adminBtnDanger:hover{background:#fee2e2;border-color:#ef4444}
		.alert{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-size:14px;line-height:1.6}
		.alert.error{background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b}
		.alert.success{background:#dcfce7;border:1.5px solid #86efac;color:#166534}
		.previewGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:16px}
		.previewCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;text-align:center;transition:all 0.2s}
		.previewCard:hover{box-shadow:0 8px 24px rgba(0,0,0,0.1);transform:translateY(-2px);border-color:#16a34a}
		.previewCard img{max-width:100%;max-height:70px;object-fit:contain;display:block;margin:0 auto 12px}
		.previewCard>div{font-weight:600;font-size:14px;color:#0f172a;margin-bottom:12px}
		.previewActions{margin-top:12px;display:flex;justify-content:center;gap:8px}
		.inlineForm{margin:0}
		@media (max-width:1024px){.adminSidebar{width:220px}.adminMain{margin-left:220px;max-width:calc(100% - 220px)}}
		@media (max-width:768px){.adminSidebar{width:70px}.adminSidebar .adminBrand h2,.adminSidebar .adminBrand p,.adminNav span{display:none}.adminNav a{justify-content:center;padding:14px}.adminMain{margin-left:70px;max-width:calc(100% - 70px);padding:24px 16px}.adminPageHeader h1{font-size:26px}.formGrid{grid-template-columns:1fr}}
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
				<li><a href="partners.php" class="active"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg><span>Partners</span></a></li>
				<li><a href="products.php"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Products</span></a></li>
				<li><a href="gallery.php"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a></li>
				<li><a href="news.php"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span>News</span></a></li>
				<li><a href="social-links.php"><svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg><span>Social Links</span></a></li>
				<li><a href="ceo.php"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg><span>CEO Content</span></a></li>
				<li class="adminNavDivider"></li>
				<li><a href="../admin/messages/admin.html"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg><span>Messages</span></a></li>
				<li><a href="../index.php"><svg viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg><span>View Site</span></a></li>
				<li><a href="logout.php"><svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg><span>Log Out</span></a></li>
			</ul>
		</aside>
		<main class="adminMain">
			<div class="adminPageHeader">
				<h1>Partners</h1>
				<p>Add and manage partner names and logos displayed on your website.</p>
				<div class="frontendIndicator">
					<strong>Where this appears on your website</strong>
					Partner logos from this page are shown on <a href="../partners.php" target="_blank" rel="noopener noreferrer">Partners</a>.
				</div>
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

		<div class="adminCard" style="margin-bottom:20px">
			<h3>Add Partner</h3>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="add" />
				<div class="formGrid">
					<div>
						<label for="name">Partner Name</label>
						<input id="name" name="name" type="text" required />
					</div>
					<div>
						<label for="photo">Partner Logo/Photo</label>
						<input id="photo" name="photo" type="file" accept="image/*" required />
					</div>
				</div>
				<button class="adminBtnPrimary" type="submit"><span>Add Partner</span><span>→</span></button>
			</form>
		</div>

		<div class="adminCard">
			<h3>Current Partners</h3>
			<?php if (!$partners): ?>
				<p>No partners added yet.</p>
			<?php else: ?>
				<div class="previewGrid">
					<?php foreach ($partners as $partner): ?>
						<div class="previewCard">
							<?php if (!empty($partner["image_path"])): ?>
								<img src="../<?php echo htmlspecialchars($partner["image_path"]); ?>" alt="<?php echo htmlspecialchars($partner["name"]); ?> logo" />
							<?php endif; ?>
							<div><?php echo htmlspecialchars($partner["name"]); ?></div>
							<div class="previewActions">
								<a class="adminBtnSecondary" href="edit-partner.php?id=<?php echo (int)$partner["id"]; ?>">Edit</a>
								<form class="inlineForm" method="post" onsubmit="return confirm('Delete this partner?');">
									<input type="hidden" name="action" value="delete" />
									<input type="hidden" name="id" value="<?php echo (int)$partner["id"]; ?>" />
									<button class="adminBtnDanger" type="submit">Delete</button>
								</form>
							</div>
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
