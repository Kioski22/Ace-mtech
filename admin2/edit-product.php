<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

require_admin_login();

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
	header("Location: products.php");
	exit;
}

$product = get_product($id);
if (!$product) {
	header("Location: products.php");
	exit;
}

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$name = trim($_POST["name"] ?? "");
	$description = trim($_POST["description"] ?? "");

	if ($name === "") {
		$errors[] = "Product name is required.";
	}

	$imagePath = null;
	if (isset($_FILES["photo"]) && $_FILES["photo"]["error"] === UPLOAD_ERR_OK) {
		$uploadDir = __DIR__ . "/../source_img/products";
		if (!is_dir($uploadDir)) {
			mkdir($uploadDir, 0775, true);
		}

		$file = $_FILES["photo"];
		$ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
		$allowed = ["jpg", "jpeg", "png", "webp"];
		if (!in_array($ext, $allowed, true)) {
			$errors[] = "Image must be a JPG, PNG, or WEBP file.";
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = $finfo ? finfo_file($finfo, $file["tmp_name"]) : "";
		if ($finfo) {
			finfo_close($finfo);
		}
		$allowedMime = ["image/jpeg", "image/png", "image/webp"];
		if ($mime && !in_array($mime, $allowedMime, true)) {
			$errors[] = "Image must be a valid file.";
		}

		if (!$errors) {
			$filename = "product-" . date("YmdHis") . "-" . bin2hex(random_bytes(4)) . "." . $ext;
			$targetPath = $uploadDir . "/" . $filename;
			if (move_uploaded_file($file["tmp_name"], $targetPath)) {
				$imagePath = "source_img/products/" . $filename;
			} else {
				$errors[] = "Failed to upload image.";
			}
		}
	}

	if (!$errors) {
		if (update_product($id, $name, $description, $imagePath)) {
			$success = "Product updated successfully.";
			$product = get_product($id) ?? $product;
		} else {
			$errors[] = "Failed to update product.";
		}
	}
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Edit Product</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		body{background:var(--bg-soft)}
		.adminShell{max-width:900px;margin:0 auto;padding:32px}
		.adminNav{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}
		.adminNav a{text-decoration:none}
		.adminCard{border:1px solid var(--stroke);background:var(--panel);border-radius:16px;padding:20px;box-shadow:var(--shadow-sm)}
		.formRow{margin-bottom:14px}
		.formRow label{display:block;font-weight:600;margin-bottom:6px}
		.formRow input,.formRow textarea{width:100%;padding:10px 12px;border:1px solid var(--stroke);border-radius:12px;background:#fff;font-family:var(--sans)}
		.formRow textarea{min-height:120px;resize:vertical}
		.alert{padding:12px 14px;border-radius:12px;margin-bottom:16px}
		.alert.error{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}
		.alert.success{background:#dcfce7;border:1px solid #bbf7d0;color:#166534}
		.previewImg{width:100%;max-width:320px;height:180px;object-fit:cover;border-radius:12px;display:block;margin-top:8px}
	</style>
</head>
<body>
	<div class="adminShell">
		<h1 style="margin-bottom:10px">Edit product</h1>
		<p class="sectionDesc" style="margin-bottom:22px">Update the product name, description, or image.</p>

		<nav class="adminNav" aria-label="Admin navigation">
			<a class="ghost" href="products.php">Back to products</a>
			<a class="ghost" href="index.php">Dashboard</a>
			<a class="ghost" href="logout.php">Log out</a>
		</nav>

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
			<form method="post" enctype="multipart/form-data">
				<div class="formRow">
					<label for="name">Product name</label>
					<input id="name" name="name" type="text" value="<?php echo htmlspecialchars($product["name"]); ?>" required />
				</div>
				<div class="formRow">
					<label for="description">Description</label>
					<textarea id="description" name="description"><?php echo htmlspecialchars($product["description"] ?? ""); ?></textarea>
				</div>
				<div class="formRow">
					<label for="photo">Replace image (optional)</label>
					<input id="photo" name="photo" type="file" accept="image/*" />
					<?php if (!empty($product["image_path"])): ?>
						<img class="previewImg" src="../<?php echo htmlspecialchars($product["image_path"]); ?>" alt="Current product image" />
					<?php endif; ?>
				</div>
				<button class="cta" type="submit"><span>Save changes</span><span aria-hidden="true">→</span></button>
			</form>
		</div>
	</div>
</body>
</html>
