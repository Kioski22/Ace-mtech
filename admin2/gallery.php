<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";
require_once __DIR__ . "/../includes/image-upload.php";

require_admin_login();

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$action = $_POST["action"] ?? "add";

	if ($action === "add") {
		$title = trim($_POST["title"] ?? "");
		$caption = trim($_POST["caption"] ?? "");
		$displayOrder = (int)($_POST["display_order"] ?? 0);
		$isVisible = isset($_POST["is_visible"]);

		$files = isset($_FILES["photo"]) ? normalize_uploaded_files($_FILES["photo"]) : [];
		$validFiles = array_values(array_filter($files, static function (array $file): bool {
			return ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
		}));

		if (!$validFiles) {
			$errors[] = "At least one gallery image is required.";
		}

		$uploadedPaths = [];
		$images = [];
		if (!$errors) {
			foreach ($validFiles as $index => $file) {
				$imagePath = upload_image_file(
					$file,
					__DIR__ . "/../source_img/products/gallery",
					"source_img/products/gallery",
					"gallery",
					$errors
				);
				if ($imagePath === null) {
					break;
				}
				$uploadedPaths[] = $imagePath;
				$defaultTitle = humanize_uploaded_filename((string)($file["name"] ?? ""));
				$images[] = [
					"image_path" => $imagePath,
					"alt_text" => $defaultTitle,
					"display_order" => $index,
				];
				if ($title === "" && $index === 0) {
					$title = $defaultTitle;
				}
			}
		}

		if (!$errors && !add_product_gallery_post($title !== "" ? $title : "Gallery post", $caption, $displayOrder, $isVisible, $images)) {
			$errors[] = "Failed to save gallery post.";
		}

		if ($errors && $uploadedPaths) {
			foreach ($uploadedPaths as $uploadedPath) {
				if (count_product_gallery_images_by_path($uploadedPath) === 0) {
					delete_uploaded_image_file($uploadedPath);
				}
			}
		}

		if (!$errors) {
			$success = count($images) === 1
				? "Gallery post added successfully."
				: "Gallery post added with " . count($images) . " images.";
		}
	} elseif ($action === "delete") {
		$id = (int)($_POST["id"] ?? 0);
		$post = $id > 0 ? get_product_gallery_post($id) : null;
		if (!$post || !delete_product_gallery_post($id)) {
			$errors[] = "Failed to delete gallery post.";
		} else {
			foreach (($post["images"] ?? []) as $image) {
				if (!empty($image["image_path"]) && count_product_gallery_images_by_path((string)$image["image_path"]) === 0) {
					delete_uploaded_image_file((string)$image["image_path"]);
				}
			}
			$success = "Gallery post deleted.";
		}
	} elseif ($action === "toggle_visibility") {
		$id = (int)($_POST["id"] ?? 0);
		$isVisible = (int)($_POST["is_visible"] ?? 0) === 1;
		if ($id <= 0 || !set_product_gallery_post_visibility($id, $isVisible)) {
			$errors[] = "Failed to update visibility.";
		} else {
			$success = $isVisible ? "Gallery post is now visible." : "Gallery post hidden from the public page.";
		}
	} elseif ($action === "update_order") {
		$id = (int)($_POST["id"] ?? 0);
		$displayOrder = (int)($_POST["display_order"] ?? 0);
		if ($id <= 0 || !update_product_gallery_post_order($id, $displayOrder)) {
			$errors[] = "Failed to update display order.";
		} else {
			$success = "Display order updated.";
		}
	}
}

$galleryPosts = get_product_gallery_posts(true);
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Gallery · Ace-mtech Admin</title>
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
		.formGrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
		.formGrid label{display:block;font-weight:600;font-size:14px;color:#0f172a;margin-bottom:8px}
		.formGrid input,.formGrid textarea{width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:14px;transition:all 0.2s}
		.formGrid textarea{min-height:120px;resize:vertical}
		.formGrid input:focus,.formGrid textarea:focus{outline:none;border-color:#16a34a;box-shadow:0 0 0 3px rgba(22,163,74,0.1)}
		.formHint{margin:8px 0 0;font-size:12px;color:#64748b;line-height:1.5}
		.adminBtnPrimary{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;text-decoration:none;border-radius:10px;font-weight:600;font-size:14px;transition:all 0.3s;box-shadow:0 4px 12px rgba(22,163,74,0.25);border:none;cursor:pointer;margin-top:16px}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(22,163,74,0.35)}
		.adminBtnSecondary{display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:#fff;color:#0f172a;text-decoration:none;border:1.5px solid #e2e8f0;border-radius:10px;font-weight:600;font-size:13px;transition:all 0.2s}
		.adminBtnSecondary:hover{background:#f8fafc;border-color:#16a34a}
		.adminBtnDanger{display:inline-flex;align-items:center;padding:10px 20px;background:#b91c1c;color:#fff;border:none;border-radius:10px;font-weight:600;font-size:13px;cursor:pointer;transition:all 0.2s}
		.adminBtnDanger:hover{background:#991b1b}
		.alert{padding:14px 18px;border-radius:12px;margin-bottom:24px;font-size:14px;line-height:1.6}
		.alert.error{background:#fee2e2;border:1.5px solid #fca5a5;color:#991b1b}
		.alert.success{background:#dcfce7;border:1.5px solid #86efac;color:#166534}
		.previewGrid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px}
		.previewCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;text-align:left;transition:all 0.2s}
		.previewCard:hover{box-shadow:0 8px 24px rgba(0,0,0,0.1);transform:translateY(-2px);border-color:#16a34a}
		.previewMedia{position:relative;margin-bottom:12px}
		.previewCard img{width:100%;height:170px;object-fit:cover;border-radius:8px;display:block}
		.previewCount{position:absolute;right:10px;bottom:10px;background:rgba(0,0,0,0.8);color:#fff;padding:6px 10px;border-radius:999px;font-size:12px;font-weight:700}
		.previewTitle{font-weight:700;font-size:15px;color:#0f172a;margin-bottom:8px}
		.previewDesc{color:#64748b;font-size:13px;line-height:1.6;margin-bottom:12px}
		.previewMeta{display:flex;gap:8px;flex-wrap:wrap;margin:12px 0}
		.statusBadge{display:inline-flex;align-items:center;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700}
		.statusBadge.visible{background:#dcfce7;color:#166534}
		.statusBadge.hidden{background:#e2e8f0;color:#334155}
		.previewActions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center}
		.previewActions a,.previewActions button{font-size:13px}
		.inlineForm{display:inline-flex;gap:8px;align-items:center;margin:0}
		.inlineForm input[type="number"]{width:84px;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;background:#fff;font-size:13px}
		.inlineForm button{border:none;cursor:pointer}
		.checkboxRow{display:flex;align-items:center;gap:10px;margin-top:8px;font-size:14px;color:#0f172a}
		.checkboxRow input{width:auto;cursor:pointer}
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
				<li><a href="partners.php"><svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg><span>Partners</span></a></li>
				<li><a href="products.php"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg><span>Products</span></a></li>
				<li><a href="gallery.php" class="active"><svg viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg><span>Gallery</span></a></li>
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
				<h1>Product Gallery</h1>
				<p>Upload one post with many images. Each upload batch appears as a single card on the public products page.</p>
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
			<h3>Add Gallery Post</h3>
			<p>Select multiple images and they will be grouped into one public gallery card. The first uploaded image becomes the cover.</p>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="add" />
				<div class="formGrid">
					<div>
						<label for="title">Post Title (Optional)</label>
						<input id="title" name="title" type="text" />
						<p class="formHint">If left blank, the first uploaded file name is used.</p>
					</div>
					<div>
						<label for="photo">Images</label>
						<input id="photo" name="photo[]" type="file" accept="<?php echo htmlspecialchars(supported_image_accept_value()); ?>" multiple required />
						<p class="formHint">Upload as many images as needed for one gallery post.</p>
					</div>
					<div>
						<label for="display_order">Display Order</label>
						<input id="display_order" name="display_order" type="number" value="0" />
					</div>
					<div style="grid-column:1/-1">
						<label for="caption">Caption/Description (Optional)</label>
						<textarea id="caption" name="caption" placeholder="Short description shown below the gallery card"></textarea>
					</div>
				<label class="checkboxRow"><input name="is_visible" type="checkbox" checked /> Visible on public site</label>
				<button class="adminBtnPrimary" type="submit"><span>Create Gallery Post</span><span>→</span></button>
			</form>
		</div>

		<div class="adminCard">
			<h3>Current Gallery Posts</h3>
			<?php if (!$galleryPosts): ?>
				<p>No gallery posts added yet.</p>
			<?php else: ?>
				<div class="previewGrid">
					<?php foreach ($galleryPosts as $post): ?>
						<div class="previewCard">
							<div class="previewMedia">
								<?php if (!empty($post["cover_image_path"])): ?>
									<img src="../<?php echo htmlspecialchars($post["cover_image_path"]); ?>" alt="<?php echo htmlspecialchars($post["title"]); ?>" />
								<?php endif; ?>
								<span class="previewCount"><?php echo (int)($post["image_count"] ?? 0); ?> image<?php echo (int)($post["image_count"] ?? 0) === 1 ? "" : "s"; ?></span>
							</div>
							<div class="previewTitle"><?php echo htmlspecialchars($post["title"]); ?></div>
							<?php if (!empty($post["caption"])): ?>
								<div class="previewDesc"><?php echo htmlspecialchars($post["caption"]); ?></div>
							<?php endif; ?>
							<div class="previewMeta">
								<span class="statusBadge <?php echo (int)$post["is_visible"] === 1 ? "visible" : "hidden"; ?>"><?php echo (int)$post["is_visible"] === 1 ? "Visible" : "Hidden"; ?></span>
								<span class="statusBadge hidden">Order: <?php echo (int)$post["display_order"]; ?></span>
							</div>
							<div class="previewActions">
								<a class="adminBtnSecondary" href="edit-gallery.php?id=<?php echo (int)$post["id"]; ?>">Edit post</a>
								<form class="inlineForm" method="post">
									<input type="hidden" name="action" value="toggle_visibility" />
									<input type="hidden" name="id" value="<?php echo (int)$post["id"]; ?>" />
									<input type="hidden" name="is_visible" value="<?php echo (int)$post["is_visible"] === 1 ? 0 : 1; ?>" />
									<button class="adminBtnSecondary" type="submit"><?php echo (int)$post["is_visible"] === 1 ? "Hide" : "Show"; ?></button>
								</form>
							</div>
							<div class="previewActions">
								<form class="inlineForm" method="post">
									<input type="hidden" name="action" value="update_order" />
									<input type="hidden" name="id" value="<?php echo (int)$post["id"]; ?>" />
									<input name="display_order" type="number" value="<?php echo (int)$post["display_order"]; ?>" />
									<button class="adminBtnSecondary" type="submit">Save order</button>
								</form>
							</div>
							<div class="previewActions">
								<form class="inlineForm" method="post" onsubmit="return confirm('Delete this gallery post and all its images?');">
									<input type="hidden" name="action" value="delete" />
									<input type="hidden" name="id" value="<?php echo (int)$post["id"]; ?>" />
									<button class="adminBtnDanger" type="submit"><span>Delete</span></button>
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
