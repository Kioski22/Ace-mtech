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
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Edit Gallery Post</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		*{margin:0;padding:0;box-sizing:border-box}
		body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);min-height:100vh;color:#1e293b;font-size:16px;line-height:1.6}
		.adminLayout{display:flex;min-height:100vh}
		.adminSidebar{width:260px;background:linear-gradient(180deg,#1e293b 0%,#0f172a 100%);color:#fff;padding:32px 0;position:fixed;height:100vh;overflow-y:auto;border-right:1px solid rgba(255,255,255,0.1);z-index:100}
		.adminSidebar h2{padding:0 24px;font-size:24px;margin-bottom:32px;font-weight:700;letter-spacing:-0.5px}
		.adminNav{display:flex;flex-direction:column;gap:4px;padding:0 16px}
		.adminNav a{color:#cbd5e1;text-decoration:none;padding:12px 16px;border-radius:12px;transition:all .2s;display:flex;align-items:center;gap:12px;font-size:15px;font-weight:500;border-left:3px solid transparent}
		.adminNav a:hover{background:rgba(255,255,255,0.1);color:#fff}
		.adminNav a.active{background:rgba(34,197,94,0.15);color:#4ade80;border-left-color:#22c55e;box-shadow:0 0 20px rgba(34,197,94,0.3)}
		.adminNav svg{width:20px;height:20px;flex-shrink:0}
		.adminMain{margin-left:260px;flex:1;padding:40px;min-height:100vh}
		.adminHeader{margin-bottom:32px}
		.adminHeader h1{font-size:36px;font-weight:700;color:#1e293b;margin-bottom:8px;letter-spacing:-1px}
		.adminHeader p{color:#64748b;font-size:16px}
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
		@media(max-width:768px){.adminSidebar{width:70px}.adminSidebar h2,.adminNav span{display:none}.adminMain{margin-left:70px;padding:24px}}
	</style>
</head>
<body>
	<div class="adminLayout">
		<aside class="adminSidebar">
			<h2>Admin Panel</h2>
			<nav class="adminNav" aria-label="Admin navigation">
				<a href="index.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
					<span>Dashboard</span>
				</a>
				<a href="clients.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
					<span>Clients</span>
				</a>
				<a href="partners.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
					<span>Partners</span>
				</a>
				<a href="products.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
					<span>Products</span>
				</a>
				<a href="gallery.php" class="active">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
					<span>Gallery</span>
				</a>
				<a href="social-links.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
					<span>Social Links</span>
				</a>
				<a href="../admin/messages/admin.html">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
					<span>Messages</span>
				</a>
				<a href="../index.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
					<span>View Site</span>
				</a>
				<a href="logout.php">
					<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
					<span>Logout</span>
				</a>
			</nav>
		</aside>
		<main class="adminMain">
			<div class="adminHeader">
				<h1>Edit Gallery Post</h1>
				<p>Manage the post details and the images inside this grouped gallery entry.</p>
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
