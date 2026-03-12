<?php

declare(strict_types=1);

function supported_image_extensions(): array
{
	return ["jpg", "jpeg", "png", "webp", "gif", "avif", "svg", "heic", "heif"];
}

function supported_image_mime_types(): array
{
	return [
		"image/jpeg",
		"image/png",
		"image/webp",
		"image/gif",
		"image/avif",
		"image/svg+xml",
		"image/heic",
		"image/heif",
		"application/octet-stream",
	];
}

function supported_image_accept_value(): string
{
	return implode(",", array_map(static fn (string $ext): string => "." . $ext, supported_image_extensions()));
}

function humanize_uploaded_filename(string $filename): string
{
	$base = pathinfo($filename, PATHINFO_FILENAME);
	$base = preg_replace('/[_-]+/', ' ', $base) ?? $base;
	$base = trim($base);
	if ($base === '') {
		return 'Gallery image';
	}

	return ucwords($base);
}

function normalize_uploaded_files(array $files): array
{
	if (!isset($files['name']) || !is_array($files['name'])) {
		return [$files];
	}

	$normalized = [];
	$count = count($files['name']);
	for ($i = 0; $i < $count; $i++) {
		$normalized[] = [
			'name' => $files['name'][$i] ?? '',
			'type' => $files['type'][$i] ?? '',
			'tmp_name' => $files['tmp_name'][$i] ?? '',
			'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
			'size' => $files['size'][$i] ?? 0,
		];
	}

	return $normalized;
}

function validate_svg_upload(string $tmpPath): bool
{
	$content = @file_get_contents($tmpPath);
	if ($content === false) {
		return false;
	}

	if (stripos($content, '<svg') === false) {
		return false;
	}

	return !preg_match('/<script\b|<foreignObject\b|on[a-z]+\s*=|javascript:/i', $content);
}

function upload_image_file(array $file, string $uploadDir, string $relativeDir, string $prefix, array &$errors): ?string
{
	if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
		$errors[] = 'Image upload failed.';
		return null;
	}

	if (!is_dir($uploadDir) && !mkdir($uploadDir, 0775, true) && !is_dir($uploadDir)) {
		$errors[] = 'Failed to create upload directory.';
		return null;
	}

	$ext = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
	if (!in_array($ext, supported_image_extensions(), true)) {
		$errors[] = 'Unsupported image format. Allowed: JPG, PNG, WEBP, GIF, AVIF, SVG, HEIC, HEIF.';
		return null;
	}

	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = $finfo ? (string)finfo_file($finfo, (string)$file['tmp_name']) : '';
	if ($finfo) {
		finfo_close($finfo);
	}

	if ($mime !== '' && !in_array(strtolower($mime), supported_image_mime_types(), true)) {
		$errors[] = 'Image must be a valid file.';
		return null;
	}

	if ($ext === 'svg' && !validate_svg_upload((string)$file['tmp_name'])) {
		$errors[] = 'SVG files must be safe and free of scripts.';
		return null;
	}

	$filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
	$targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $filename;
	if (!move_uploaded_file((string)$file['tmp_name'], $targetPath)) {
		$errors[] = 'Failed to upload image.';
		return null;
	}

	return trim($relativeDir, '/\\') . '/' . $filename;
}

function uploaded_image_absolute_path(string $relativePath): ?string
{
	$relativePath = trim(str_replace('\\', '/', $relativePath));
	if ($relativePath === '' || str_contains($relativePath, '..')) {
		return null;
	}

	$projectRoot = realpath(__DIR__ . '/..');
	if ($projectRoot === false) {
		return null;
	}

	return $projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/'));
}

function delete_uploaded_image_file(string $relativePath): bool
{
	$absolutePath = uploaded_image_absolute_path($relativePath);
	if ($absolutePath === null || !is_file($absolutePath)) {
		return false;
	}

	return @unlink($absolutePath);
}
