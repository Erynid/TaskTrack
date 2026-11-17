<?php
/**
 * TaskTrack - User Profile Endpoint (/api/me.php)
 * Modul 12 Latihan 2: Mengembalikan data profil pengguna aktif tanpa password hash
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

// 1. Route Guard: Wajib login (401 jika unauthenticated)
$auth = requireLogin();
$userId = $auth['user_id'];

try {
    $pdo = Database::getInstance();
    $stmt = $pdo->prepare('
        SELECT id, username, email, role, avatar_url, email_verified_at, created_at, updated_at
        FROM users 
        WHERE id = :id 
        LIMIT 1
    ');
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        if (!headers_sent()) http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'error' => 'Pengguna tidak ditemukan',
            'code' => 404
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Data aman tanpa password_hash, verification_token, dsb.
    if (!headers_sent()) http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => [
            'id' => (int)$user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar_url' => $user['avatar_url'],
            'email_verified_at' => $user['email_verified_at'],
            'created_at' => $user['created_at'],
            'updated_at' => $user['updated_at']
        ]
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if (!defined('TEST_RUNNING')) {
        exit;
    }

} catch (Throwable $e) {
    error_log('[TaskTrack /api/me.php Error] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => 'Terjadi kesalahan sistem saat memuat profil pengguna.',
        'code' => 500
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
