<?php

require_once __DIR__ . '/common.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    respond(['ok' => true, 'authenticated' => !empty($_SESSION['admin_logged_in'])]);
}

$data = json_input();
$action = $data['action'] ?? '';

if ($method === 'POST' && $action === 'logout') {
    $_SESSION['admin_logged_in'] = false;
    respond(['ok' => true, 'message' => 'Logged out']);
}

if ($method === 'POST' && $action === 'login') {
    $username = trim((string) ($data['username'] ?? ''));
    $password = trim((string) ($data['password'] ?? ''));

    $stmt = db()->prepare('SELECT id, username, password FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['password'] !== $password) {
        respond(['ok' => false, 'error' => 'Invalid username or password'], 401);
    }

    $_SESSION['admin_logged_in'] = true;
    respond(['ok' => true, 'message' => 'Login successful']);
}

respond(['ok' => false, 'error' => 'Method not allowed'], 405);
