<?php

require_once __DIR__ . '/common.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = db()->query('SELECT id, name, category, price, badge, image_url FROM products ORDER BY created_at ASC');
    respond(['ok' => true, 'products' => $stmt->fetchAll()]);
}

$data = json_input();

if ($method === 'POST') {
    must_be_admin();

    $id = trim((string) ($data['id'] ?? ''));
    $name = trim((string) ($data['name'] ?? ''));
    $category = trim((string) ($data['category'] ?? 'Other'));
    $price = (int) ($data['price'] ?? 0);
    $badge = trim((string) ($data['badge'] ?? 'Popular'));
    $imageUrl = trim((string) ($data['image_url'] ?? ''));

    if ($name === '' || $price <= 0) {
        respond(['ok' => false, 'error' => 'Name and valid price are required'], 422);
    }

    if ($id !== '') {
        $stmt = db()->prepare('UPDATE products SET name = ?, category = ?, price = ?, badge = ?, image_url = ? WHERE id = ?');
        $stmt->execute([$name, $category, $price, $badge, $imageUrl, $id]);
        respond(['ok' => true, 'message' => 'Product updated']);
    }

    $stmt = db()->prepare('INSERT INTO products (name, category, price, badge, image_url) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$name, $category, $price, $badge, $imageUrl]);

    respond(['ok' => true, 'message' => 'Product created']);
}

if ($method === 'DELETE') {
    must_be_admin();

    $id = trim((string) ($_GET['id'] ?? ''));
    if ($id === '') {
        respond(['ok' => false, 'error' => 'Missing product id'], 422);
    }

    $stmt = db()->prepare('DELETE FROM products WHERE id = ?');
    $stmt->execute([$id]);

    respond(['ok' => true, 'message' => 'Product deleted']);
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
