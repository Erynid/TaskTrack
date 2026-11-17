<?php
/**
 * TaskTrack - Email Verification Handler
 * Handles activation links (?token=...) sent to user email.
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

require_once __DIR__ . '/config/database.php';

$token = trim($_GET['token'] ?? '');
$status = 'unknown'; // 'success', 'expired', 'invalid'
$message = '';
$userEmail = '';

if (empty($token) || !preg_match('/^[a-f0-9]{64}$/i', $token)) {
    $status = 'invalid';
    $message = 'Format tautan aktivasi tidak valid atau parameter token tidak ditemukan.';
} else {
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT id, username, email, verification_expires_at, email_verified_at 
            FROM users 
            WHERE verification_token = :token 
            LIMIT 1
        ');
        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $status = 'invalid';
            $message = 'Tautan verifikasi tidak valid atau akun Anda telah berhasil diverifikasi sebelumnya.';
        } else {
            $userEmail = $user['email'];
            $expiresAt = strtotime($user['verification_expires_at'] ?? '0');

            if ($expiresAt < time()) {
                $status = 'expired';
                $message = 'Tautan verifikasi ini telah kedaluwarsa (masa aktif 24 jam). Silakan minta tautan verifikasi baru.';
            } else {
                // Berhasil: Aktifkan akun dan bersihkan token
                $update = $pdo->prepare('
                    UPDATE users 
                    SET email_verified_at = NOW(), 
                        verification_token = NULL, 
                        verification_expires_at = NULL 
                    WHERE id = :id
                ');
                $update->execute(['id' => $user['id']]);

                // Langsung redirect ke login dengan parameter verified=1
                header('Location: login.php?verified=1');
                exit;
            }
        }
    } catch (Throwable $e) {
        error_log('[TaskTrack Verify Error] ' . $e->getMessage());
        $status = 'invalid';
        $message = 'Terjadi kesalahan sistem saat memproses verifikasi email Anda.';
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Verifikasi Email - TaskTrack</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <style>
        .verify-page-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: var(--bg-body, #0a0c10);
            position: relative;
            overflow: hidden;
        }
        .verify-card {
            width: 100%;
            max-width: 460px;
            background: var(--bg-surface, rgba(18, 22, 34, 0.75));
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
            border-radius: 20px;
            padding: 2.25rem 2rem;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            position: relative;
            z-index: 10;
        }
        .verify-icon-wrap {
            width: 64px;
            height: 64px;
            margin: 0 auto 1.5rem;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
        }
        .icon-warning {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .icon-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .verify-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary, #f1f5f9);
            margin-bottom: 0.75rem;
            letter-spacing: -0.02em;
        }
        .verify-desc {
            font-size: 0.9rem;
            color: var(--text-secondary, #94a3b8);
            line-height: 1.55;
            margin-bottom: 1.75rem;
        }
        .verify-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn-verify-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .btn-verify-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.45);
        }
        .btn-verify-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            color: var(--text-secondary, #94a3b8);
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-subtle, rgba(255, 255, 255, 0.08));
            transition: all 0.2s ease;
        }
        .btn-verify-secondary:hover {
            background: rgba(255, 255, 255, 0.08);
            color: var(--text-primary, #ffffff);
        }
    </style>
</head>
<body>
    <div class="verify-page-wrapper">
        <div class="verify-card">
            <?php if ($status === 'expired'): ?>
                <div class="verify-icon-wrap icon-warning" aria-hidden="true">⏱️</div>
                <h1 class="verify-title">Tautan Telah Kedaluwarsa</h1>
                <p class="verify-desc"><?= htmlspecialchars($message) ?></p>
                <div class="verify-actions">
                    <a href="resend-verification.php<?= !empty($userEmail) ? '?email=' . urlencode($userEmail) : '' ?>" class="btn-verify-primary">
                        Kirim Ulang Tautan Baru &rarr;
                    </a>
                    <a href="login.php" class="btn-verify-secondary">
                        Kembali ke Halaman Masuk
                    </a>
                </div>
            <?php else: ?>
                <div class="verify-icon-wrap icon-danger" aria-hidden="true">⚠️</div>
                <h1 class="verify-title">Verifikasi Tidak Valid</h1>
                <p class="verify-desc"><?= htmlspecialchars($message) ?></p>
                <div class="verify-actions">
                    <a href="login.php" class="btn-verify-primary">
                        Menuju Halaman Masuk &rarr;
                    </a>
                    <a href="resend-verification.php" class="btn-verify-secondary">
                        Minta Tautan Verifikasi Baru
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

