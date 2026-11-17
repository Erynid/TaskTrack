<?php
/**
 * TaskTrack - Authentication & Authorization Helper Module
 * Modul 12 OBE: Session Security, Protected Routes, & Role-Based Access Control (RBAC)
 */

if (!function_exists('initSecureSession')) {
    /**
     * Inisialisasi sesi dengan standar keamanan session cookie (HttpOnly, SameSite, Secure)
     */
    function initSecureSession(): void
    {
        if (!is_writable(session_save_path())) {
            session_save_path(sys_get_temp_dir());
        }
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps, // false pada localhost HTTP, true pada HTTPS produksi
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }
}

// Otomatis aktifkan secure session saat file di-include
initSecureSession();

if (!function_exists('getCurrentUserId')) {
    /**
     * Dapatkan ID pengguna yang sedang login
     */
    function getCurrentUserId(): ?int
    {
        if (isset($_SESSION['user_id'])) {
            return (int)$_SESSION['user_id'];
        }
        if (isset($_SESSION['user']['id'])) {
            return (int)$_SESSION['user']['id'];
        }
        return null;
    }
}

if (!function_exists('getCurrentUserRole')) {
    /**
     * Dapatkan role pengguna yang sedang login (admin / mahasiswa / viewer)
     */
    function getCurrentUserRole(): string
    {
        if (!empty($_SESSION['role'])) {
            return (string)$_SESSION['role'];
        }
        if (!empty($_SESSION['user']['role'])) {
            return (string)$_SESSION['user']['role'];
        }
        return 'viewer';
    }
}

if (!function_exists('isLoggedIn')) {
    /**
     * Cek apakah pengguna sudah login
     */
    function isLoggedIn(): bool
    {
        return getCurrentUserId() !== null;
    }
}

if (!function_exists('requireLogin')) {
    /**
     * Helper Autentikasi untuk API/Endpoint Terlindungi (Fail-Closed: 401 Unauthorized)
     */
    function requireLogin(): array
    {
        $userId = getCurrentUserId();
        if (!$userId) {
            if (!headers_sent()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'status' => 'error',
                'error' => 'Silakan login',
                'code' => 401,
                'message' => 'Autentikasi diperlukan untuk mengakses resource ini.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return [
            'user_id' => $userId,
            'role' => getCurrentUserRole()
        ];
    }
}

if (!function_exists('requireRole')) {
    /**
     * Helper Otorisasi Role-Based Access Control (Fail-Closed: 403 Forbidden)
     *
     * @param array $roles Daftar role yang diizinkan, misal: ['admin', 'mahasiswa']
     */
    function requireRole(array $roles): void
    {
        // 1. Pastikan pengguna terautentikasi dahulu (401 jika belum)
        $auth = requireLogin();
        $userRole = $auth['role'];

        // 2. Periksa apakah role pengguna tercantum pada whitelist role yang diizinkan
        if (!in_array($userRole, $roles, true)) {
            if (!headers_sent()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode([
                'status' => 'error',
                'error' => 'Akses ditolak',
                'code' => 403,
                'message' => 'Akses ditolak: role Anda (' . $userRole . ') tidak memiliki hak izin untuk melakukan aksi ini.',
                'allowed_roles' => $roles
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
}

if (!function_exists('requireWebLogin')) {
    /**
     * Route Guard untuk halaman web reguler (alihkan ke login jika belum ada sesi)
     */
    function requireWebLogin(): void
    {
        if (!isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }
}

if (!function_exists('performSecureLogout')) {
    /**
     * Alur pembersihan sesi logout yang aman (Modul 12 Langkah 6)
     */
    function performSecureLogout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Kosongkan variabel array $_SESSION di memori server
        $_SESSION = [];

        // 2. Hapus cookie session dari browser klien dengan waktu kadaluarsa masa lalu
        if (ini_get('session.use_cookies') && !headers_sent()) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => (bool)$params['secure'],
                    'httponly' => (bool)$params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        // 3. Hancurkan data sesi di media penyimpanan server
        session_destroy();
    }
}
