<?php
/**
 * TaskTrack - Google OAuth 2.0 & GIS Callback Handler
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

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");

require_once __DIR__ . '/../config/google.php';

function failLogin(string $message): void
{
    $_SESSION['auth_error'] = $message;
    header('Location: ../login.php');
    exit;
}

try {
    $googleUserData = null;

    // 1. Tangani Error dari Google OAuth Dialog
    if (isset($_GET['error'])) {
        $err = $_GET['error'];
        $desc = $_GET['error_description'] ?? 'Autentikasi Google dibatalkan.';
        failLogin("Autentikasi Google gagal: {$desc}");
    }

    // 2. Alur A: Server-Side Authorization Code Flow (GET ?code=...&state=...)
    if (isset($_GET['code'])) {
        $state = $_GET['state'] ?? '';
        $savedState = $_SESSION['google_oauth_state'] ?? '';

        // Validasi CSRF State Token
        if (empty($state) || empty($savedState) || !hash_equals($savedState, $state)) {
            failLogin('Validasi token keamanan (CSRF State) gagal. Silakan ulangi login.');
        }
        unset($_SESSION['google_oauth_state']);

        $tokenData = exchangeGoogleCode($_GET['code']);
        if (!$tokenData || empty($tokenData['access_token'])) {
            failLogin('Gagal menukar kode otorisasi dengan token akses Google.');
        }

        $userInfo = getGoogleUserInfo($tokenData['access_token']);
        if (!$userInfo || empty($userInfo['email'])) {
            failLogin('Gagal mengambil data profil dari akun Google Anda.');
        }

        $googleUserData = $userInfo;
    }

    // 3. Alur B: Google Identity Services (GIS) / One-Tap (POST credential)
    elseif (isset($_POST['credential'])) {
        $idToken = trim($_POST['credential']);
        $payload = verifyGoogleIdToken($idToken);

        if (!$payload || empty($payload['email'])) {
            failLogin('Token identitas Google (ID Token) tidak valid atau telah kedaluwarsa.');
        }

        $googleUserData = $payload;
    }

    // Jika tidak ada data otorisasi yang diterima
    else {
        failLogin('Permintaan login tidak valid. Tidak ada kode atau kredensial yang diterima.');
    }

    // 4. Cari atau Buat Akun Pengguna di Basis Data MySQL
    $userRecord = findOrCreateGoogleUser($googleUserData);

    // 5. Inisialisasi Sesi Login Aman (Cegah Session Fixation)
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['user'] = [
        'id'            => $userRecord['id'],
        'name'          => $userRecord['name'],
        'email'         => $userRecord['email'],
        'avatar'        => $userRecord['avatar'] ?? null,
        'auth_provider' => 'google'
    ];

    // Simpan token user untuk sinkronisasi LocalStorage frontend
    $redirectUser = $_SESSION['user'];

} catch (Throwable $e) {
    error_log('[Google Callback Fatal Exception] ' . $e->getMessage());
    failLogin('Terjadi kesalahan internal saat memproses autentikasi Google.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Mengalihkan ke Dashboard...</title>
    <script>
        // Sinkronisasi data autentikasi ke LocalStorage untuk UI TaskTrack
        const authData = <?= json_encode($redirectUser ?? []) ?>;
        if (authData && authData.email) {
            localStorage.setItem('tasktrack_agro_auth', JSON.stringify(authData));
        }
        window.location.href = '../dashboard.php';
    </script>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #0c0e14; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0;">
    <div style="text-align: center;">
        <div style="width: 36px; height: 36px; border: 3px solid rgba(255,255,255,0.15); border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 1rem;"></div>
        <p style="font-size: 0.9rem; color: #94a3b8;">Berhasil masuk dengan Google. Mengalihkan ke dashboard...</p>
    </div>
    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</body>
</html>

