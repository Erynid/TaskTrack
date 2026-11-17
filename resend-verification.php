<?php
/**
 * TaskTrack - Resend Verification Email
 * Allows users to request a fresh activation link if the previous one expired or was not received.
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

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$email_input = trim($_GET['email'] ?? '');
$success_message = '';
$error_message = '';
$preview_url = '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $submitted_token)) {
        $error_message = 'Validasi keamanan gagal atau sesi Anda telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $email_input = $email;

        if (empty($email)) {
            $error_message = 'Alamat email wajib diisi.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Format alamat email tidak valid.';
        } else {
            try {
                require_once __DIR__ . '/config/database.php';
                require_once __DIR__ . '/config/mailer.php';

                $pdo = Database::getInstance();
                $stmt = $pdo->prepare('SELECT id, username, email, email_verified_at FROM users WHERE email = :email LIMIT 1');
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$user) {
                    // Pesan aman: tidak membocorkan keberadaan user
                    $success_message = 'Jika email tersebut terdaftar di sistem kami, tautan verifikasi baru telah dikirimkan. Silakan periksa kotak masuk atau spam Anda.';
                } elseif ($user['email_verified_at'] !== null) {
                    $success_message = 'Akun Anda sudah terverifikasi sebelumnya! Silakan langsung masuk ke akun Anda.';
                } else {
                    $newToken = bin2hex(random_bytes(32));
                    $newExpires = date('Y-m-d H:i:s', time() + 86400);

                    $upd = $pdo->prepare('
                        UPDATE users 
                        SET verification_token = :token, 
                            verification_expires_at = :expires 
                        WHERE id = :id
                    ');
                    $upd->execute([
                        'token' => $newToken,
                        'expires' => $newExpires,
                        'id' => $user['id']
                    ]);

                    $mailResult = sendVerificationEmail($user['email'], $user['username'], $newToken);

                    $success_message = 'Tautan verifikasi baru berhasil dikirim ke ' . htmlspecialchars($user['email']) . '. Silakan periksa inbox atau folder spam Anda.';
                    if (!empty($mailResult['preview_url'])) {
                        $preview_url = $mailResult['preview_url'];
                    }
                }
            } catch (Throwable $e) {
                error_log('[TaskTrack Resend Verify Error] ' . $e->getMessage());
                $error_message = 'Terjadi kesalahan sistem saat memproses permintaan verifikasi. Silakan coba lagi.';
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
    <title>Kirim Ulang Verifikasi Email - TaskTrack</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <style>
        .resend-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--bg-body, #0a0c10);
            position: relative;
        }
        .resend-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-surface, rgba(18, 22, 34, 0.75));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 10;
        }
        .resend-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }
        .resend-icon-wrap {
            width: 58px;
            height: 58px;
            margin: 0 auto 1.25rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            background: rgba(56, 189, 248, 0.12);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.25);
        }
        .resend-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-primary, #ffffff);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }
        .resend-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary, #94a3b8);
            line-height: 1.5;
        }
        .resend-alert {
            padding: 0.85rem 1rem;
            border-radius: 12px;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }
        .resend-alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
        .resend-alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-secondary, #94a3b8);
            margin-bottom: 0.45rem;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.1));
            color: #ffffff;
            font-size: 0.9rem;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
            background: rgba(255, 255, 255, 0.07);
        }
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }
        .resend-footer-links {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
        }
        .resend-footer-links a {
            color: #38bdf8;
            text-decoration: none;
            font-weight: 500;
        }
        .resend-footer-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="resend-page-wrapper">
        <div class="resend-card">
            <div class="resend-header">
                <div class="resend-icon-wrap" aria-hidden="true">✉️</div>
                <h1 class="resend-title">Kirim Ulang Verifikasi</h1>
                <p class="resend-subtitle">
                    Masukkan email Anda untuk menerima tautan aktivasi baru ke kotak masuk Anda.
                </p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="resend-alert resend-alert-error" role="alert">
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="resend-alert resend-alert-success" role="alert">
                    <?= $success_message ?>
                    <?php if (!empty($preview_url)): ?>
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(110, 231, 183, 0.3); font-size: 0.825rem;">
                            <strong>Mode Pengujian:</strong> <a href="<?= htmlspecialchars($preview_url) ?>" target="_blank" style="color: #6ee7b7; text-decoration: underline; font-weight: 700;">Buka Pratinjau Email Verifikasi &rarr;</a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="resend-verification.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email Terdaftar</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        placeholder="contoh@mahasiswa.ac.id" 
                        value="<?= htmlspecialchars($email_input) ?>" 
                        required 
                        autofocus
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Kirimkan Tautan Baru &rarr;
                </button>
            </form>

            <div class="resend-footer-links">
                <a href="login.php">&larr; Kembali ke Halaman Masuk</a>
            </div>
        </div>
    </div>
</body>
</html>

