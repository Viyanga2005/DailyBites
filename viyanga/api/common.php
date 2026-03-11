<?php

require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

function json_input(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function respond(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function must_be_admin(): void
{
    if (empty($_SESSION['admin_logged_in'])) {
        respond(['ok' => false, 'error' => 'Unauthorized'], 401);
    }
}

function get_cart_map(): array
{
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    return $_SESSION['cart'];
}

function save_cart_map(array $cart): void
{
    $_SESSION['cart'] = $cart;
}

function cart_count(): int
{
    $count = 0;
    foreach (get_cart_map() as $qty) {
        $count += max(0, (int) $qty);
    }

    return $count;
}

function fetch_products_by_ids(array $ids): array
{
    if (!$ids) {
        return [];
    }

    $ids = array_values(array_unique(array_map('strval', $ids)));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    $stmt = db()->prepare("SELECT id, name, category, price, badge, image_url FROM products WHERE id IN ($placeholders)");
    $stmt->execute($ids);

    $rows = $stmt->fetchAll();
    $indexed = [];
    foreach ($rows as $row) {
        $indexed[$row['id']] = $row;
    }

    return $indexed;
}
