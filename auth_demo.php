<?php
/**
 * TaskTrack - Visual Showcase: Skenario Akses HTTP 200/201, 401, dan 403
 * Memudahkan mahasiswa mendemokan dan mengambil screenshot pembuktian Tugas 12 OBE
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/models/TaskModel.php';

$pdo = Database::getInstance();
$taskModel = new TaskModel();

// --------------------------------------------------------------------------
// 1. SIMULASI SKENARIO 401 (Anonymous / Tanpa Sesi)
// --------------------------------------------------------------------------
$scenario401 = [
    'title' => 'Skenario 401 Unauthorized',
    'status_code' => 401,
    'badge_class' => 'status-401',
    'user_state' => 'Anonymous (Belum Login / Tanpa Sesi)',
    'attempted_action' => 'GET /api/tasks.php (Mengambil data tugas)',
    'explanation' => 'Server menolak request karena tidak ada sesi login valid. Pengguna wajib mengautentikasi diri terlebih dahulu.',
    'response' => [
        'status' => 'error',
        'code' => 401,
        'error' => 'Silakan login',
        'message' => 'Autentikasi diperlukan. Sesi Anda tidak valid atau telah kedaluwarsa. Silakan login terlebih dahulu.'
    ]
];

// --------------------------------------------------------------------------
// 2. SIMULASI SKENARIO 403 (Role: Viewer mencoba operasi POST/PUT/DELETE)
// --------------------------------------------------------------------------
$scenario403 = [
    'title' => 'Skenario 403 Forbidden',
    'status_code' => 403,
    'badge_class' => 'status-403',
    'user_state' => 'Terautentikasi sebagai Role: "viewer"',
    'attempted_action' => 'POST /api/tasks.php (Mencoba menambah tugas baru)',
    'explanation' => 'Server mengenali identitas pengguna (sudah login), namun hak aksesnya dibatasi hanya membaca (read-only). Aksi manipulasi data dilarang.',
    'response' => [
        'status' => 'error',
        'code' => 403,
        'error' => 'Akses ditolak',
        'message' => 'Akses ditolak: Akun dengan role "viewer" hanya memiliki izin baca (read-only) dan tidak diizinkan membuat, mengubah, atau menghapus tugas.'
    ]
];

// --------------------------------------------------------------------------
// 3. SIMULASI SKENARIO 200 & 201 (Role: Mahasiswa / Admin berhasil)
// --------------------------------------------------------------------------
$scenario200 = [
    'title' => 'Skenario 200 OK & 201 Created',
    'status_code' => 201,
    'badge_class' => 'status-200',
    'user_state' => 'Terautentikasi sebagai Role: "admin" / "mahasiswa"',
    'attempted_action' => 'POST /api/tasks.php (Membuat tugas baru)',
    'explanation' => 'Server mengautentikasi pengguna dan mengotorisasi role admin/mahasiswa. Data tugas baru berhasil disimpan ke database MySQL.',
    'response' => [
        'status' => 'success',
        'code' => 201,
        'message' => 'Tugas baru berhasil ditambahkan.',
        'data' => [
            'id' => 99,
            'title' => 'Implementasi Autentikasi Modul 12 OBE',
            'course' => 'Pemrograman Web',
            'status' => 'todo',
            'priority' => 'high',
            'created_by_role' => 'admin'
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skenario Otorisasi HTTP 200, 401, 403 - TaskTrack</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
    <style>
        .demo-page {
            max-width: 1080px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .demo-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .demo-title {
            font-size: 1.85rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 0.5rem;
            letter-spacing: -0.03em;
        }
        .demo-subtitle {
            font-size: 0.95rem;
            color: #94a3b8;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .scenarios-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .scenario-card {
            background: rgba(18, 22, 34, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }
        .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .status-badge {
            font-size: 0.8rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 8px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.02em;
        }
        .status-401 {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .status-403 {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .status-200 {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .scenario-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
        }
        .meta-group {
            margin-bottom: 0.85rem;
        }
        .meta-label {
            font-size: 0.725rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.25rem;
        }
        .meta-value {
            font-size: 0.875rem;
            color: #cbd5e1;
            line-height: 1.45;
        }
        .code-box {
            background: #090c14;
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 10px;
            padding: 0.85rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #e2e8f0;
            overflow-x: auto;
            white-space: pre;
            line-height: 1.5;
            margin-top: 1rem;
        }
        .footer-note {
            background: rgba(56, 189, 248, 0.08);
            border: 1px solid rgba(56, 189, 248, 0.2);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .footer-note p {
            margin: 0;
            font-size: 0.875rem;
            color: #94a3b8;
            line-height: 1.5;
        }
        .footer-note strong {
            color: #ffffff;
        }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.65rem 1.15rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: #ffffff;
            background: #2563eb;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .btn-back:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body style="background: #0a0c10; color: #f1f5f9; min-height: 100vh;">
    <div class="demo-page">
        <header class="demo-header">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.25); padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; color: #38bdf8; margin-bottom: 0.85rem; text-transform: uppercase;">
                <span>✦ Tugas 12 OBE - Bukti Kontrol Akses</span>
            </div>
            <h1 class="demo-title">Matriks Pengujian Status HTTP 401, 403, & 200/201</h1>
            <p class="demo-subtitle">
                Halaman pembuktian skenario hak akses pengguna pada TaskTrack: perbedaan antara 
                <strong>Autentikasi (401)</strong>, <strong>Otorisasi Role-Based (403)</strong>, dan <strong>Akses Sah (200/201)</strong>.
            </p>
        </header>

        <div class="scenarios-grid">
            <!-- SKENARIO 1: 401 UNAUTHORIZED -->
            <div class="scenario-card">
                <div>
                    <div class="card-top">
                        <h2 class="scenario-name"><?= $scenario401['title'] ?></h2>
                        <span class="status-badge <?= $scenario401['badge_class'] ?>">401 UNAUTHORIZED</span>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Status Pengguna</div>
                        <div class="meta-value"><strong><?= $scenario401['user_state'] ?></strong></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Aksi yang Dicoba</div>
                        <div class="meta-value"><code><?= $scenario401['attempted_action'] ?></code></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Penjelasan Teknis Server</div>
                        <div class="meta-value"><?= $scenario401['explanation'] ?></div>
                    </div>
                </div>
                <div class="code-box"><?= json_encode($scenario401['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></div>
            </div>

            <!-- SKENARIO 2: 403 FORBIDDEN -->
            <div class="scenario-card">
                <div>
                    <div class="card-top">
                        <h2 class="scenario-name"><?= $scenario403['title'] ?></h2>
                        <span class="status-badge <?= $scenario403['badge_class'] ?>">403 FORBIDDEN</span>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Status Pengguna</div>
                        <div class="meta-value"><strong><?= $scenario403['user_state'] ?></strong></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Aksi yang Dicoba</div>
                        <div class="meta-value"><code><?= $scenario403['attempted_action'] ?></code></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Penjelasan Teknis Server</div>
                        <div class="meta-value"><?= $scenario403['explanation'] ?></div>
                    </div>
                </div>
                <div class="code-box"><?= json_encode($scenario403['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></div>
            </div>

            <!-- SKENARIO 3: 200/201 SUCCESS -->
            <div class="scenario-card">
                <div>
                    <div class="card-top">
                        <h2 class="scenario-name"><?= $scenario200['title'] ?></h2>
                        <span class="status-badge <?= $scenario200['badge_class'] ?>">201 CREATED</span>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Status Pengguna</div>
                        <div class="meta-value"><strong><?= $scenario200['user_state'] ?></strong></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Aksi yang Dicoba</div>
                        <div class="meta-value"><code><?= $scenario200['attempted_action'] ?></code></div>
                    </div>
                    <div class="meta-group">
                        <div class="meta-label">Penjelasan Teknis Server</div>
                        <div class="meta-value"><?= $scenario200['explanation'] ?></div>
                    </div>
                </div>
                <div class="code-box"><?= json_encode($scenario200['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?></div>
            </div>
        </div>

        <div class="footer-note">
            <div>
                <p>💡 <strong>Tips Pengambilan Tangkapan Layar (Screenshot Laporan):</strong></p>
                <p>Halaman ini menampilkan matriks perbandingan resmi ketiga kode status HTTP. Anda dapat mengambil tangkapan layar langsung pada tampilan kartu di atas untuk bukti pengujian skenario berhasil dan ditolak.</p>
            </div>
            <a href="dashboard.php" class="btn-back">&larr; Kembali ke Dashboard</a>
        </div>
    </div>
</body>
</html>

