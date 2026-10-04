<?php
/**
 * Admin Image Upload Endpoint
 * Returns JSON: { success: true, path: "/uploads/services/abc123.jpg" }
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Keep PHP warnings from corrupting the JSON response consumed by the uploader.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

header('Content-Type: application/json');

// Uploads are called with fetch(), so return JSON instead of redirecting to the login page.
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Your admin session has expired. Please log in again.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// CSRF check
if (!csrf_verify($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
    exit;
}

// File present?
if (empty($_FILES['file']) || $_FILES['file']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];

// Upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errors = [
        UPLOAD_ERR_INI_SIZE   => 'File too large (server limit)',
        UPLOAD_ERR_FORM_SIZE  => 'File too large (form limit)',
        UPLOAD_ERR_PARTIAL    => 'Partial upload',
        UPLOAD_ERR_NO_TMP_DIR => 'No temp directory',
        UPLOAD_ERR_CANT_WRITE => 'Cannot write file',
        UPLOAD_ERR_EXTENSION  => 'Upload blocked by extension',
    ];
    echo json_encode(['success' => false, 'error' => $errors[$file['error']] ?? 'Unknown upload error']);
    exit;
}

// Size limit (5 MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5 MB)']);
    exit;
}

// Validate MIME via finfo (server-detected, not client-provided)
$allowed_mimes = [
    'image/jpeg'   => 'jpg',
    'image/png'    => 'png',
    'image/gif'    => 'gif',
    'image/webp'   => 'webp',
    'image/svg+xml'=> 'svg',
];

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($file['tmp_name']);

if ($mime === false || !isset($allowed_mimes[$mime])) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed: JPG, PNG, GIF, WebP, SVG']);
    exit;
}

// Allowed destination folders
$folder = preg_replace('/[^a-z0-9\-]/', '', $_POST['folder'] ?? 'general');
$allowed_folders = ['services', 'portfolio', 'blog', 'testimonials', 'categories', 'general', 'og'];
if (!in_array($folder, $allowed_folders, true)) {
    $folder = 'general';
}

// Ensure folder exists
$target_dir = ROOT_PATH . '/uploads/' . $folder . '/';
if (!is_dir($target_dir)) {
    if (!mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
        echo json_encode(['success' => false, 'error' => 'Could not create upload folder']);
        exit;
    }
}

// Generate safe filename (never trust original)
$ext  = $allowed_mimes[$mime];
$base = pathinfo($file['name'], PATHINFO_FILENAME);
$base = preg_replace('/[^a-z0-9]/i', '-', $base);
$base = strtolower(trim($base, '-'));
if ($base === '') $base = 'image';
$base = substr($base, 0, 50);

$filename    = $base . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
$target_path = $target_dir . $filename;
$public_path = rtrim(BASE_URL, '/') . '/uploads/' . $folder . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(['success' => false, 'error' => 'Could not save uploaded file']);
    exit;
}

// Optional: record in media table
try {
    $stmt = $db->prepare("
        INSERT INTO media (file_name, original_name, file_path, file_type, mime_type, file_size, uploaded_by, created_at, updated_at)
        VALUES (:fn, :on, :fp, :ft, :mt, :fs, :ub, NOW(), NOW())
    ");
    $stmt->execute([
        ':fn' => $filename,
        ':on' => $file['name'],
        ':fp' => $public_path,
        ':ft' => $ext,
        ':mt' => $mime,
        ':fs' => $file['size'],
        ':ub' => $_SESSION['user_id'] ?? null,
    ]);
} catch (PDOException $e) {
    // Media table is optional — don't fail the upload if this errors
    error_log('[YK Upload] Media log failed: ' . $e->getMessage());
}

echo json_encode([
    'success'  => true,
    'path'     => $public_path,
    'filename' => $filename,
    'size'     => $file['size'],
]);