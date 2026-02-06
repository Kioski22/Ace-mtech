<?php

declare(strict_types=1);

function load_items(string $type): array
{
	$path = __DIR__ . "/../data/{$type}.json";
	if (!file_exists($path)) {
		return [];
	}

	$raw = file_get_contents($path);
	if ($raw === false) {
		return [];
	}

	$data = json_decode($raw, true);
	return is_array($data) ? $data : [];
}

function save_items(string $type, array $items): bool
{
	$path = __DIR__ . "/../data/{$type}.json";
	$payload = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	if ($payload === false) {
		return false;
	}

	return file_put_contents($path, $payload) !== false;
}

function add_item(string $type, string $name, string $imagePath): bool
{
	$items = load_items($type);
	$items[] = [
		"name" => $name,
		"image" => $imagePath,
		"created_at" => date(DATE_ATOM),
	];

	return save_items($type, $items);
}
