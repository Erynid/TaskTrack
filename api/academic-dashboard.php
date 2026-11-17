<?php
/**
 * TaskTrack - API Endpoint: Ringkasan Akademik Mini Dashboard
 * Mengembalikan data terstruktur format JSON untuk dikonsumsi via fetch() di frontend.
 * Header: Content-Type application/json; charset=utf-8
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
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Autentikasi Pengguna (Fail-Closed)
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'Akses ditolak: Autentikasi sesi diperlukan untuk mengakses ringkasan akademik.'
    ]);
    exit;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!empty($origin)) {
    $parsedOrigin = parse_url($origin, PHP_URL_HOST);
    if ($parsedOrigin === 'localhost' || $parsedOrigin === '127.0.0.1' || $parsedOrigin === $serverHost) {
        header("Access-Control-Allow-Origin: $origin");
        header('Access-Control-Allow-Credentials: true');
    }
}
header('Access-Control-Allow-Methods: GET');

$jsonPath = __DIR__ . '/../data/academic-dashboard.json';

if (!file_exists($jsonPath)) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Berkas data akademik tidak ditemukan di server.'
    ]);
    exit;
}

$content = file_get_contents($jsonPath);
if ($content === false) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membaca berkas data akademik.'
    ]);
    exit;
}

$data = json_decode($content, true);
if (is_array($data) && !empty($_SESSION['user']['name'])) {
    $data['student']['name'] = $_SESSION['user']['name'];
    if (!empty($_SESSION['user']['email'])) {
        $data['student']['email'] = $_SESSION['user']['email'];
    }
    $content = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}

http_response_code(200);
echo $content;