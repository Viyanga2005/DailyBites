<?php

require_once __DIR__ . '/common.php';

function cart_details(): array
{
    $cart = get_cart_map();
    $products = fetch_products_by_ids(array_keys($cart));

    $items = [];
    $subtotal = 0;

    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) {
            continue;
        }

        $product = $products[$productId];
        $q = max(1, (int) $qty);
        $line = $q * (int) $product['price'];
        $subtotal += $line;

        $items[] = [
            'id' => $product['id'],
            'name' => $product['name'],
            'category' => $product['category'],
            'badge' => $product['badge'],
            'image_url' => $product['image_url'],
            'price' => (int) $product['price'],
            'qty' => $q,
            'line_total' => $line,
        ];
    }

    return [
        'items' => $items,
        'subtotal' => $subtotal,
        'delivery' => 0,
        'total' => $subtotal,
        'count' => cart_count(),
    ];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    respond(['ok' => true, 'cart' => cart_details()]);
}

$data = json_input();
$action = $data['action'] ?? '';

if ($method === 'POST' && $action === 'add') {
    $id = trim((string) ($data['id'] ?? ''));
    $qty = max(1, (int) ($data['qty'] ?? 1));

    $cart = get_cart_map();
    $cart[$id] = ((int) ($cart[$id] ?? 0)) + $qty;
    save_cart_map($cart);

    respond(['ok' => true, 'cart' => cart_details()]);
}

if ($method === 'POST' && $action === 'set_qty') {
    $id = trim((string) ($data['id'] ?? ''));
    $qty = max(1, (int) ($data['qty'] ?? 1));

    $cart = get_cart_map();
    if (isset($cart[$id])) {
        $cart[$id] = $qty;
        save_cart_map($cart);
    }

    respond(['ok' => true, 'cart' => cart_details()]);
}

if ($method === 'POST' && $action === 'remove') {
    $id = trim((string) ($data['id'] ?? ''));
    $cart = get_cart_map();
    unset($cart[$id]);
    save_cart_map($cart);

    respond(['ok' => true, 'cart' => cart_details()]);
}

if ($method === 'POST' && $action === 'clear') {
    save_cart_map([]);
    respond(['ok' => true, 'cart' => cart_details()]);
}

if ($method === 'POST' && $action === 'checkout') {
    $cart = get_cart_map();
    if (!$cart) {
        respond(['ok' => false, 'error' => 'Cart is empty'], 422);
    }

    $products = fetch_products_by_ids(array_keys($cart));
    $lines = [];
    $total = 0;

    foreach ($cart as $productId => $qty) {
        if (!isset($products[$productId])) {
            continue;
        }

        $q = max(1, (int) $qty);
        $price = (int) $products[$productId]['price'];
        $lineTotal = $q * $price;
        $total += $lineTotal;

        $lines[] = [
            'product_id' => $productId,
            'qty' => $q,
            'unit_price' => $price,
            'line_total' => $lineTotal,
        ];
    }

    if (!$lines) {
        respond(['ok' => false, 'error' => 'No valid products in cart'], 422);
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('INSERT INTO orders (total_amount, status) VALUES (?, ?)');
        $stmt->execute([$total, 'Pending']);

        $orderId = (int) $pdo->lastInsertId();

        $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, product_id, qty, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');
        foreach ($lines as $line) {
            $itemStmt->execute([$orderId, $line['product_id'], $line['qty'], $line['unit_price'], $line['line_total']]);
        }

        $pdo->commit();

        save_cart_map([]);

        respond([
            'ok' => true,
            'message' => 'Order placed',
            'order_id' => $orderId,
            'cart' => cart_details(),
        ]);
    } catch (Throwable $e) {
        $pdo->rollBack();
        respond(['ok' => false, 'error' => 'Failed to place order'], 500);
    }
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
