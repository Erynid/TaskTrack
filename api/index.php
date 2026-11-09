<?php
/**
 * TaskTrack - Central REST API Router (Tugas 10 - Database Integration)
 */

if (!is_writable(session_save_path())) {
    session_save_path(sys_get_temp_dir());
}
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// Batasi CORS ke origin yang sah (Same-Origin / Localhost)
$allowedOrigins = ['http://localhost', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array(rtrim($origin, '/'), $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Tangani Preflight Request
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Load .env secara aman
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

require_once __DIR__ . '/../models/TaskModel.php';

function jsonResponse($status, $dataOrMessage, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'payload' => $dataOrMessage
    ]);
    exit;
}

$path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);

// Routing Endpoint
if (strpos($path, '/tasks') !== false) {
    require __DIR__ . '/tasks.php';
    exit;
} elseif (strpos($path, '/dashboard') !== false) {
    // Autentikasi Pengguna
    if (!isset($_SESSION['user'])) {
        jsonResponse('error', 'Akses ditolak: Autentikasi sesi diperlukan untuk mengakses endpoint ini.', 401);
    }

    try {
        $taskModel = new TaskModel();
        // Ambil data tugas milik user yang sedang aktif
        $currentUserId = $_SESSION['user']['id'] ?? 1;
        $tasks = $taskModel->getTasksByUserId($currentUserId);

        jsonResponse('success', [
            'total_tasks' => count($tasks),
            'tasks' => $tasks
        ], 200);
    } catch (Exception $e) {
        error_log('[TaskTrack API Error] ' . $e->getMessage());
        jsonResponse('error', 'Terjadi kesalahan internal saat mengambil data tugas.', 500);
    }
} else {
    jsonResponse('error', 'Endpoint tidak ditemukan', 404);
}