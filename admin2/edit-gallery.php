<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";
require_once __DIR__ . "/../includes/image-upload.php";

require_admin_login();

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
	header("Location: gallery.php");
	exit;
}

$post = get_product_gallery_post($id);
if (!$post) {
	header("Location: gallery.php");
	exit;
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$action = $_POST["action"] ?? "update_post";

	if ($action === "update_post") {
		$title = trim($_POST["title"] ?? "");
		$caption = trim($_POST["caption"] ?? "");
		$displayOrder = (int)($_POST["display_order"] ?? 0);
		$isVisible = isset($_POST["is_visible"]);

		if ($title === "") {
			$errors[] = "Gallery title is required.";
		}

		if (!$errors) {
			if (update_product_gallery_post($id, $title, $caption, $displayOrder, $isVisible)) {
				$success = "Gallery post updated successfully.";
			} else {
				$errors[] = "Failed to update gallery post.";
			}
		}
	} elseif ($action === "add_images") {
		$files = isset($_FILES["photo"]) ? normalize_uploaded_files($_FILES["photo"]) : [];
		$validFiles = array_values(array_filter($files, static function (array $file): bool {
			return ($file["error"] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
		}));

		if (!$validFiles) {
			$errors[] = "Select one or more images to add.";
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
				$images[] = [
					"image_path" => $imagePath,
					"alt_text" => humanize_uploaded_filename((string)($file["name"] ?? "")),
					"display_order" => $index,
				];
			}
		}

		if (!$errors && !add_images_to_product_gallery_post($id, $images)) {
			$errors[] = "Failed to add images to this gallery post.";
		}

		if ($errors && $uploadedPaths) {
			foreach ($uploadedPaths as $uploadedPath) {
				if (count_product_gallery_images_by_path($uploadedPath) === 0) {
					delete_uploaded_image_file($uploadedPath);
				}
			}
		}

		if (!$errors) {
			$success = count($images) . " image" . (count($images) === 1 ? "" : "s") . " added to this post.";
		}
	} elseif ($action === "delete_image") {
		$imageId = (int)($_POST["image_id"] ?? 0);
		$image = $imageId > 0 ? get_product_gallery_image($imageId) : null;
		if (!$image || (int)$image["gallery_post_id"] !== $id) {
			$errors[] = "Image not found.";
		} elseif (count($post["images"] ?? []) <= 1) {
			$errors[] = "A gallery post must keep at least one image. Delete the whole post instead if needed.";
		} elseif (!delete_product_gallery_image($imageId)) {
			$errors[] = "Failed to delete image.";
		} else {
			if (!empty($image["image_path"]) && count_product_gallery_images_by_path((string)$image["image_path"], $imageId) === 0) {
				delete_uploaded_image_file((string)$image["image_path"]);
			}
			$success = "Image removed from the gallery post.";
		}
	} elseif ($action === "update_alt") {
		$imageId = (int)($_POST["image_id"] ?? 0);
		$altText = trim($_POST["alt_text"] ?? "");
		$image = $imageId > 0 ? get_product_gallery_image($imageId) : null;
		if (!$image || (int)$image["gallery_post_id"] !== $id) {
			$errors[] = "Image not found.";
		} elseif (!update_product_gallery_image_alt_text($imageId, $altText)) {
			$errors[] = "Failed to update alt text.";
		} else {
			$success = "Alt text updated.";
		}
	}

	$post = get_product_gallery_post($id) ?? $post;
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" /><link rel="icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" type="image/svg+xml" />
<link rel="apple-touch-icon" href="../<?php echo htmlspecialchars(get_site_logo_path()); ?>" />
	<title>Edit Gallery Post</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		*{margin:0;padding:0;box-sizing:border-box}
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#f8fafc;min-height:100vh;color:#1e293b;font-size:16px;line-height:1.6}
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
		.adminMain{margin-left:260px;flex:1;padding:40px;min-height:100vh}
		.adminHeader{margin-bottom:32px}
		.adminHeader h1{font-size:36px;font-weight:700;color:#1e293b;margin-bottom:8px;letter-spacing:-1px}
		.adminHeader p{color:#64748b;font-size:16px}
		.frontendIndicator{margin-top:14px;padding:12px 14px;background:#ecfdf5;border:1.5px solid #86efac;border-radius:10px;color:#14532d;font-size:13px;line-height:1.6}
		.frontendIndicator strong{display:block;font-size:12px;letter-spacing:0.3px;text-transform:uppercase;color:#166534;margin-bottom:4px}
		.frontendIndicator a{color:#166534;text-decoration:underline;font-weight:700}
		.frontendIndicator a:hover{color:#14532d}
		.adminCard{background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;padding:28px;margin-bottom:24px;transition:all .3s;box-shadow:0 1px 3px rgba(0,0,0,0.05)}
		.adminCard:hover{border-color:#cbd5e1;box-shadow:0 4px 12px rgba(0,0,0,0.08);transform:translateY(-2px)}
		.adminCard h3{font-size:20px;font-weight:600;color:#1e293b;margin-bottom:20px}
		.formRow{margin-bottom:20px}
		.formRow label{display:block;font-weight:600;margin-bottom:8px;color:#334155;font-size:14px;letter-spacing:0.2px}
		.formRow input,.formRow textarea{width:100%;padding:12px 16px;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-family:inherit;font-size:15px;transition:all .2s}
		.formRow input:focus,.formRow textarea:focus{outline:none;border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,0.1)}
		.formRow textarea{min-height:120px;resize:vertical}
		.checkboxRow{display:flex;align-items:center;gap:10px}
		.checkboxRow input{width:auto;margin:0}
		.checkboxRow label{margin:0}
		.adminBtnPrimary{background:linear-gradient(135deg,#16a34a 0%,#059669 100%);color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;transition:all .3s;box-shadow:0 4px 12px rgba(22,163,74,0.3);display:inline-flex;align-items:center;gap:8px}
		.adminBtnPrimary:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(22,163,74,0.4)}
		.adminBtnSecondary{background:#fff;color:#1e293b;border:1.5px solid #e2e8f0;padding:12px 24px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;transition:all .3s;display:inline-flex;align-items:center;gap:8px}
		.adminBtnSecondary:hover{background:#f8fafc;border-color:#cbd5e1;transform:translateY(-1px)}
		.adminBtnDanger{background:linear-gradient(135deg,#dc2626 0%,#b91c1c 100%);color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;font-size:15px;cursor:pointer;transition:all .3s;box-shadow:0 4px 12px rgba(220,38,38,0.3);display:inline-flex;align-items:center;gap:8px}
		.adminBtnDanger:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(220,38,38,0.4)}
		.alert{padding:16px 20px;border-radius:12px;margin-bottom:24px;font-size:15px}
		.alert.error{background:#fee2e2;border:1.5px solid #fecaca;color:#991b1b}
		.alert.success{background:#d1fae5;border:1.5px solid #a7f3d0;color:#065f46}
		.previewGrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:20px;margin-top:20px}
		.previewCard{border:1.5px solid #e2e8f0;background:#fff;border-radius:12px;padding:16px;transition:all .3s}
		.previewCard:hover{border-color:#cbd5e1;box-shadow:0 4px 12px rgba(0,0,0,0.08)}
		.previewCard img{width:100%;height:180px;object-fit:cover;border-radius:8px;display:block;margin-bottom:12px}
		.inlineForm{display:grid;gap:12px}
		.inlineActions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:10px}
		@media(max-width:768px){.adminSidebar{width:70px}.adminSidebar .adminBrand h2,.adminSidebar .adminBrand p,.adminNav span{display:none}.adminNav a{justify-content:center;padding:14px}.adminMain{margin-left:70px;max-width:calc(100% - 70px);padding:24px 16px}}
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
			<div class="adminHeader">
				<h1>Edit Gallery Post</h1>
				<p>Manage the post details and the images inside this grouped gallery entry.</p>
				<div class="frontendIndicator">
					<strong>Where this appears on your website</strong>
					This gallery post appears in <a href="../products.php" target="_blank" rel="noopener noreferrer">Products</a> when marked visible.
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
			<h3 style="margin-top:0">Post details</h3>
			<form method="post">
				<input type="hidden" name="action" value="update_post" />
				<div class="formRow">
					<label for="title">Title</label>
					<input id="title" name="title" type="text" value="<?php echo htmlspecialchars($post["title"]); ?>" required />
				</div>
				<div class="formRow">
					<label for="caption">Caption / description</label>
					<textarea id="caption" name="caption"><?php echo htmlspecialchars($post["caption"] ?? ""); ?></textarea>
				</div>
				<div class="formRow">
					<label for="display_order">Display order</label>
					<input id="display_order" name="display_order" type="number" value="<?php echo (int)$post["display_order"]; ?>" />
				</div>
				<div class="formRow checkboxRow">
					<input id="is_visible" name="is_visible" type="checkbox" <?php echo (int)$post["is_visible"] === 1 ? "checked" : ""; ?> />
					<label for="is_visible" style="margin:0">Visible on public site</label>
				</div>
				<button class="adminBtnPrimary" type="submit"><span>Save post</span><span aria-hidden="true">→</span></button>
			</form>
		</div>

		<div class="adminCard" style="margin-bottom:20px">
			<h3 style="margin-top:0">Add more images</h3>
			<form method="post" enctype="multipart/form-data">
				<input type="hidden" name="action" value="add_images" />
				<div class="formRow">
					<label for="photo">Images</label>
					<input id="photo" name="photo[]" type="file" accept="<?php echo htmlspecialchars(supported_image_accept_value()); ?>" multiple required />
				</div>
				<button class="adminBtnPrimary" type="submit"><span>Add images</span><span aria-hidden="true">→</span></button>
			</form>
		</div>

		<div class="adminCard">
			<h3 style="margin-top:0">Images in this post</h3>
			<?php if (empty($post["images"])): ?>
				<p class="sectionDesc">No images in this post.</p>
			<?php else: ?>
				<div class="previewGrid">
					<?php foreach ($post["images"] as $image): ?>
						<div class="previewCard">
							<img src="../<?php echo htmlspecialchars($image["image_path"]); ?>" alt="<?php echo htmlspecialchars($image["alt_text"] ?: $post["title"]); ?>" />
							<form class="inlineForm" method="post">
								<input type="hidden" name="action" value="update_alt" />
								<input type="hidden" name="image_id" value="<?php echo (int)$image["id"]; ?>" />
								<label for="alt_<?php echo (int)$image["id"]; ?>">Alt text</label>
								<input id="alt_<?php echo (int)$image["id"]; ?>" name="alt_text" type="text" value="<?php echo htmlspecialchars($image["alt_text"] ?? ""); ?>" />
								<div class="inlineActions">
									<button class="adminBtnSecondary" type="submit">Save alt text</button>
								</div>
							</form>
							<form method="post" onsubmit="return confirm('Remove this image from the post?');" style="margin-top:10px">
								<input type="hidden" name="action" value="delete_image" />
								<input type="hidden" name="image_id" value="<?php echo (int)$image["id"]; ?>" />
								<button class="adminBtnDanger" type="submit"><span>Remove image</span></button>
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
