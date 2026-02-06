<?php
require_once __DIR__ . "/../includes/admin-auth.php";
require_once __DIR__ . "/../includes/repository.php";

require_admin_login();

$errors = [];
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$name = trim($_POST["name"] ?? "");
	$description = trim($_POST["description"] ?? "");

	if ($name === "") {
		$errors[] = "Product name is required.";
	}

	if (!isset($_FILES["photo"]) || $_FILES["photo"]["error"] !== UPLOAD_ERR_OK) {
		$errors[] = "Product image is required.";
	}

	if (!$errors) {
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
				$relativePath = "source_img/products/" . $filename;
				if (add_product($name, $description, $relativePath)) {
					$success = "Product added successfully.";
				} else {
					$errors[] = "Failed to save product list.";
				}
			} else {
				$errors[] = "Failed to upload image.";
			}
		}
	}
}

$products = get_products();
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>Admin | Products</title>
	<link rel="stylesheet" href="../styles.css" />
	<style>
		body{background:var(--bg-soft)}
		.adminShell{max-width:1100px;margin:0 auto;padding:32px}
		.adminNav{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}
		.adminNav a{text-decoration:none}
		.adminCard{border:1px solid var(--stroke);background:var(--panel);border-radius:16px;padding:20px;box-shadow:var(--shadow-sm)}
		.formGrid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
		.formGrid label{display:block;font-weight:600;margin-bottom:6px}
		.formGrid input,.formGrid textarea{width:100%;padding:10px 12px;border:1px solid var(--stroke);border-radius:12px;background:#fff;font-family:var(--sans)}
		.formGrid textarea{min-height:120px;resize:vertical}
		.alert{padding:12px 14px;border-radius:12px;margin-bottom:16px}
		.alert.error{background:#fee2e2;border:1px solid #fecaca;color:#991b1b}
		.alert.success{background:#dcfce7;border:1px solid #bbf7d0;color:#166534}
		.previewGrid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
		.previewCard{border:1px solid var(--stroke);background:var(--panel);border-radius:14px;padding:14px;text-align:left}
		.previewCard img{width:100%;height:140px;object-fit:cover;border-radius:10px;display:block;margin-bottom:10px}
		.previewTitle{font-weight:700;margin-bottom:6px}
		.previewDesc{color:var(--muted);font-size:13px;line-height:1.6}
		.previewActions{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap}
		.previewActions a{font-size:13px;text-decoration:none}
		@media (max-width:800px){.formGrid{grid-template-columns:1fr}}
	</style>
</head>
<body>
	<div class="adminShell">
		<h1 style="margin-bottom:10px">Products</h1>
		<p class="sectionDesc" style="margin-bottom:22px">Add products shown on the public products page.</p>

		<nav class="adminNav" aria-label="Admin navigation">
			<a class="ghost" href="index.php">Dashboard</a>
			<a class="ghost" href="clients.php">Client list</a>
			<a class="ghost" href="partners.php">Partners list</a>
			<a class="ghost" href="products.php">Products</a>
			<a class="ghost" href="../index.php">View site</a>
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

		<div class="adminCard" style="margin-bottom:20px">
			<h3 style="margin-top:0">Add product</h3>
			<form method="post" enctype="multipart/form-data">
				<div class="formGrid">
					<div>
						<label for="name">Product name</label>
						<input id="name" name="name" type="text" required />
					</div>
					<div>
						<label for="photo">Product image</label>
						<input id="photo" name="photo" type="file" accept="image/*" required />
					</div>
					<div style="grid-column:1/-1">
						<label for="description">Description (optional)</label>
						<textarea id="description" name="description" placeholder="Short product description"></textarea>
					</div>
				</div>
				<div style="margin-top:16px">
					<button class="cta" type="submit"><span>Add product</span><span aria-hidden="true">→</span></button>
				</div>
			</form>
		</div>

		<div class="adminCard">
			<h3 style="margin-top:0">Current products</h3>
			<?php if (!$products): ?>
				<p class="sectionDesc">No products added yet.</p>
			<?php else: ?>
				<div class="previewGrid">
					<?php foreach ($products as $product): ?>
						<div class="previewCard">
							<?php if (!empty($product["image_path"])): ?>
								<img src="../<?php echo htmlspecialchars($product["image_path"]); ?>" alt="<?php echo htmlspecialchars($product["name"]); ?>" />
							<?php endif; ?>
							<div class="previewTitle"><?php echo htmlspecialchars($product["name"]); ?></div>
							<?php if (!empty($product["description"])): ?>
								<div class="previewDesc"><?php echo htmlspecialchars($product["description"]); ?></div>
							<?php endif; ?>
							<div class="previewActions">
								<a class="ghost" href="edit-product.php?id=<?php echo (int)$product["id"]; ?>">Edit</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</body>
</html>
