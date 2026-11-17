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

if (isset($_GET['verified']) && $_GET['verified'] === '1') {
    $success_message = 'Alamat email Anda berhasil diverifikasi! Silakan masuk ke akun Anda.';
    $active_tab = 'login';
} elseif (isset($_GET['resent']) && $_GET['resent'] === '1') {
    $success_message = 'Tautan verifikasi baru telah dikirimkan ke email Anda. Silakan periksa inbox atau spam.';
    $active_tab = 'login';
}

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
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Format alamat email tidak valid.';
                $active_tab = 'login';
            } else {
                try {
                    require_once __DIR__ . '/config/database.php';
                    $pdo = Database::getInstance();
                    $chk = $pdo->prepare('SELECT id, username, email, password_hash, role, google_id, avatar_url, email_verified_at FROM users WHERE email = :email LIMIT 1');
                    $chk->execute(['email' => $email]);
                    $usr = $chk->fetch(PDO::FETCH_ASSOC);

                    if (!$usr) {
                        $error_message = 'Email atau kata sandi yang Anda masukkan salah.';
                        $active_tab = 'login';
                    } elseif (!password_verify($password, $usr['password_hash'] ?? '')) {
                        if (!empty($usr['google_id'])) {
                            $error_message = 'Kata sandi tidak cocok. Akun ini terdaftar via Google, silakan gunakan tombol "Masuk dengan Google".';
                        } else {
                            $error_message = 'Email atau kata sandi yang Anda masukkan salah.';
                        }
                        $active_tab = 'login';
                    } elseif ($usr['email_verified_at'] === null) {
                        // Perlindungan Akun Belum Terverifikasi
                        $encodedEmail = urlencode($usr['email']);
                        $error_message = 'Akun belum aktif. Silakan lakukan verifikasi email terlebih dahulu melalui tautan yang dikirim ke email Anda, atau <a href="resend-verification.php?email=' . $encodedEmail . '" style="color: var(--accent-primary, #38bdf8); text-decoration: underline; font-weight: 600;">Kirim Ulang Tautan Verifikasi</a>.';
                        $active_tab = 'login';
                    } else {
                        // Rehash jika algoritma default PHP diperbarui
                        if (password_needs_rehash($usr['password_hash'], PASSWORD_DEFAULT)) {
                            $newHash = password_hash($password, PASSWORD_DEFAULT);
                            $upd = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
                            $upd->execute(['hash' => $newHash, 'id' => $usr['id']]);
                        }

                        // Cegah Session Fixation & Simpan State Sesi (Modul 12 Langkah 3)
                        session_regenerate_id(true);
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                        $userRole = $usr['role'] ?? 'mahasiswa';
                        $_SESSION['user_id'] = (int)$usr['id'];
                        $_SESSION['role'] = $userRole;

                        $_SESSION['user'] = [
                            'id' => (int)$usr['id'],
                            'name' => $usr['username'] ?: 'Mahasiswa',
                            'email' => $usr['email'],
                            'role' => $userRole,
                            'avatar' => $usr['avatar_url'] ?? null
                        ];
                        $redirect_user = $_SESSION['user'];
                    }
                } catch (Throwable $e) {
                    error_log('[TaskTrack Login Error] ' . $e->getMessage());
                    $error_message = 'Terjadi kesalahan sistem saat memproses login. Silakan coba lagi.';
                    $active_tab = 'login';
                }
            }
        } elseif ($action === 'register') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';

            if (empty($name) || empty($email) || empty($password)) {
                $error_message = 'Nama lengkap, email, dan kata sandi wajib diisi.';
                $active_tab = 'register';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Format alamat email tidak valid.';
                $active_tab = 'register';
            } elseif ($password !== $password_confirm) {
                $error_message = 'Konfirmasi kata sandi tidak cocok.';
                $active_tab = 'register';
            } elseif (strlen($password) < 6) {
                $error_message = 'Kata sandi minimal harus terdiri dari 6 karakter.';
                $active_tab = 'register';
            } else {
                try {
                    require_once __DIR__ . '/config/database.php';
                    $pdo = Database::getInstance();

                    // Cek apakah email sudah terdaftar
                    $chk = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
                    $chk->execute(['email' => $email]);
                    if ($chk->fetch()) {
                        $error_message = 'Email sudah terdaftar. Silakan gunakan email lain atau langsung masuk.';
                        $active_tab = 'register';
                    } else {
                        $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
                        if (empty($baseUsername)) $baseUsername = 'user';
                        $username = $baseUsername;

                        $chkU = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :un');
                        $chkU->execute(['un' => $username]);
                        if ($chkU->fetchColumn() > 0) {
                            $username = $baseUsername . '_' . substr(bin2hex(random_bytes(2)), 0, 4);
                        }

                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $verificationToken = bin2hex(random_bytes(32));
                        $verificationExpires = date('Y-m-d H:i:s', time() + 86400);

                        $ins = $pdo->prepare('
                            INSERT INTO users (username, email, password_hash, role, email_verified_at, verification_token, verification_expires_at, created_at)
                            VALUES (:username, :email, :password_hash, :role, NULL, :token, :expires, NOW())
                        ');
                        $displayName = !empty($name) ? $name : $username;
                        $ins->execute([
                            'username' => $displayName,
                            'email' => $email,
                            'password_hash' => $hashedPassword,
                            'role' => 'mahasiswa',
                            'token' => $verificationToken,
                            'expires' => $verificationExpires
                        ]);

                        // Kirim email verifikasi
                        require_once __DIR__ . '/config/mailer.php';
                        $mailResult = sendVerificationEmail($email, $displayName, $verificationToken);

                        $success_message = 'Pendaftaran berhasil! Tautan verifikasi telah dikirimkan ke <strong>' . htmlspecialchars($email) . '</strong>.<br>Silakan periksa kotak masuk atau spam Anda untuk mengaktifkan akun.';
                        if (!empty($mailResult['preview_url'])) {
                            $success_message .= '<br><span style="display:inline-block; margin-top:8px; font-size:0.85em; opacity:0.95;"><strong>Mode Pengujian / Dev:</strong> <a href="' . htmlspecialchars($mailResult['preview_url']) . '" target="_blank" style="color: #38bdf8; text-decoration: underline; font-weight: 600;">Buka Pratinjau Email Verifikasi &rarr;</a></span>';
                        }
                        $active_tab = 'login';
                    }
                } catch (Throwable $e) {
                    error_log('[TaskTrack Register Error] ' . $e->getMessage());
                    $error_message = 'Terjadi kesalahan sistem saat mendaftarkan akun. Silakan coba lagi.';
                    $active_tab = 'register';
                }
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
    <title>TaskTrack</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
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
                    <span><?= $error_message ?></span>
                </div>
                <?php endif; ?>

                <?php if (!empty($success_message)): ?>
                <div class="auth-alert-box auth-alert-success" role="status">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span><?= $success_message ?></span>
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
