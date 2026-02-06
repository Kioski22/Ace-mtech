<?php

declare(strict_types=1);

require_once __DIR__ . "/db.php";

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
