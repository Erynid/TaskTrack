<?php
/**
 * TaskTrack - Google OAuth 2.0 Initiation Route
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

require_once __DIR__ . '/../config/google.php';

try {
    $authUrl = getGoogleAuthUrl();
    header('Location: ' . $authUrl);
    exit;
} catch (Exception $e) {
    error_log('[Google OAuth Init Error] ' . $e->getMessage());
    $_SESSION['auth_error'] = 'Gagal menginisialisasi Google Sign-In: ' . $e->getMessage();
    header('Location: ../login.php');
    exit;
}

