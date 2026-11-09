<?php
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
header("Pragma: no-cache");
header("Expires: 0");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");

// Inisialisasi Token CSRF jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Jika sudah login, langsung alihkan ke dashboard utama
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

$app_name = "TaskTrack";
$active_tab = isset($_GET['tab']) && $_GET['tab'] === 'register' ? 'register' : 'login';
$error_message = '';
$success_message = '';
$redirect_user = null;

require_once __DIR__ . '/config/google.php';
$googleConfig = getGoogleConfig();
$googleClientId = $googleConfig['client_id'];

if (isset($_SESSION['auth_error'])) {
    $error_message = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_token)) {
        $error_message = 'Validasi keamanan (CSRF Token) gagal atau sesi Anda telah kedaluwarsa. Silakan muat ulang halaman.';
        $active_tab = $_POST['action'] ?? 'login';
    } else {
        $action = $_POST['action'] ?? 'login';

        if ($action === 'login') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $error_message = 'Email dan kata sandi wajib diisi.';
                $active_tab = 'login';
            } else {
                $name = 'Pengguna';
                if (strpos($email, '@') !== false) {
                    $parts = explode('@', $email);
                    $name = ucwords(str_replace(['.', '_', '-'], ' ', $parts[0]));
                } else {
                    $name = 'Pengguna ' . htmlspecialchars($email);
                }

                // Cegah Session Fixation
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $avatar_url = null;
                $user_id = 1;
                try {
                    require_once __DIR__ . '/config/database.php';
                    $pdo = Database::getInstance();
                    $chk = $pdo->prepare('SELECT id, username, avatar_url FROM users WHERE email = :email LIMIT 1');
                    $chk->execute(['email' => $email]);
                    $usr = $chk->fetch(PDO::FETCH_ASSOC);
                    if ($usr) {
                        $user_id = (int)$usr['id'];
                        if (!empty($usr['username']) && $name === 'Pengguna') {
                            $name = $usr['username'];
                        }
                        $avatar_url = $usr['avatar_url'] ?? null;
                    }
                } catch (Throwable $e) {
                    // Fallback gracefully
                }

                $_SESSION['user'] = [
                    'id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'avatar' => $avatar_url
                ];
                $redirect_user = $_SESSION['user'];
            }
        } elseif ($action === 'register') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                $error_message = 'Nama lengkap, email, dan kata sandi wajib diisi.';
                $active_tab = 'register';
            } elseif ($password !== $password_confirm) {
                $error_message = 'Konfirmasi kata sandi tidak cocok.';
                $active_tab = 'register';
            } elseif (strlen($password) < 6) {
                $error_message = 'Kata sandi minimal harus terdiri dari 6 karakter.';
                $active_tab = 'register';
            } else {
                // Cegah Session Fixation
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                $avatar_url = null;
                $user_id = 1;
                try {
                    require_once __DIR__ . '/config/database.php';
                    $pdo = Database::getInstance();
                    $chk = $pdo->prepare('SELECT id, username, avatar_url FROM users WHERE email = :email LIMIT 1');
                    $chk->execute(['email' => $email]);
                    $usr = $chk->fetch(PDO::FETCH_ASSOC);
                    if ($usr) {
                        $user_id = (int)$usr['id'];
                        if (!empty($usr['username']) && $name === 'Pengguna') {
                            $name = $usr['username'];
                        }
                        $avatar_url = $usr['avatar_url'] ?? null;
                    }
                } catch (Throwable $e) {
                    // Fallback gracefully
                }

                $_SESSION['user'] = [
                    'id' => $user_id,
                    'name' => $name,
                    'email' => $email,
                    'avatar' => $avatar_url
                ];
                $redirect_user = $_SESSION['user'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk / Daftar Akun - <?= $app_name ?></title>
    <meta name="description" content="Halaman autentikasi masuk dan registrasi akun mahasiswa TaskTrack.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <!-- Google Identity Services (GIS) Client Library -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <?php if ($redirect_user): ?>
    <script>
        const authData = <?= json_encode($redirect_user) ?>;
        localStorage.setItem('tasktrack_agro_auth', JSON.stringify(authData));
        window.location.href = 'dashboard.php';
    </script>
    <?php endif; ?>
</head>

<body>
    <div class="auth-wrapper">
        <div class="auth-container">
            <div class="auth-brand-header">
                <a href="index.php" class="auth-brand-logo" aria-label="Kembali ke Beranda TaskTrack">
                    <div class="brand-icon" aria-hidden="true">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span><?= $app_name ?></span>
                </a>
                <div>
                    <a href="index.php" class="auth-back-link">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Kembali ke Landing Page</span>
                    </a>
                </div>
            </div>

            <div class="auth-card">
                <div class="auth-tabs-nav" role="tablist">
                    <button type="button" class="auth-tab-btn <?= $active_tab === 'login' ? 'active' : '' ?>" id="btn-tab-login" role="tab" aria-selected="<?= $active_tab === 'login' ? 'true' : 'false' ?>">
                        Masuk
                    </button>
                    <button type="button" class="auth-tab-btn <?= $active_tab === 'register' ? 'active' : '' ?>" id="btn-tab-register" role="tab" aria-selected="<?= $active_tab === 'register' ? 'true' : 'false' ?>">
                        Daftar Akun
                    </button>
                </div>

                <?php if (!empty($error_message)): ?>
                <div class="auth-alert-box auth-alert-error" role="alert">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= htmlspecialchars($error_message) ?></span>
                </div>
                <?php endif; ?>

                <?php if (isset($_GET['logged_out'])): ?>
                <div class="auth-alert-box auth-alert-success" role="status">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Anda telah berhasil keluar dari akun.</span>
                </div>
                <?php endif; ?>

                <!-- Form Login -->
                <form id="form-login" action="login.php" method="POST" style="display: <?= $active_tab === 'login' ? 'block' : 'none' ?>;">
                    <input type="hidden" name="action" value="login">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="auth-form-field">
                        <label for="login-email" class="auth-label">Email</label>
                        <input type="email" id="login-email" name="email" class="auth-input" placeholder="contoh: nama@email.com" required autofocus>
                    </div>

                    <div class="auth-form-field">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.45rem;">
                            <label for="login-password" class="auth-label" style="margin-bottom: 0;">Kata Sandi</label>
                            <a href="javascript:void(0)" onclick="alert('Silakan hubungi administrator jika Anda lupa kata sandi.')" style="font-size: 0.78rem; color: var(--accent-primary); text-decoration: none;">Lupa sandi?</a>
                        </div>
                        <input type="password" id="login-password" name="password" class="auth-input" placeholder="Masukkan kata sandi" required>
                    </div>

                    <button type="submit" class="auth-btn-submit">Masuk ke Dashboard</button>

                    <div class="auth-divider" style="margin: 1.5rem 0;">
                        <span>atau masuk dengan</span>
                    </div>

                    <a href="auth/google.php" id="btn-google-login" class="btn-sso-google" style="width: 100%; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 0.65rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z" />
                            <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z" />
                            <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2s.7 5.5 1.9 7.9l3.7-2.9z" />
                            <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 16.9C3.7 20.6 7.5 23.5 12 23.5z" />
                        </svg>
                        <span>Masuk dengan Google</span>
                    </a>
                </form>

                <!-- Form Register -->
                <form id="form-register" action="login.php?tab=register" method="POST" style="display: <?= $active_tab === 'register' ? 'block' : 'none' ?>;">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                    <div class="auth-form-field">
                        <label for="reg-name" class="auth-label">Nama Lengkap</label>
                        <input type="text" id="reg-name" name="name" class="auth-input" placeholder="Contoh: Alex Pratama" required>
                    </div>

                    <div class="auth-form-field">
                        <label for="reg-email" class="auth-label">Email</label>
                        <input type="email" id="reg-email" name="email" class="auth-input" placeholder="nama@email.com" required>
                    </div>

                    <div class="auth-form-field">
                        <label for="reg-password" class="auth-label">Kata Sandi Baru</label>
                        <input type="password" id="reg-password" name="password" class="auth-input" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>

                    <div class="auth-form-field">
                        <label for="reg-confirm" class="auth-label">Ulangi Kata Sandi</label>
                        <input type="password" id="reg-confirm" name="password_confirm" class="auth-input" placeholder="Ketik ulang kata sandi" required minlength="6">
                    </div>

                    <button type="submit" class="auth-btn-submit">Daftar Akun Mahasiswa</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const btnTabLogin = document.getElementById('btn-tab-login');
        const btnTabRegister = document.getElementById('btn-tab-register');
        const formLogin = document.getElementById('form-login');
        const formRegister = document.getElementById('form-register');
        const btnGoogleLogin = document.getElementById('btn-google-login');

        btnTabLogin.addEventListener('click', () => {
            btnTabLogin.classList.add('active');
            btnTabRegister.classList.remove('active');
            formLogin.style.display = 'block';
            formRegister.style.display = 'none';
        });

        btnTabRegister.addEventListener('click', () => {
            btnTabRegister.classList.add('active');
            btnTabLogin.classList.remove('active');
            formRegister.style.display = 'block';
            formLogin.style.display = 'none';
        });

        function handleGoogleCredentialResponse(response) {
            if (response && response.credential) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'auth/google-callback.php';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'credential';
                input.value = response.credential;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }

        window.addEventListener('load', () => {
            const googleClientId = <?= json_encode($googleClientId) ?>;
            if (googleClientId && window.google && window.google.accounts && window.google.accounts.id) {
                try {
                    google.accounts.id.initialize({
                        client_id: googleClientId,
                        callback: handleGoogleCredentialResponse,
                        auto_select: false,
                        cancel_on_tap_outside: true
                    });
                    google.accounts.id.prompt();
                } catch (e) {
                    console.warn('Google Identity Services notice:', e);
                }
            }
        });
    </script>
</body>

</html>
