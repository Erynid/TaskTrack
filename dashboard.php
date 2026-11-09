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

// Route Guard: Wajib login untuk mengakses dashboard (Fail-Closed)
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$logged_user = $_SESSION['user'];
$user_name = htmlspecialchars($logged_user['name'] ?? 'Mahasiswa');
$user_email = htmlspecialchars($logged_user['email'] ?? '');
$user_avatar = $logged_user['avatar'] ?? ($logged_user['avatar_url'] ?? null);

// Ambil avatar dari database jika belum ada di sesi
if (empty($user_avatar)) {
    try {
        require_once __DIR__ . '/config/database.php';
        $db = Database::getInstance();
        $raw_email = trim($logged_user['email'] ?? '');
        $user_id = (int)($logged_user['id'] ?? 0);
        $st = $db->prepare('SELECT avatar_url, username FROM users WHERE (email != "" AND email = :email) OR id = :id LIMIT 1');
        $st->execute([
            'email' => $raw_email,
            'id' => $user_id
        ]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row && !empty($row['avatar_url'])) {
            $user_avatar = $row['avatar_url'];
            $_SESSION['user']['avatar'] = $user_avatar;
        }
    } catch (Throwable $e) {
        // Fallback gracefully
    }
}

$words = explode(' ', trim($logged_user['name'] ?? 'Mahasiswa'));
$initials = strtoupper(substr($words[0], 0, 1) . (isset($words[1]) ? substr($words[1], 0, 1) : ''));

require_once __DIR__ . '/components/task-card.php';
require_once __DIR__ . '/components/stat-card.php';

$app_name = "TaskTrack";
$current_year = date("Y");

$initial_tasks = [
    [
        'id' => 'task-1',
        'title' => 'Tugas Semantik HTML & Aksesibilitas Web',
        'course' => 'Pemrograman Web Dasar',
        'deadline' => date('Y-m-d\TH:i', strtotime('+3 days')),
        'status' => 'todo',
        'priority' => 'high',
        'lms_url' => 'https://elearning.kampus.ac.id',
        'lms_label' => 'LMS',
        'instructions' => 'Pastikan struktur heading bertingkat, form memiliki label terkait, dan kontras warna memenuhi standar WCAG AA.'
    ],
    [
        'id' => 'task-2',
        'title' => 'Desain Entity Relationship Diagram (ERD)',
        'course' => 'Sistem Basis Data',
        'deadline' => date('Y-m-d\TH:i', strtotime('+6 days')),
        'status' => 'todo',
        'priority' => 'low',
        'lms_url' => 'https://classroom.google.com',
        'lms_label' => 'Classroom',
        'instructions' => 'Rancang ERD sistem rekam medis klinik lengkap dengan kardinalitas 1-to-N dan relasi antar entitas.'
    ],
    [
        'id' => 'task-3',
        'title' => 'Konfigurasi Routing OSPF & Subnetting',
        'course' => 'Jaringan Komputer Lanjut',
        'deadline' => date('Y-m-d\TH:i', strtotime('+2 days')),
        'status' => 'inprogress',
        'priority' => 'medium',
        'lms_url' => 'https://elearning.kampus.ac.id',
        'lms_label' => 'LMS',
        'instructions' => 'Simulasikan di Cisco Packet Tracer dengan 3 router dan 4 subnet kelas C.'
    ],
    [
        'id' => 'task-4',
        'title' => 'Resume Materi Algoritma Dijkstra',
        'course' => 'Struktur Data & Algoritma',
        'deadline' => date('Y-m-d\TH:i', strtotime('-7 days')),
        'status' => 'done',
        'priority' => 'low',
        'lms_url' => 'https://elearning.kampus.ac.id',
        'lms_label' => 'LMS',
        'instructions' => 'Ringkas algoritma pencarian rute terpendek dengan matriks bobot berarah.'
    ],
    [
        'id' => 'task-5',
        'title' => 'Upload Revisi Laporan Praktikum Modul 1',
        'course' => 'Pemrograman Berorientasi Objek',
        'deadline' => date('Y-m-d\TH:i', strtotime('+4 hours')),
        'status' => 'overdue',
        'priority' => 'high',
        'lms_url' => 'https://elearning.kampus.ac.id',
        'lms_label' => 'Kumpulkan Segera',
        'instructions' => 'Perbaiki diagram class dan lampirkan screenshot eksekusi unit test modul 1.'
    ]
];
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $app_name ?> Studio &mdash; Pusat Kendali Tugas &amp; Akademik Mahasiswa</title>
    <meta name="description" content="Workspace studio visual pelacak tugas perkuliahan mahasiswa berstandar Apple HIG dan WCAG AA.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0a0c10">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>

<body class="studio-body">
    <a href="#main-content" class="skip-link">Loncat ke konten utama</a>
    <div id="a11y-announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <!-- APPLE WORKSPACE SHELL -->
    <div class="apple-studio-shell">
        
        <!-- ====================================================================
             A. LEFT UNIFIED APPLE SIDEBAR (COMMAND NAVIGATION RAIL)
             ==================================================================== -->
        <aside class="studio-sidebar" id="studio-sidebar" role="navigation" aria-label="Navigasi Ruang Kerja Studio">
            <!-- Sidebar Header & Brand -->
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-brand-link" aria-label="TaskTrack Beranda Studio">
                    <div class="dock-brand-icon" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <div class="sidebar-brand-meta">
                        <span class="sidebar-brand-name"><?= $app_name ?></span>
                        <span class="sidebar-brand-edition">Studio Mahasiswa</span>
                    </div>
                </a>
            </div>

            <!-- Primary Action in Sidebar: + Tambah Tugas Baru -->
            <div class="sidebar-action-wrap">
                <button type="button" class="btn-apple-sidebar-action nav-tab-btn nav-form-cta" id="nav-btn-open-form" aria-haspopup="dialog" aria-expanded="false" title="Tambah Tugas Baru (Alt + N)">
                    <span class="action-icon-pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </span>
                    <span class="action-label-text">Tugas Baru</span>
                    <kbd class="sidebar-action-kbd" hidden style="display: none;">⌘N</kbd>
                </button>
            </div>

            <!-- Workspace Views Group -->
            <div class="sidebar-nav-section">
                <span class="sidebar-section-title">Ruang Kerja</span>
                <ul class="sidebar-nav-list" role="tablist">
                    <li>
                        <button type="button" class="sidebar-nav-btn nav-tab-btn active" data-target="view-dashboard" role="tab" aria-selected="true">
                            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                                <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                                <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                            </svg>
                            <span>Dashboard</span>
                            <span class="sidebar-kbd-hint" hidden style="display: none;">⌘1</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="sidebar-nav-btn nav-tab-btn" data-target="view-tasks" role="tab" aria-selected="false">
                            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                <line x1="15" y1="3" x2="15" y2="21"></line>
                            </svg>
                            <span>Papan Tugas</span>
                            <span class="sidebar-kbd-hint" hidden style="display: none;">⌘2</span>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="sidebar-nav-btn nav-tab-btn" data-target="view-calendar" role="tab" aria-selected="false">
                            <svg class="nav-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Kalender</span>
                            <span class="sidebar-kbd-hint" hidden style="display: none;">⌘3</span>
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Campus Portal Shortcuts (Customizable per user, default empty) -->
            <div class="sidebar-nav-section" id="sidebar-portal-section">
                <div class="sidebar-section-header">
                    <span class="sidebar-section-title">Portal Perkuliahan</span>
                    <button type="button" class="btn-add-portal-trigger" id="btn-add-portal-trigger" title="Tambah Tautan Portal Baru" aria-label="Tambah portal perkuliahan">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                </div>
                <ul class="sidebar-nav-list" id="sidebar-portal-list">
                    <!-- Dinamis: Terisi sesuai link kustom pengguna (default kosong) -->
                </ul>
                <div id="portal-empty-notice" class="sidebar-portal-empty" style="display: none;">
                    <span class="empty-portal-text">Belum ada link portal.</span>
                    <button type="button" class="btn-portal-quick-add" id="btn-portal-quick-add">+ Tambah Link</button>
                </div>
            </div>

            <!-- Sidebar Bottom: User Profile & Offline Status -->
            <div class="sidebar-footer">
                <div class="sidebar-user-tile" id="btn-open-profile" role="button" tabindex="0" title="Buka Pengaturan Akun: <?= $user_name ?>">
                    <div class="sidebar-avatar"><?php if (!empty($user_avatar)): ?><img src="<?= htmlspecialchars($user_avatar) ?>" alt="<?= $user_name ?>" referrerpolicy="no-referrer" loading="eager" onerror="this.remove();" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 50%; z-index: 1;"><?php endif; ?><span><?= $initials ?></span></div>
                    <div class="sidebar-user-info">
                        <strong class="sidebar-user-name"><?= $user_name ?></strong>
                        <span style="font-size: 0.75rem; color: var(--text-muted);"><?= $user_email ?></span>
                    </div>
                </div>

                <div class="sidebar-bottom-controls">
                    <span id="footer-notif-status" class="system-status-chip">
                        <span class="status-orb" aria-hidden="true"></span>
                        <span>Lokal &bull; Siap</span>
                    </span>
                    <a href="index.php" class="sidebar-home-icon-btn" title="Kembali ke Landing Page">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </a>
                </div>
            </div>
        </aside>

        <!-- ====================================================================
             B. MAIN WORKSPACE CANVAS (TOPBAR & FLUID CONTENT AREA)
             ==================================================================== -->
        <div class="studio-main-wrapper">
            
            <!-- Top Unified Command Bar (Frosted Glass Header) -->
            <header class="studio-topbar" role="banner">
                <div class="topbar-left-zone">
                    <!-- Mobile Hamburger Toggle -->
                    <button type="button" id="btn-toggle-sidebar" class="sidebar-mobile-toggle" aria-label="Buka/Tutup Menu Samping">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    
                    <div class="topbar-breadcrumb">
                        <span class="crumb-root">TaskTrack</span>
                        <span class="crumb-slash">/</span>
                        <span class="crumb-current" id="topbar-active-view-label">Dashboard</span>
                    </div>
                </div>

                <!-- Spotlight Search Input -->
                <div class="topbar-search-zone">
                    <div class="spotlight-search-field">
                        <span class="spotlight-search-icon" aria-hidden="true">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </span>
                        <input type="search" id="search-task-input" placeholder="Cari tugas kuliah, mata kuliah, atau tag..." aria-label="Cari tugas kuliah" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                        <kbd class="spotlight-shortcut-badge" hidden style="display: none;">⌘K</kbd>
                    </div>
                </div>

                <!-- Topbar Control Cluster -->
                <div class="topbar-right-controls">
                    <!-- Quick Summary Button -->
                    <button type="button" id="btn-quick-summary" class="topbar-icon-action" title="Buka Ringkasan &amp; Analitik (Alt + S)" aria-label="Buka Ringkasan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span class="btn-text-responsive">Ringkasan</span>
                    </button>

                    <!-- Theme Toggle -->
                    <button type="button" id="btn-theme-toggle" class="topbar-icon-action" title="Ganti Tampilan (Dark / Light)" aria-label="Ganti Tema">
                        <svg class="icon-svg" id="theme-toggle-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </button>

                    <!-- Settings Trigger -->
                    <button type="button" id="btn-open-settings" class="topbar-icon-action" title="Pengaturan &amp; Notifikasi" aria-label="Pengaturan">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span id="notif-badge" class="badge-dot" title="Notifikasi Aktif"></span>
                    </button>
                </div>
            </header>

            <!-- Main Studio Content Viewport -->
            <main id="main-content" class="studio-viewport" tabindex="-1">
                
                <!-- ================================================================
                     VIEW 1: DASHBOARD (REDESIGNED TOTAL: HERO HUD + BENTO METRICS + URGENT)
                     ================================================================ -->
                <section id="view-dashboard" class="app-view-section active-view" aria-labelledby="heading-dashboard">
                    
                    <!-- 1. Academic Hero HUD Banner (Reordered to top) -->
                    <div class="academic-hero-hud">
                        <div class="hud-editorial">
                            <div class="hud-semester-badge">
                                <span class="live-pulse-dot" aria-hidden="true"></span>
                                <span>Semester Genap 2026/2027</span>
                                <span class="badge-sep">&bull;</span>
                                <span class="badge-sub">Status: Aktif</span>
                            </div>
                            <h1 class="hud-title" id="heading-dashboard">Selamat datang, <?= $user_name ?></h1>
                            <p class="hud-desc">Seluruh catatan tugas dan alur tenggat perkuliahan Anda siap ditinjau secara terstruktur.</p>
                        </div>
                        <div class="hud-action-cluster">
                            <button type="button" class="btn-apple-prominent" id="btn-new-task-trigger">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Tugas Baru</span>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Bento Metrics HUD (Apple HIG Translucent Glass Cards) -->
                    <div class="bento-metrics-row" role="region" aria-label="Ringkasan Statistik">
                        <?php
                        renderStatCard([
                            'id' => 'stat-total-tasks',
                            'label' => 'Total Tugas',
                            'value' => '5',
                            'desc' => 'Tersimpan di ruang kerja',
                            'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
                            'color' => '#f8fafc'
                        ]);
                        renderStatCard([
                            'id' => 'stat-planned-tasks',
                            'label' => 'Rencana',
                            'value' => '2',
                            'desc' => 'Menunggu dikerjakan',
                            'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>',
                            'color' => '#38bdf8'
                        ]);
                        renderStatCard([
                            'id' => 'stat-progress-tasks',
                            'label' => 'Sedang Jalan',
                            'value' => '1',
                            'desc' => 'Aktif dikerjakan',
                            'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
                            'color' => '#f59e0b'
                        ]);
                        renderStatCard([
                            'id' => 'stat-overdue-tasks',
                            'label' => 'Mendekati Tenggat',
                            'value' => '1',
                            'desc' => 'Batas waktu < 24 jam',
                            'icon_svg' => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
                            'color' => '#f43f5e'
                        ]);
                        ?>
                    </div>

                    <!-- 3. Studio Panels: Urgent Feed & Notification Control -->
                    <div class="dashboard-studio-panels">
                        <!-- Urgent Tasks Card -->
                        <div class="studio-card-panel">
                            <div class="panel-header-row">
                                <div class="panel-title-cluster">
                                    <span class="panel-icon-capsule rose-tint" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="panel-main-title">Tugas Paling Mendesak (&lt; 24 Jam)</h2>
                                        <span class="panel-sub-desc">Kumpulkan sebelum portal e-learning kampus ditutup</span>
                                    </div>
                                </div>
                            </div>
                            <div id="urgent-tasks-list" class="urgent-task-feed"></div>
                        </div>

                        <!-- Push Notification Status Card -->
                        <div class="studio-card-panel">
                            <div class="panel-header-row">
                                <div class="panel-title-cluster">
                                    <span class="panel-icon-capsule blue-tint" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                        </svg>
                                    </span>
                                    <div>
                                        <h2 class="panel-main-title">Pengingat &amp; Notifikasi Browser</h2>
                                        <span class="panel-sub-desc">Peringatan otomatis H-1 dan H-3 jam sebelum deadline</span>
                                    </div>
                                </div>
                            </div>
                            <div class="panel-body-content">
                                <p class="panel-text">
                                    Web Push Notification memberikan peringatan pop-up langsung pada peramban saat batas waktu penyerahan tugas mendekati masa kritis.
                                </p>
                                <button type="button" id="btn-dashboard-enable-notif" class="btn-apple-translucent-full">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                    <span>Aktifkan Pengingat Browser</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ================================================================
                     VIEW 2: TASKS KANBAN (REDESIGNED TOTAL: COMMAND TOOLBAR + APPLE BOARD)
                     ================================================================ -->
                <section id="view-tasks" class="app-view-section" aria-labelledby="page-title">
                    
                    <!-- Sub-header & Filter Toolbar (Redesigned) -->
                    <div class="tasks-command-toolbar">
                        <div class="toolbar-left-cluster">
                            <h1 id="page-title" class="tasks-page-title">Papan Alur Tugas</h1>
                            
                            <!-- Filter Popover Component -->
                            <div class="filter-dropdown-wrapper">
                                <button type="button" id="btn-filter-toggle" class="btn-apple-control" aria-expanded="false" aria-haspopup="true" aria-label="Filter tugas kuliah">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                    </svg>
                                    <span id="filter-current-label">Filter Mata Kuliah</span>
                                </button>
                                <div id="filter-popover" class="filter-menu-popover" role="dialog" aria-label="Menu Filter Mata Kuliah">
                                    <label for="filter-course-select" class="filter-label">Mata Kuliah / Kategori:</label>
                                    <select id="filter-course-select" class="select-apple">
                                        <option value="all">Semua Kategori</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Sort Select -->
                            <div class="sort-dropdown-wrapper">
                                <label for="sort-task-select" class="sr-only">Urutkan Berdasarkan</label>
                                <div class="select-wrapper">
                                    <select id="sort-task-select" class="select-apple select-sort" aria-label="Urutkan tugas perkuliahan">
                                        <option value="deadline-asc">Tenggat Terdekat</option>
                                        <option value="deadline-desc">Tenggat Terjauh</option>
                                        <option value="priority-desc">Prioritas Tertinggi</option>
                                        <option value="priority-asc">Prioritas Terendah</option>
                                        <option value="title-asc">Judul (A-Z)</option>
                                        <option value="title-desc">Judul (Z-A)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="toolbar-right-cluster">
                            <!-- Kanban / List Segmented Switcher -->
                            <div class="apple-segmented-control" role="tablist" aria-label="Pilih Mode Tampilan">
                                <button type="button" id="tab-view-kanban" class="seg-btn active" role="tab" aria-selected="true" aria-controls="kanban-view-container">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                        <line x1="9" y1="3" x2="9" y2="21"></line>
                                        <line x1="15" y1="3" x2="15" y2="21"></line>
                                    </svg>
                                    <span>Kanban</span>
                                </button>
                                <button type="button" id="tab-view-list" class="seg-btn" role="tab" aria-selected="false" aria-controls="list-view-container">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="8" y1="6" x2="21" y2="6"></line>
                                        <line x1="8" y1="12" x2="21" y2="12"></line>
                                        <line x1="8" y1="18" x2="21" y2="18"></line>
                                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                    </svg>
                                    <span>Daftar</span>
                                </button>
                            </div>

                            <!-- Density Segmented Switcher -->
                            <div class="apple-segmented-control density-switcher" role="group" aria-label="Pilih Kerapatan Tampilan">
                                <button type="button" id="btn-density-comfortable" class="seg-btn active" title="Kerapatan Lega" aria-pressed="true">Lega</button>
                                <button type="button" id="btn-density-compact" class="seg-btn" title="Kerapatan Padat" aria-pressed="false">Padat</button>
                            </div>
                        </div>
                    </div>

                    <!-- Apple Kanban Board Grid (4 Columns) -->
                    <div id="kanban-view-container">
                        <section class="apple-kanban-board" aria-label="Papan Alur Tugas Kanban">
                            <!-- Column 1: Planned -->
                            <div class="kanban-column" aria-labelledby="col-planned" data-status="todo">
                                <div class="kanban-column-header">
                                    <div class="col-header-left">
                                        <span class="col-status-dot dot-blue" aria-hidden="true"></span>
                                        <span class="col-title" id="col-planned">Rencana</span>
                                    </div>
                                    <span class="col-counter" id="count-todo">2</span>
                                </div>
                                <div class="column-dropzone" id="dropzone-todo" role="list" aria-label="Daftar tugas direncanakan">
                                    <?php
                                    renderTaskCard($initial_tasks[0]);
                                    renderTaskCard($initial_tasks[1]);
                                    ?>
                                </div>
                            </div>

                            <!-- Column 2: In Progress -->
                            <div class="kanban-column" aria-labelledby="col-progress" data-status="inprogress">
                                <div class="kanban-column-header">
                                    <div class="col-header-left">
                                        <span class="col-status-dot dot-amber" aria-hidden="true"></span>
                                        <span class="col-title" id="col-progress">Sedang Jalan</span>
                                    </div>
                                    <span class="col-counter" id="count-inprogress">1</span>
                                </div>
                                <div class="column-dropzone" id="dropzone-inprogress" role="list" aria-label="Daftar tugas sedang dikerjakan">
                                    <?php renderTaskCard($initial_tasks[2]); ?>
                                </div>
                            </div>

                            <!-- Column 3: Completed -->
                            <div class="kanban-column" aria-labelledby="col-completed" data-status="done">
                                <div class="kanban-column-header">
                                    <div class="col-header-left">
                                        <span class="col-status-dot dot-green" aria-hidden="true"></span>
                                        <span class="col-title" id="col-completed">Selesai</span>
                                    </div>
                                    <span class="col-counter" id="count-done">1</span>
                                </div>
                                <div class="column-dropzone" id="dropzone-done" role="list" aria-label="Daftar tugas selesai">
                                    <?php renderTaskCard($initial_tasks[3]); ?>
                                </div>
                            </div>

                            <!-- Column 4: Overdue -->
                            <div class="kanban-column" aria-labelledby="col-overdue" data-status="overdue">
                                <div class="kanban-column-header">
                                    <div class="col-header-left">
                                        <span class="col-status-dot dot-rose" aria-hidden="true"></span>
                                        <span class="col-title" id="col-overdue">Mendekati Tenggat</span>
                                    </div>
                                    <span class="col-counter counter-urgent" id="count-overdue">1</span>
                                </div>
                                <div class="column-dropzone" id="dropzone-overdue" role="list" aria-label="Daftar tugas mendesak atau terlewat">
                                    <?php renderTaskCard($initial_tasks[4]); ?>
                                </div>
                            </div>
                        </section>
                    </div>

                    <!-- Apple List View Table -->
                    <div id="list-view-container" style="display: none;" aria-label="Tampilan Daftar Tugas">
                        <div class="apple-task-table">
                            <div class="task-table-header">
                                <span>Judul Tugas</span>
                                <span>Mata Kuliah</span>
                                <span>Tenggat Waktu</span>
                                <span>Prioritas</span>
                                <span>Portal LMS</span>
                                <span style="text-align: right;">Aksi</span>
                            </div>
                            <div id="list-view-rows"></div>
                        </div>
                    </div>
                </section>

                <!-- ================================================================
                     VIEW 3: CALENDAR (REDESIGNED TOTAL: APPLE MONTH CALENDAR CARD)
                     ================================================================ -->
                <section id="view-calendar" class="app-view-section" aria-labelledby="heading-calendar">
                    <div class="calendar-studio-card">
                        <div class="calendar-header-toolbar">
                            <div class="cal-title-group">
                                <span class="cal-eyebrow">Kalender Akademik Bulanan</span>
                                <h1 id="heading-calendar" class="sr-only">Calendar</h1>
                                <h2 id="calendar-month-year" class="cal-month-heading"><?= date('F Y') ?></h2>
                            </div>
                            <div class="calendar-nav-buttons">
                                <button type="button" class="btn-apple-control" id="btn-prev-month" aria-label="Bulan sebelumnya">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="15 18 9 12 15 6"></polyline>
                                    </svg>
                                </button>
                                <button type="button" class="btn-apple-control" id="btn-today-month">Bulan Ini</button>
                                <button type="button" class="btn-apple-control" id="btn-next-month" aria-label="Bulan berikutnya">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="9 18 15 12 9 6"></polyline>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="calendar-table-grid" id="calendar-grid-cells"></div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <!-- ====================================================================
         MODAL 1: FORM TAMBAH & EDIT TUGAS (APPLE SHEET / DIALOG)
         ==================================================================== -->
    <dialog id="task-modal" class="app-dialog task-dialog" aria-labelledby="form-title" aria-modal="true">
        <div class="dialog-content task-dialog-content">
            <div class="dialog-header">
                <div>
                    <h2 id="form-title" class="dialog-title">Tambah Tugas Kuliah Baru</h2>
                    <p class="form-subtitle">Masukkan rincian penugasan serta tautan e-learning untuk akses langsung pengumpulan.</p>
                </div>
                <button type="button" id="btn-close-task-modal" class="btn-close-dialog" aria-label="Tutup dialog tugas">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="task-form" action="javascript:void(0);" novalidate autocomplete="off">
                <input type="hidden" id="task-id" name="task_id" value="">

                <div id="form-error-summary" class="form-error-summary" role="alert" aria-live="assertive" tabindex="-1" hidden>
                    <div class="error-summary-header">
                        <svg class="icon-svg error-summary-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <strong id="error-summary-title">Terdapat beberapa kesalahan pengisian formulir:</strong>
                    </div>
                    <ul id="error-summary-list" class="error-summary-list"></ul>
                </div>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Informasi Pokok Tugas</legend>
                    <div class="form-row">
                        <div class="form-field">
                            <div class="form-label-row">
                                <label for="input-judul" id="label-judul">
                                    Judul Tugas <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                            </div>
                            <input type="text" id="input-judul" name="judul" required aria-required="true"
                                aria-invalid="false" aria-describedby="error-judul"
                                placeholder="Contoh: Implementasi Normalisasi Database" maxlength="100"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <div id="error-judul" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field">
                            <div class="form-label-row">
                                <label for="input-matkul" id="label-matkul">
                                    Mata Kuliah <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                            </div>
                            <input type="text" id="input-matkul" name="matkul" list="course-suggestions" required
                                aria-required="true" aria-invalid="false" aria-describedby="error-matkul"
                                placeholder="Contoh: Pemrograman Web Dasar" maxlength="60"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <datalist id="course-suggestions">
                                <option value="Pemrograman Web Dasar">
                                <option value="Sistem Basis Data">
                                <option value="Jaringan Komputer Lanjut">
                                <option value="Struktur Data & Algoritma">
                                <option value="Pemrograman Berorientasi Objek">
                            </datalist>
                            <div id="error-matkul" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Jadwal &amp; Prioritas</legend>
                    <div class="form-row">
                        <div class="form-field" style="flex: 1.4;">
                            <div class="form-label-row">
                                <label for="input-deadline" id="label-deadline">
                                    Tenggat Waktu <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                                <span id="hint-deadline" class="field-hint">Waktu pengumpulan</span>
                            </div>
                            <input type="datetime-local" id="input-deadline" name="deadline" required
                                aria-required="true" aria-invalid="false"
                                aria-describedby="hint-deadline error-deadline" autocomplete="off">
                            <div class="deadline-preset-pills" role="group" aria-label="Pintasan cepat tenggat waktu">
                                <span class="preset-label">Pintasan:</span>
                                <button type="button" class="btn-preset-pill" data-days="1" title="Set tenggat 1 hari ke depan">+1 Hari</button>
                                <button type="button" class="btn-preset-pill" data-days="3" title="Set tenggat 3 hari ke depan">+3 Hari</button>
                                <button type="button" class="btn-preset-pill" data-days="7" title="Set tenggat 1 minggu ke depan">+1 Minggu</button>
                            </div>
                            <div id="error-deadline" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field" style="flex: 1;">
                            <div class="form-label-row">
                                <label for="input-priority" id="label-priority">Prioritas</label>
                                <span id="hint-priority" class="field-hint">Tingkat penting</span>
                            </div>
                            <select id="input-priority" name="priority" aria-describedby="hint-priority error-priority" aria-invalid="false">
                                <option value="high">Tinggi (High)</option>
                                <option value="medium" selected>Sedang (Medium)</option>
                                <option value="low">Rendah (Low)</option>
                            </select>
                            <div id="error-priority" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field" style="flex: 1;">
                            <div class="form-label-row">
                                <label for="input-status" id="label-status">Status Kolom</label>
                                <span id="hint-status" class="field-hint">Posisi alur</span>
                            </div>
                            <select id="input-status" name="status" aria-describedby="hint-status error-status" aria-invalid="false">
                                <option value="todo" selected>Rencana</option>
                                <option value="inprogress">Sedang Jalan</option>
                                <option value="done">Selesai</option>
                                <option value="overdue">Mendekati Tenggat</option>
                            </select>
                            <div id="error-status" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="form-fieldset">
                    <legend class="form-legend">Integrasi E-Learning &amp; Catatan</legend>
                    <div class="form-row">
                        <div class="form-field" style="flex: 2;">
                            <div class="form-label-row">
                                <label for="input-url" id="label-url">Link LMS / Classroom</label>
                                <span id="hint-url" class="field-hint">Opsional (http/https)</span>
                            </div>
                            <input type="url" id="input-url" name="url_pengumpulan"
                                placeholder="https://elearning.kampus.ac.id/mod/assign/..."
                                aria-describedby="hint-url error-url" aria-invalid="false"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <div id="error-url" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field" style="flex: 1;">
                            <div class="form-label-row">
                                <label for="input-url-label" id="label-url-label">Label Pintasan Tombol</label>
                                <span id="hint-url-label" class="field-hint">Maks. 20 karakter</span>
                            </div>
                            <input type="text" id="input-url-label" name="url_label"
                                placeholder="Contoh: LMS atau Classroom" maxlength="20"
                                aria-describedby="hint-url-label error-url-label" aria-invalid="false"
                                autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                            <div id="error-url-label" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field" style="grid-column: 1 / -1;">
                            <div class="form-field-header">
                                <div class="form-label-row">
                                    <label for="input-notes" id="label-notes">Catatan &amp; Instruksi Tugas</label>
                                    <span id="hint-notes" class="field-hint">Maks. 500 karakter</span>
                                </div>
                                <div class="notes-tools">
                                    <span id="notes-char-counter" class="char-counter" aria-live="polite">0 / 500</span>
                                    <button type="button" id="btn-paste-clipboard" class="btn-paste-clipboard" title="Tempel dari Clipboard">
                                        <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                             <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                        </svg>
                                        <span id="paste-clipboard-label">Tempel</span>
                                    </button>
                                </div>
                            </div>
                            <textarea id="input-notes" name="notes" rows="3"
                                placeholder="Salin instruksi atau format pengumpulan di sini..."
                                maxlength="500" aria-describedby="hint-notes error-notes notes-char-counter"
                                aria-invalid="false" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"></textarea>
                            <div id="error-notes" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions-row">
                    <button type="submit" id="btn-submit-task" class="btn-apple-prominent">
                        <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span id="save-btn-label">Simpan Kartu Tugas</span>
                    </button>
                    <button type="button" id="btn-reset-form" class="btn-apple-control">
                        Batal
                    </button>
                    <span class="form-keyboard-hint">
                        Pintasan: <kbd>Ctrl</kbd> + <kbd>Enter</kbd>
                    </span>
                </div>
            </form>
        </div>
    </dialog>

    <!-- ====================================================================
         MODAL 2: RINCIAN TUGAS (APPLE SHEET)
         ==================================================================== -->
    <dialog id="task-detail-modal" class="app-dialog task-dialog" aria-labelledby="detail-task-title" aria-modal="true">
        <div class="dialog-content task-dialog-content">
            <div class="dialog-header">
                <div>
                    <span class="detail-course-tag" id="detail-course-name">Mata Kuliah</span>
                    <h2 id="detail-task-title" class="dialog-title" style="margin-top: 0.35rem;">Judul Tugas Kuliah</h2>
                </div>
                <button type="button" id="btn-close-detail-modal" class="btn-close-dialog" aria-label="Tutup rincian tugas">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="detail-meta-grid">
                <div class="detail-meta-card">
                    <span class="meta-label">Batas Waktu</span>
                    <strong id="detail-deadline-date" class="meta-value">-</strong>
                    <span id="detail-countdown" class="meta-sub">-</span>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Prioritas</span>
                    <div id="detail-priority-badge" class="meta-badge-slot"></div>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Status Alur</span>
                    <div id="detail-status-badge" class="meta-badge-slot"></div>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Portal LMS</span>
                    <div id="detail-lms-slot" class="meta-badge-slot"></div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-section-header">
                    <h3 class="detail-section-title">Instruksi &amp; Catatan Penugasan</h3>
                    <button type="button" id="btn-copy-detail-notes" class="btn-copy-clipboard" title="Salin instruksi tugas">
                        <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span id="copy-notes-label">Salin</span>
                    </button>
                </div>
                <div id="detail-instructions-box" class="detail-notes-box">
                    Tidak ada catatan instruksi khusus.
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">Pindahkan Status Cepat</h3>
                <div class="detail-status-actions" id="detail-status-actions-container">
                    <button type="button" class="btn-status-pill" data-status="todo">Rencana</button>
                    <button type="button" class="btn-status-pill" data-status="inprogress">Sedang Jalan</button>
                    <button type="button" class="btn-status-pill" data-status="done">Selesai</button>
                    <button type="button" class="btn-status-pill" data-status="overdue">Mendekati Tenggat</button>
                </div>
            </div>

            <div class="dialog-footer-actions">
                <button type="button" id="btn-edit-from-detail" class="btn-apple-prominent">
                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit Tugas</span>
                </button>
                <button type="button" id="btn-delete-from-detail" class="btn-apple-control" style="color: #f87171; border-color: rgba(248, 113, 113, 0.35);">
                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Hapus</span>
                </button>
                <button type="button" id="btn-close-detail-secondary" class="btn-apple-control">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    <!-- ====================================================================
         MODAL 3: RINGKASAN & ANALITIK BEBAN STUDI
         ==================================================================== -->
    <dialog id="summary-modal" class="app-dialog" aria-labelledby="summary-dialog-title" aria-modal="true">
        <div class="dialog-content">
            <div class="dialog-header">
                <div>
                    <h2 id="summary-dialog-title" class="dialog-title">Ringkasan Tugas Mahasiswa</h2>
                    <p class="form-subtitle">Ikhtisar beban akademik, statistik penyelesaian, dan deadline aktif.</p>
                </div>
                <button type="button" id="btn-close-summary-modal" class="btn-close-dialog" aria-label="Tutup ringkasan">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="summary-stat-grid" id="summary-metrics-container">
                <div class="summary-stat-box">
                    <div class="summary-stat-num" id="sum-total-tasks">0</div>
                    <div class="summary-stat-lbl">Total Tugas</div>
                </div>
                <div class="summary-stat-box">
                    <div class="summary-stat-num" id="sum-completed-tasks" style="--summary-color: #10b981;">0</div>
                    <div class="summary-stat-lbl">Selesai</div>
                </div>
                <div class="summary-stat-box">
                    <div class="summary-stat-num" id="sum-rate-tasks" style="--summary-color: #f59e0b;">0%</div>
                    <div class="summary-stat-lbl">Tingkat Tuntas</div>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <h3 class="detail-section-title">Distribusi per Mata Kuliah</h3>
                <div id="summary-course-breakdown" style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 0.6rem; max-height: 220px; overflow-y: auto;">
                </div>
            </div>

            <div style="margin-top: 1.75rem; text-align: right;">
                <button type="button" id="btn-close-summary-bottom" class="btn-apple-control">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    <!-- ====================================================================
         MODAL 4: PENGATURAN & PROFIL PENGGUNA
         ==================================================================== -->
    <dialog id="settings-modal" class="app-dialog" aria-labelledby="settings-dialog-title" aria-modal="true">
        <div class="dialog-content">
            <div class="dialog-header">
                <h2 id="settings-dialog-title" class="dialog-title">Profil &amp; Preferensi Studio</h2>
                <button type="button" id="btn-close-settings" class="btn-close-dialog" aria-label="Tutup dialog">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="profile-header-card">
                <div class="sidebar-avatar" style="width: 48px; height: 48px; font-size: 1.15rem;"><?php if (!empty($user_avatar)): ?><img src="<?= htmlspecialchars($user_avatar) ?>" alt="<?= $user_name ?>" referrerpolicy="no-referrer" loading="eager" onerror="this.remove();" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border-radius: 50%; z-index: 1;"><?php endif; ?><span><?= $initials ?></span></div>
                <div>
                    <strong class="profile-user-name" id="profile-user-name"><?= $user_name ?></strong>
                    <span style="font-size: 0.8125rem; color: var(--text-muted); display: block;" id="profile-user-email"><?= $user_email ?></span>
                </div>
            </div>

            <div class="auth-divider">
                <span>preferensi tampilan</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem;">
                <div>
                    <label for="select-theme" style="display: block; font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.35rem;">Tema Tampilan:</label>
                    <select id="select-theme" class="select-apple" style="width: 100%;">
                        <option value="dark">Dark Appearance (Default)</option>
                        <option value="light">Light Appearance</option>
                    </select>
                </div>

                <div>
                    <label for="select-density" style="display: block; font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.35rem;">Kerapatan Kartu (Density):</label>
                    <select id="select-density" class="select-apple" style="width: 100%;">
                        <option value="comfortable">Lega (Comfortable)</option>
                        <option value="compact">Padat (Compact)</option>
                    </select>
                </div>
            </div>

            <div class="auth-divider">
                <span>preferensi notifikasi</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.5rem;">
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.84375rem; color: var(--text-main); cursor: pointer;">
                    <input type="checkbox" id="check-enable-sound" checked style="accent-color: var(--accent-primary);">
                    Bunyikan suara saat ada tugas &lt; 24 jam
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.84375rem; color: var(--text-main); cursor: pointer;">
                    <input type="checkbox" id="check-auto-archive" style="accent-color: var(--accent-primary);">
                    Arsipkan otomatis tugas yang sudah selesai
                </label>
            </div>

            <div style="padding-top: 1rem; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center;">
                <a href="index.php" style="color: var(--text-muted); font-size: 0.8125rem; text-decoration: none;">&larr; Landing Page</a>
                <a href="logout.php" class="btn-apple-control" style="color: #f43f5e; text-decoration: none; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.8125rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Keluar Akun</span>
                </a>
            </div>
        </div>
    </dialog>

    <!-- ====================================================================
         MODAL 5: TAMBAH PORTAL PERKULIAHAN KUSTOM
         ==================================================================== -->
    <dialog id="portal-modal" class="app-dialog" aria-labelledby="portal-dialog-title" aria-modal="true">
        <div class="dialog-content" style="max-width: 440px;">
            <div class="dialog-header">
                <div>
                    <h2 id="portal-dialog-title" class="dialog-title">Tambah Portal Perkuliahan</h2>
                    <p class="form-subtitle">Tambahkan tautan cepat ke LMS, SIAKAD, atau portal kampus Anda.</p>
                </div>
                <button type="button" id="btn-close-portal-modal" class="btn-close-dialog" aria-label="Tutup dialog portal">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="portal-form" action="javascript:void(0);" novalidate autocomplete="off">
                <div class="form-field" style="margin-bottom: 1rem;">
                    <label for="input-portal-name" style="display: block; font-size: 0.8125rem; margin-bottom: 0.35rem; color: var(--text-secondary);">
                        Nama Portal <span class="required-mark" style="color: #ef4444;">*</span>
                    </label>
                    <input type="text" id="input-portal-name" required placeholder="Contoh: Moodle, SIAKAD, Classroom" class="input-apple" style="width: 100%;" maxlength="40" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                </div>

                <div class="form-field" style="margin-bottom: 1rem;">
                    <label for="input-portal-url" style="display: block; font-size: 0.8125rem; margin-bottom: 0.35rem; color: var(--text-secondary);">
                        URL Tautan <span class="required-mark" style="color: #ef4444;">*</span>
                    </label>
                    <input type="url" id="input-portal-url" required placeholder="https://elearning.kampus.ac.id" class="input-apple" style="width: 100%;" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
                </div>

                <div class="form-field" style="margin-bottom: 1.5rem;">
                    <label for="select-portal-color" style="display: block; font-size: 0.8125rem; margin-bottom: 0.35rem; color: var(--text-secondary);">
                        Warna Indikator
                    </label>
                    <select id="select-portal-color" class="select-apple" style="width: 100%;">
                        <option value="#3b82f6">Biru (Umum)</option>
                        <option value="#f97316">Jingga (Moodle / LMS)</option>
                        <option value="#10b981">Hijau (Google Classroom)</option>
                        <option value="#8b5cf6">Ungu (SIAKAD / Akademik)</option>
                        <option value="#ec4899">Merah Muda (Perpustakaan)</option>
                    </select>
                </div>

                <div class="dialog-footer-actions" style="margin-top: 0.5rem;">
                    <button type="submit" class="btn-apple-prominent" id="btn-save-portal">
                        Simpan Portal
                    </button>
                    <button type="button" id="btn-cancel-portal" class="btn-apple-control">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- ====================================================================
         MODAL 6: KONFIRMASI HAPUS (APPLE DESTRUCTIVE ACTION SHEET)
         ==================================================================== -->
    <dialog id="confirm-modal" class="app-dialog confirm-dialog" aria-labelledby="confirm-dialog-title" aria-describedby="confirm-dialog-message" aria-modal="true">
        <div class="confirm-dialog-body">
            <div class="confirm-icon-orb" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                </svg>
            </div>
            <h3 id="confirm-dialog-title" class="confirm-title">Hapus Tugas</h3>
            <p id="confirm-dialog-message" class="confirm-desc">Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="confirm-actions">
                <button type="button" id="btn-confirm-cancel" class="btn-apple-control">Batal</button>
                <button type="button" id="btn-confirm-action" class="btn-apple-danger">Hapus Tugas</button>
            </div>
        </div>
    </dialog>

    <div id="toast-container" aria-live="polite" aria-atomic="true"></div>

    <!-- CORE SCRIPTS -->
    <script type="module" src="js/app.js?v=<?= time() ?>"></script>

    <!-- APPLE STUDIO INTERACTION ENHANCEMENTS -->
    <script>
        // 1. Mobile Sidebar Toggle
        const toggleSidebarBtn = document.getElementById('btn-toggle-sidebar');
        const studioSidebar = document.getElementById('studio-sidebar');
        if (toggleSidebarBtn && studioSidebar) {
            toggleSidebarBtn.addEventListener('click', () => {
                studioSidebar.classList.toggle('sidebar-open');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth <= 960) {
                    if (!studioSidebar.contains(e.target) && !toggleSidebarBtn.contains(e.target)) {
                        studioSidebar.classList.remove('sidebar-open');
                    }
                }
            });
        }

        // 2. Sync Topbar Context Label with Active Nav Button
        function updateTopbarLabel() {
            const activeBtn = document.querySelector('.sidebar-nav-btn.active');
            const topbarLabel = document.getElementById('topbar-active-view-label');
            if (activeBtn && topbarLabel) {
                const labelText = activeBtn.querySelector('span')?.textContent || 'Dashboard';
                topbarLabel.textContent = labelText;
            }
        }
        document.querySelectorAll('.sidebar-nav-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                setTimeout(updateTopbarLabel, 50);
            });
        });
        updateTopbarLabel();

        // 3. Apple Keyboard Shortcuts (⌘1: Dashboard, ⌘2: Tasks, ⌘3: Calendar, ⌘N: New Task, ⌘K: Search Focus)
        window.addEventListener('keydown', (e) => {
            const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
            const cmdKey = isMac ? e.metaKey : e.ctrlKey;

            if (cmdKey && e.key === '1') {
                e.preventDefault();
                document.querySelector('[data-target="view-dashboard"]')?.click();
            } else if (cmdKey && e.key === '2') {
                e.preventDefault();
                document.querySelector('[data-target="view-tasks"]')?.click();
            } else if (cmdKey && e.key === '3') {
                e.preventDefault();
                document.querySelector('[data-target="view-calendar"]')?.click();
            } else if (cmdKey && (e.key === 'n' || e.key === 'N')) {
                e.preventDefault();
                document.getElementById('nav-btn-open-form')?.click();
            } else if (cmdKey && (e.key === 'k' || e.key === 'K')) {
                e.preventDefault();
                document.getElementById('search-task-input')?.focus();
            }
        });
    </script>
</body>

</html>
