<?php
/**
 * TaskTrack - Secure Logout Route (Modul 12 Langkah 6)
 * Mendukung penghentian sesi melalui HTTP POST dan GET dengan pembersihan cookie menyeluruh
 */

require_once __DIR__ . '/config/auth.php';

// Eksekusi pembersihan sesi: kosongkan $_SESSION, expired-kan cookie, dan session_destroy()
performSecureLogout();

// Jika dipanggil via AJAX/Fetch API atau request JSON
$isJsonRequest = (
    (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
    (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
    (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
);

if ($isJsonRequest) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => 'success',
        'code' => 200,
        'message' => 'Logout berhasil. Sesi autentikasi telah diakhiri secara aman.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>TaskTrack - Logout</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <script>
        // Bersihkan state autentikasi lokal
        try {
            localStorage.removeItem('tasktrack_agro_auth');
            sessionStorage.clear();
        } catch (e) {}
        window.location.href = 'index.php?logged_out=1';
    </script>
</head>
<body style="background: #090a0e; color: #94a3b8; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
    <p>Sedang keluar dari akun TaskTrack...</p>
</body>
</html>
