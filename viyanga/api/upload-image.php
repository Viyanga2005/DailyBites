<?php

require_once __DIR__ . '/common.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    respond(['ok' => false, 'error' => 'Method not allowed'], 405);
}

must_be_admin();

// Define upload directory
$uploadDir = __DIR__ . '/../images/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $error = $_FILES['image']['error'] ?? 'Unknown error';
    $errorMsg = match ($error) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File size exceeds limit',
        UPLOAD_ERR_PARTIAL => 'File upload incomplete',
        UPLOAD_ERR_NO_FILE => 'No file selected',
        default => 'Upload failed'
    };
    respond(['ok' => false, 'error' => $errorMsg], 422);
}

$file = $_FILES['image'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Validate file size
if ($file['size'] > $maxSize) {
    respond(['ok' => false, 'error' => 'File size exceeds 5MB limit'], 422);
}

// Validate mime type
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedMimes)) {
    respond(['ok' => false, 'error' => 'Invalid image format. Only JPEG, PNG, WebP, and GIF allowed'], 422);
}

// Generate unique filename
$ext = match ($mime) {
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'image/gif' => 'gif',
    default => 'jpg'
};

$filename = uniqid('product_', true) . '.' . $ext;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    respond(['ok' => false, 'error' => 'Failed to save image'], 500);
}

// Return relative path for database storage
$relativePath = '/viyanga/images/products/' . $filename;

respond(['ok' => true, 'image_url' => $relativePath]);
