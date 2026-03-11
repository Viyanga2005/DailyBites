<?php

require_once __DIR__ . '/common.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $stmt = db()->query('SELECT id, title, message, created_at FROM announcements ORDER BY created_at DESC');
    respond(['ok' => true, 'announcements' => $stmt->fetchAll()]);
}

$data = json_input();

if ($method === 'POST') {
    must_be_admin();

    $title = trim((string) ($data['title'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));

    if ($title === '' || $message === '') {
        respond(['ok' => false, 'error' => 'Title and message are required'], 422);
    }

    $stmt = db()->prepare('INSERT INTO announcements (title, message) VALUES (?, ?)');
    $stmt->execute([$title, $message]);

    respond(['ok' => true, 'message' => 'Announcement created']);
}

if ($method === 'DELETE') {
    must_be_admin();

    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
        respond(['ok' => false, 'error' => 'Missing announcement id'], 422);
    }

    $stmt = db()->prepare('DELETE FROM announcements WHERE id = ?');
    $stmt->execute([$id]);

    respond(['ok' => true, 'message' => 'Announcement deleted']);
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
