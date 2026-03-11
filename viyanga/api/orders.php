<?php

require_once __DIR__ . '/common.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    must_be_admin();

    $stmt = db()->query('SELECT id, total_amount, status, created_at FROM orders ORDER BY created_at DESC');
    $orders = $stmt->fetchAll();

    $ids = array_map(static fn($o) => (int) $o['id'], $orders);
    $itemsByOrder = [];

    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $itemStmt = db()->prepare("SELECT oi.order_id, oi.qty, p.name
                                   FROM order_items oi
                                   JOIN products p ON p.id = oi.product_id
                                   WHERE oi.order_id IN ($placeholders)");
        $itemStmt->execute($ids);

        foreach ($itemStmt->fetchAll() as $row) {
            $orderId = (int) $row['order_id'];
            if (!isset($itemsByOrder[$orderId])) {
                $itemsByOrder[$orderId] = [];
            }

            $itemsByOrder[$orderId][] = $row;
        }
    }

    foreach ($orders as &$order) {
        $oid = (int) $order['id'];
        $items = $itemsByOrder[$oid] ?? [];
        $order['items_count'] = array_reduce($items, static fn($sum, $it) => $sum + (int) $it['qty'], 0);
    }

    respond(['ok' => true, 'orders' => $orders]);
}

$data = json_input();

if ($method === 'POST') {
    must_be_admin();

    $action = $data['action'] ?? '';

    if ($action === 'status') {
        $id = (int) ($data['id'] ?? 0);
        $status = trim((string) ($data['status'] ?? 'Pending'));

        $allowed = ['Pending', 'Preparing', 'Delivered', 'Cancelled'];
        if ($id <= 0 || !in_array($status, $allowed, true)) {
            respond(['ok' => false, 'error' => 'Invalid payload'], 422);
        }

        $stmt = db()->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $id]);

        respond(['ok' => true, 'message' => 'Order status updated']);
    }

    if ($action === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id <= 0) {
            respond(['ok' => false, 'error' => 'Missing order id'], 422);
        }

        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('DELETE FROM order_items WHERE order_id = ?');
            $stmt->execute([$id]);

            $orderStmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
            $orderStmt->execute([$id]);

            $pdo->commit();
            respond(['ok' => true, 'message' => 'Order deleted']);
        } catch (Throwable $e) {
            $pdo->rollBack();
            respond(['ok' => false, 'error' => 'Failed to delete order'], 500);
        }
    }
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
