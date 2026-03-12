<?php

declare(strict_types=1);

require_once __DIR__ . "/db.php";

function table_exists(PDO $pdo, string $tableName): bool
{
	$stmt = $pdo->query("SHOW TABLES LIKE " . $pdo->quote($tableName));
	return (bool)$stmt->fetchColumn();
}

function sync_product_gallery_post_cover_with_pdo(PDO $pdo, int $postId): void
{
	$stmt = $pdo->prepare(
		"SELECT image_path FROM product_gallery_images WHERE gallery_post_id = :post_id ORDER BY display_order ASC, id ASC LIMIT 1"
	);
	$stmt->execute([":post_id" => $postId]);
	$coverImagePath = (string)($stmt->fetchColumn() ?: "");

	$update = $pdo->prepare("UPDATE product_gallery_posts SET cover_image_path = :cover_image_path WHERE id = :id");
	$update->execute([
		":cover_image_path" => $coverImagePath,
		":id" => $postId,
	]);
}

function migrate_legacy_product_gallery(PDO $pdo): void
{
	if (!table_exists($pdo, "product_gallery")) {
		return;
	}

	$totalPosts = (int)($pdo->query("SELECT COUNT(*) FROM product_gallery_posts")->fetchColumn() ?: 0);
	if ($totalPosts > 0) {
		return;
	}

	$legacyRows = $pdo->query(
		"SELECT id, title, caption, alt_text, image_path, display_order, is_visible, created_at FROM product_gallery ORDER BY display_order ASC, created_at DESC"
	)->fetchAll();

	if (!$legacyRows) {
		return;
	}

	$insertPost = $pdo->prepare(
		"INSERT INTO product_gallery_posts (title, caption, cover_image_path, display_order, is_visible, created_at) VALUES (:title, :caption, :cover_image_path, :display_order, :is_visible, :created_at)"
	);
	$insertImage = $pdo->prepare(
		"INSERT INTO product_gallery_images (gallery_post_id, image_path, alt_text, display_order, created_at) VALUES (:gallery_post_id, :image_path, :alt_text, :display_order, :created_at)"
	);

	$pdo->beginTransaction();
	try {
		foreach ($legacyRows as $legacyRow) {
			$insertPost->execute([
				":title" => $legacyRow["title"],
				":caption" => $legacyRow["caption"],
				":cover_image_path" => $legacyRow["image_path"],
				":display_order" => $legacyRow["display_order"],
				":is_visible" => $legacyRow["is_visible"],
				":created_at" => $legacyRow["created_at"],
			]);

			$postId = (int)$pdo->lastInsertId();
			$insertImage->execute([
				":gallery_post_id" => $postId,
				":image_path" => $legacyRow["image_path"],
				":alt_text" => $legacyRow["alt_text"],
				":display_order" => 0,
				":created_at" => $legacyRow["created_at"],
			]);
		}
		$pdo->commit();
	} catch (Throwable $e) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		throw $e;
	}
}

function social_platform_catalog(): array
{
	return [
		"facebook" => [
			"name" => "Facebook",
			"icon" => "F",
			"description" => "Updates, photos, and announcements from the field.",
			"button_label" => "Visit Facebook →",
			"default_url" => "https://facebook.com",
		],
		"instagram" => [
			"name" => "Instagram",
			"icon" => "I",
			"description" => "Project highlights, installation details, and behind-the-scenes.",
			"button_label" => "Visit Instagram →",
			"default_url" => "https://instagram.com",
		],
		"linkedin" => [
			"name" => "LinkedIn",
			"icon" => "L",
			"description" => "Company news, partnerships, and career updates.",
			"button_label" => "Visit LinkedIn →",
			"default_url" => "https://linkedin.com",
		],
	];
}

function seed_social_links_if_missing(PDO $pdo): void
{
	$catalog = social_platform_catalog();
	$findStmt = $pdo->prepare("SELECT COUNT(*) FROM social_links WHERE platform_key = :platform_key");
	$insertStmt = $pdo->prepare(
		"INSERT INTO social_links (platform_key, url, display_order, is_visible, created_at) VALUES (:platform_key, :url, :display_order, :is_visible, NOW())"
	);

	$displayOrder = 0;
	foreach ($catalog as $platformKey => $meta) {
		$findStmt->execute([":platform_key" => $platformKey]);
		$exists = (int)($findStmt->fetchColumn() ?: 0) > 0;
		if (!$exists) {
			$insertStmt->execute([
				":platform_key" => $platformKey,
				":url" => $meta["default_url"],
				":display_order" => $displayOrder,
				":is_visible" => 1,
			]);
		}
		$displayOrder++;
	}
}

function attach_product_gallery_images(array $posts): array
{
	if (!$posts) {
		return [];
	}

	$ids = array_map(static fn (array $post): int => (int)$post["id"], $posts);
	$placeholders = implode(",", array_fill(0, count($ids), "?"));

	$pdo = db();
	$stmt = $pdo->prepare(
		"SELECT id, gallery_post_id, image_path, alt_text, display_order, created_at FROM product_gallery_images WHERE gallery_post_id IN ($placeholders) ORDER BY display_order ASC, id ASC"
	);
	$stmt->execute($ids);
	$images = $stmt->fetchAll();

	$byPostId = [];
	foreach ($images as $image) {
		$postId = (int)$image["gallery_post_id"];
		$byPostId[$postId][] = $image;
	}

	foreach ($posts as &$post) {
		$postId = (int)$post["id"];
		$post["images"] = $byPostId[$postId] ?? [];
		$post["image_count"] = count($post["images"]);
	}
	unset($post);

	return $posts;
}

function ensure_schema(): void
{
	static $ready = false;
	if ($ready) {
		return;
	}

	$pdo = db();
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS admins (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tusername VARCHAR(64) NOT NULL UNIQUE,\n"
		. "\tpassword_hash VARCHAR(255) NOT NULL,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS clients (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tname VARCHAR(150) NOT NULL,\n"
		. "\timage_path VARCHAR(255) NOT NULL,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS partners (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tname VARCHAR(150) NOT NULL,\n"
		. "\timage_path VARCHAR(255) NOT NULL,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS products (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tname VARCHAR(150) NOT NULL,\n"
		. "\tdescription TEXT NULL,\n"
		. "\timage_path VARCHAR(255) NOT NULL,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS product_gallery (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\ttitle VARCHAR(150) NOT NULL,\n"
		. "\tcaption TEXT NULL,\n"
		. "\talt_text VARCHAR(255) NULL,\n"
		. "\timage_path VARCHAR(255) NOT NULL,\n"
		. "\tdisplay_order INT NOT NULL DEFAULT 0,\n"
		. "\tis_visible TINYINT(1) NOT NULL DEFAULT 1,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS product_gallery_posts (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\ttitle VARCHAR(150) NOT NULL,\n"
		. "\tcaption TEXT NULL,\n"
		. "\tcover_image_path VARCHAR(255) NOT NULL,\n"
		. "\tdisplay_order INT NOT NULL DEFAULT 0,\n"
		. "\tis_visible TINYINT(1) NOT NULL DEFAULT 1,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS product_gallery_images (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tgallery_post_id INT UNSIGNED NOT NULL,\n"
		. "\timage_path VARCHAR(255) NOT NULL,\n"
		. "\talt_text VARCHAR(255) NULL,\n"
		. "\tdisplay_order INT NOT NULL DEFAULT 0,\n"
		. "\tcreated_at DATETIME NOT NULL,\n"
		. "\tCONSTRAINT fk_product_gallery_images_post FOREIGN KEY (gallery_post_id) REFERENCES product_gallery_posts(id) ON DELETE CASCADE\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS social_links (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\tplatform_key VARCHAR(32) NOT NULL UNIQUE,\n"
		. "\turl VARCHAR(255) NOT NULL,\n"
		. "\tdisplay_order INT NOT NULL DEFAULT 0,\n"
		. "\tis_visible TINYINT(1) NOT NULL DEFAULT 1,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS news_posts (\n"
		. "\tid INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,\n"
		. "\ttitle VARCHAR(180) NOT NULL,\n"
		. "\tcontent TEXT NOT NULL,\n"
		. "\tauthor_name VARCHAR(120) NOT NULL,\n"
		. "\timage_path VARCHAR(255) NULL,\n"
		. "\tdisplay_order INT NOT NULL DEFAULT 0,\n"
		. "\tis_visible TINYINT(1) NOT NULL DEFAULT 1,\n"
		. "\tcreated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);
	$pdo->exec(
		"CREATE TABLE IF NOT EXISTS site_settings (\n"
		. "\tsetting_key VARCHAR(80) PRIMARY KEY,\n"
		. "\tsetting_value TEXT NOT NULL,\n"
		. "\tupdated_at DATETIME NOT NULL\n"
		. ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
	);

	migrate_legacy_product_gallery($pdo);
	seed_social_links_if_missing($pdo);

	$ready = true;
}

function admin_count(): int
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->query("SELECT COUNT(*) AS total FROM admins");
	$row = $stmt->fetch();
	return (int)($row["total"] ?? 0);
}

function create_admin(string $username, string $password): bool
{
	ensure_schema();
	$pdo = db();
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$stmt = $pdo->prepare("INSERT INTO admins (username, password_hash, created_at) VALUES (:username, :hash, NOW())");
	return $stmt->execute([
		":username" => $username,
		":hash" => $hash,
	]);
}

function find_admin_by_username(string $username): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username LIMIT 1");
	$stmt->execute([":username" => $username]);
	$admin = $stmt->fetch();
	return $admin ?: null;
}

function verify_admin_credentials(string $username, string $password): ?array
{
	ensure_schema();
	$admin = find_admin_by_username($username);
	if (!$admin) {
		return null;
	}
	if (!password_verify($password, $admin["password_hash"] ?? "")) {
		return null;
	}
	return $admin;
}

function get_clients(): array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->query("SELECT id, name, image_path, created_at FROM clients ORDER BY created_at DESC");
	return $stmt->fetchAll();
}

function get_client(int $id): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT id, name, image_path, created_at FROM clients WHERE id = :id LIMIT 1");
	$stmt->execute([":id" => $id]);
	$row = $stmt->fetch();
	return $row ?: null;
}

function add_client(string $name, string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("INSERT INTO clients (name, image_path, created_at) VALUES (:name, :image, NOW())");
	return $stmt->execute([
		":name" => $name,
		":image" => $imagePath,
	]);
}

function update_client(int $id, string $name, ?string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	if ($imagePath) {
		$stmt = $pdo->prepare("UPDATE clients SET name = :name, image_path = :image WHERE id = :id");
		return $stmt->execute([
			":name" => $name,
			":image" => $imagePath,
			":id" => $id,
		]);
	}
	$stmt = $pdo->prepare("UPDATE clients SET name = :name WHERE id = :id");
	return $stmt->execute([
		":name" => $name,
		":id" => $id,
	]);
}

function get_partners(): array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->query("SELECT id, name, image_path, created_at FROM partners ORDER BY created_at DESC");
	return $stmt->fetchAll();
}

function get_partner(int $id): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT id, name, image_path, created_at FROM partners WHERE id = :id LIMIT 1");
	$stmt->execute([":id" => $id]);
	$row = $stmt->fetch();
	return $row ?: null;
}

function add_partner(string $name, string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("INSERT INTO partners (name, image_path, created_at) VALUES (:name, :image, NOW())");
	return $stmt->execute([
		":name" => $name,
		":image" => $imagePath,
	]);
}

function update_partner(int $id, string $name, ?string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	if ($imagePath) {
		$stmt = $pdo->prepare("UPDATE partners SET name = :name, image_path = :image WHERE id = :id");
		return $stmt->execute([
			":name" => $name,
			":image" => $imagePath,
			":id" => $id,
		]);
	}
	$stmt = $pdo->prepare("UPDATE partners SET name = :name WHERE id = :id");
	return $stmt->execute([
		":name" => $name,
		":id" => $id,
	]);
}

function get_products(): array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->query("SELECT id, name, description, image_path, created_at FROM products ORDER BY created_at DESC");
	return $stmt->fetchAll();
}

function get_product(int $id): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT id, name, description, image_path, created_at FROM products WHERE id = :id LIMIT 1");
	$stmt->execute([":id" => $id]);
	$row = $stmt->fetch();
	return $row ?: null;
}

function add_product(string $name, string $description, string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("INSERT INTO products (name, description, image_path, created_at) VALUES (:name, :description, :image, NOW())");
	return $stmt->execute([
		":name" => $name,
		":description" => $description,
		":image" => $imagePath,
	]);
}

function update_product(int $id, string $name, string $description, ?string $imagePath): bool
{
	ensure_schema();
	$pdo = db();
	if ($imagePath) {
		$stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description, image_path = :image WHERE id = :id");
		return $stmt->execute([
			":name" => $name,
			":description" => $description,
			":image" => $imagePath,
			":id" => $id,
		]);
	}
	$stmt = $pdo->prepare("UPDATE products SET name = :name, description = :description WHERE id = :id");
	return $stmt->execute([
		":name" => $name,
		":description" => $description,
		":id" => $id,
	]);
}

function get_product_gallery_posts(bool $includeHidden = false): array
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT id, title, caption, cover_image_path, display_order, is_visible, created_at FROM product_gallery_posts";
	if (!$includeHidden) {
		$sql .= " WHERE is_visible = 1";
	}
	$sql .= " ORDER BY display_order ASC, created_at DESC";
	$stmt = $pdo->query($sql);
	return attach_product_gallery_images($stmt->fetchAll());
}

function get_product_gallery_post(int $id, bool $includeHidden = true): ?array
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT id, title, caption, cover_image_path, display_order, is_visible, created_at FROM product_gallery_posts WHERE id = :id";
	if (!$includeHidden) {
		$sql .= " AND is_visible = 1";
	}
	$sql .= " LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([":id" => $id]);
	$row = $stmt->fetch();
	if (!$row) {
		return null;
	}

	$posts = attach_product_gallery_images([$row]);
	return $posts[0] ?? null;
}

function count_product_gallery_posts(bool $includeHidden = true): int
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT COUNT(*) FROM product_gallery_posts";
	if (!$includeHidden) {
		$sql .= " WHERE is_visible = 1";
	}
	return (int)($pdo->query($sql)->fetchColumn() ?: 0);
}

function add_product_gallery_post(string $title, string $caption, int $displayOrder, bool $isVisible, array $images): ?int
{
	ensure_schema();
	if (!$images) {
		return null;
	}

	$pdo = db();
	$insertPost = $pdo->prepare(
		"INSERT INTO product_gallery_posts (title, caption, cover_image_path, display_order, is_visible, created_at) VALUES (:title, :caption, :cover_image_path, :display_order, :is_visible, NOW())"
	);
	$insertImage = $pdo->prepare(
		"INSERT INTO product_gallery_images (gallery_post_id, image_path, alt_text, display_order, created_at) VALUES (:gallery_post_id, :image_path, :alt_text, :display_order, NOW())"
	);

	$pdo->beginTransaction();
	try {
		$coverImagePath = (string)($images[0]["image_path"] ?? "");
		$insertPost->execute([
			":title" => $title,
			":caption" => $caption,
			":cover_image_path" => $coverImagePath,
			":display_order" => $displayOrder,
			":is_visible" => $isVisible ? 1 : 0,
		]);

		$postId = (int)$pdo->lastInsertId();
		foreach ($images as $index => $image) {
			$insertImage->execute([
				":gallery_post_id" => $postId,
				":image_path" => $image["image_path"],
				":alt_text" => $image["alt_text"] ?? null,
				":display_order" => $image["display_order"] ?? $index,
			]);
		}

		$pdo->commit();
		return $postId;
	} catch (Throwable $e) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		return null;
	}
}

function update_product_gallery_post(int $id, string $title, string $caption, int $displayOrder, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare(
		"UPDATE product_gallery_posts SET title = :title, caption = :caption, display_order = :display_order, is_visible = :is_visible WHERE id = :id"
	);
	return $stmt->execute([
		":title" => $title,
		":caption" => $caption,
		":display_order" => $displayOrder,
		":is_visible" => $isVisible ? 1 : 0,
		":id" => $id,
	]);
}

function add_images_to_product_gallery_post(int $postId, array $images): bool
{
	ensure_schema();
	if (!$images) {
		return true;
	}

	$pdo = db();
	$maxOrderStmt = $pdo->prepare("SELECT COALESCE(MAX(display_order), -1) FROM product_gallery_images WHERE gallery_post_id = :post_id");
	$maxOrderStmt->execute([":post_id" => $postId]);
	$startOrder = (int)($maxOrderStmt->fetchColumn() ?: -1) + 1;

	$insertImage = $pdo->prepare(
		"INSERT INTO product_gallery_images (gallery_post_id, image_path, alt_text, display_order, created_at) VALUES (:gallery_post_id, :image_path, :alt_text, :display_order, NOW())"
	);

	$pdo->beginTransaction();
	try {
		foreach ($images as $index => $image) {
			$insertImage->execute([
				":gallery_post_id" => $postId,
				":image_path" => $image["image_path"],
				":alt_text" => $image["alt_text"] ?? null,
				":display_order" => $startOrder + $index,
			]);
		}
		sync_product_gallery_post_cover_with_pdo($pdo, $postId);
		$pdo->commit();
		return true;
	} catch (Throwable $e) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		return false;
	}
}

function get_product_gallery_image(int $imageId): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT id, gallery_post_id, image_path, alt_text, display_order, created_at FROM product_gallery_images WHERE id = :id LIMIT 1");
	$stmt->execute([":id" => $imageId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

function update_product_gallery_image_alt_text(int $imageId, string $altText): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("UPDATE product_gallery_images SET alt_text = :alt_text WHERE id = :id");
	return $stmt->execute([
		":alt_text" => $altText,
		":id" => $imageId,
	]);
}

function delete_product_gallery_image(int $imageId): bool
{
	ensure_schema();
	$image = get_product_gallery_image($imageId);
	if (!$image) {
		return false;
	}

	$pdo = db();
	$pdo->beginTransaction();
	try {
		$stmt = $pdo->prepare("DELETE FROM product_gallery_images WHERE id = :id");
		$stmt->execute([":id" => $imageId]);
		sync_product_gallery_post_cover_with_pdo($pdo, (int)$image["gallery_post_id"]);
		$pdo->commit();
		return true;
	} catch (Throwable $e) {
		if ($pdo->inTransaction()) {
			$pdo->rollBack();
		}
		return false;
	}
}

function delete_product_gallery_post(int $id): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("DELETE FROM product_gallery_posts WHERE id = :id");
	return $stmt->execute([":id" => $id]);
}

function count_product_gallery_images_by_path(string $imagePath, ?int $excludeImageId = null): int
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT COUNT(*) AS total FROM product_gallery_images WHERE image_path = :image_path";
	$params = [":image_path" => $imagePath];
	if ($excludeImageId !== null) {
		$sql .= " AND id <> :exclude_image_id";
		$params[":exclude_image_id"] = $excludeImageId;
	}
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	return (int)($stmt->fetchColumn() ?: 0);
}

function set_product_gallery_post_visibility(int $id, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("UPDATE product_gallery_posts SET is_visible = :is_visible WHERE id = :id");
	return $stmt->execute([
		":is_visible" => $isVisible ? 1 : 0,
		":id" => $id,
	]);
}

function update_product_gallery_post_order(int $id, int $displayOrder): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("UPDATE product_gallery_posts SET display_order = :display_order WHERE id = :id");
	return $stmt->execute([
		":display_order" => $displayOrder,
		":id" => $id,
	]);
}

function get_product_gallery_items(bool $includeHidden = false): array
{
	return get_product_gallery_posts($includeHidden);
}

function get_product_gallery_item(int $id): ?array
{
	return get_product_gallery_post($id, true);
}

function count_product_gallery_items_by_image_path(string $imagePath, ?int $excludeId = null): int
{
	return count_product_gallery_images_by_path($imagePath, $excludeId);
}

function set_product_gallery_visibility(int $id, bool $isVisible): bool
{
	return set_product_gallery_post_visibility($id, $isVisible);
}

function update_product_gallery_order(int $id, int $displayOrder): bool
{
	return update_product_gallery_post_order($id, $displayOrder);
}

function get_news_posts(bool $includeHidden = false): array
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT id, title, content, author_name, image_path, display_order, is_visible, created_at FROM news_posts";
	if (!$includeHidden) {
		$sql .= " WHERE is_visible = 1";
	}
	$sql .= " ORDER BY display_order ASC, created_at DESC";
	$stmt = $pdo->query($sql);
	return $stmt->fetchAll();
}

function get_news_post(int $id): ?array
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT id, title, content, author_name, image_path, display_order, is_visible, created_at FROM news_posts WHERE id = :id LIMIT 1");
	$stmt->execute([":id" => $id]);
	$row = $stmt->fetch();
	return $row ?: null;
}

function add_news_post(string $title, string $content, string $authorName, ?string $imagePath, int $displayOrder, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare(
		"INSERT INTO news_posts (title, content, author_name, image_path, display_order, is_visible, created_at) VALUES (:title, :content, :author_name, :image_path, :display_order, :is_visible, NOW())"
	);
	return $stmt->execute([
		":title" => $title,
		":content" => $content,
		":author_name" => $authorName,
		":image_path" => $imagePath,
		":display_order" => $displayOrder,
		":is_visible" => $isVisible ? 1 : 0,
	]);
}

function update_news_post(int $id, string $title, string $content, string $authorName, ?string $imagePath, int $displayOrder, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	if ($imagePath !== null) {
		$stmt = $pdo->prepare(
			"UPDATE news_posts SET title = :title, content = :content, author_name = :author_name, image_path = :image_path, display_order = :display_order, is_visible = :is_visible WHERE id = :id"
		);
		return $stmt->execute([
			":title" => $title,
			":content" => $content,
			":author_name" => $authorName,
			":image_path" => $imagePath,
			":display_order" => $displayOrder,
			":is_visible" => $isVisible ? 1 : 0,
			":id" => $id,
		]);
	}

	$stmt = $pdo->prepare(
		"UPDATE news_posts SET title = :title, content = :content, author_name = :author_name, display_order = :display_order, is_visible = :is_visible WHERE id = :id"
	);
	return $stmt->execute([
		":title" => $title,
		":content" => $content,
		":author_name" => $authorName,
		":display_order" => $displayOrder,
		":is_visible" => $isVisible ? 1 : 0,
		":id" => $id,
	]);
}

function delete_news_post(int $id): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("DELETE FROM news_posts WHERE id = :id");
	return $stmt->execute([":id" => $id]);
}

function set_news_post_visibility(int $id, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("UPDATE news_posts SET is_visible = :is_visible WHERE id = :id");
	return $stmt->execute([
		":is_visible" => $isVisible ? 1 : 0,
		":id" => $id,
	]);
}

function update_news_post_order(int $id, int $displayOrder): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("UPDATE news_posts SET display_order = :display_order WHERE id = :id");
	return $stmt->execute([
		":display_order" => $displayOrder,
		":id" => $id,
	]);
}

function count_news_posts_by_image_path(string $imagePath, ?int $excludeId = null): int
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT COUNT(*) FROM news_posts WHERE image_path = :image_path";
	$params = [":image_path" => $imagePath];
	if ($excludeId !== null) {
		$sql .= " AND id <> :exclude_id";
		$params[":exclude_id"] = $excludeId;
	}
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	return (int)($stmt->fetchColumn() ?: 0);
}

function get_social_links(bool $includeHidden = false): array
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT id, platform_key, url, display_order, is_visible, created_at FROM social_links";
	if (!$includeHidden) {
		$sql .= " WHERE is_visible = 1";
	}
	$sql .= " ORDER BY display_order ASC, id ASC";
	$stmt = $pdo->query($sql);
	$rows = $stmt->fetchAll();

	$catalog = social_platform_catalog();
	$links = [];
	foreach ($rows as $row) {
		$platformKey = (string)($row["platform_key"] ?? "");
		if (!isset($catalog[$platformKey])) {
			continue;
		}

		$meta = $catalog[$platformKey];
		$row["name"] = $meta["name"];
		$row["icon"] = $meta["icon"];
		$row["description"] = $meta["description"];
		$row["button_label"] = $meta["button_label"];
		$links[] = $row;
	}

	return $links;
}

function count_social_links(bool $includeHidden = true): int
{
	ensure_schema();
	$pdo = db();
	$sql = "SELECT COUNT(*) FROM social_links";
	if (!$includeHidden) {
		$sql .= " WHERE is_visible = 1";
	}
	return (int)($pdo->query($sql)->fetchColumn() ?: 0);
}

function update_social_link_settings(int $id, string $url, int $displayOrder, bool $isVisible): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare(
		"UPDATE social_links SET url = :url, display_order = :display_order, is_visible = :is_visible WHERE id = :id"
	);
	return $stmt->execute([
		":url" => $url,
		":display_order" => $displayOrder,
		":is_visible" => $isVisible ? 1 : 0,
		":id" => $id,
	]);
}

function get_site_setting(string $key, ?string $defaultValue = null): ?string
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = :setting_key LIMIT 1");
	$stmt->execute([":setting_key" => $key]);
	$value = $stmt->fetchColumn();
	if ($value === false) {
		return $defaultValue;
	}

	return (string)$value;
}

function set_site_setting(string $key, string $value): bool
{
	ensure_schema();
	$pdo = db();
	$stmt = $pdo->prepare(
		"INSERT INTO site_settings (setting_key, setting_value, updated_at) VALUES (:setting_key, :setting_value, NOW())
		ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()"
	);

	return $stmt->execute([
		":setting_key" => $key,
		":setting_value" => $value,
	]);
}

function get_site_logo_path(): string
{
	$logoPath = get_site_setting("site_logo_path", "source_img/Ace-mtech-logo.png");
	if ($logoPath === null || trim($logoPath) === "") {
		return "source_img/Ace-mtech-logo.png";
	}

	return (string)$logoPath;
}

function set_site_logo_path(string $logoPath): bool
{
	return set_site_setting("site_logo_path", $logoPath);
}
