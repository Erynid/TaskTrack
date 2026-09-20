<?php
/**
 * TaskTrack: Web Tracker Tugas, Jadwal, dan Proyek
 * Landing Page Responsif dan Aksesibel berbasis PHP Standar, Flexbox/Grid, dan Komponen Reusable.
 * Dibangun dengan panduan taste-skill anti-slop: tipografi terkalibrasi, layout bento, bebas em-dash, dan kustomisasi fleksibel.
 */
require_once __DIR__ . '/components/stat-card.php';
require_once __DIR__ . '/components/feature-card.php';
require_once __DIR__ . '/components/task-card.php';

$current_year = date('Y');
$page_title = "TaskTrack: Visual Task & Schedule Kanban Board";
$meta_description = "Aplikasi visual tracker dan jadwal berbasis Kanban 3 kolom fleksibel. Drag and drop interaktif, auto-sorting deadline, kategori kustom, tautan serbaguna, dan indikator warna kritis.";

// Data Awal Tugas & Jadwal (Menunjukkan Fleksibilitas Berbagai Tipe Aktivitas)
$initial_tasks = [
    [
        'id' => 'task-1',
        'title' => 'Slicing UI Dashboard dan Integrasi REST API',
        'course' => 'Proyek Web',
        'deadline' => date('Y-m-d\TH:i', strtotime('+18 hours')),
        'status' => 'todo',
        'urgency' => 'critical',
        'lms_url' => 'https://github.com/example/tasktrack-project',
        'lms_label' => 'GitHub Repo',
        'instructions' => 'Selesaikan komponen reusable, perbaiki kontras warna sesuai panduan WCAG AA, dan hubungkan data mock ke layout kartu.'
    ],
    [
        'id' => 'task-2',
        'title' => 'Normalisasi Basis Data Relasional 3NF',
        'course' => 'Sistem Basis Data',
        'deadline' => date('Y-m-d\TH:i', strtotime('+2 days 4 hours')),
        'status' => 'inprogress',
        'urgency' => 'warning',
        'lms_url' => 'https://lms.universitas.ac.id/mod/assign/view.php?id=204',
        'lms_label' => 'Portal LMS',
        'instructions' => 'Lakukan perancangan ERD dan normalisasi tabel transaksi klinik hingga bentuk 3NF beserta DDL script MySQL.'
    ],
    [
        'id' => 'task-3',
        'title' => 'Penyusunan Bab 2 Tinjauan Pustaka Skripsi',
        'course' => 'Riset Skripsi',
        'deadline' => date('Y-m-d\TH:i', strtotime('+5 days 10 hours')),
        'status' => 'inprogress',
        'urgency' => 'safe',
        'lms_url' => 'https://drive.google.com/drive/folders/sample-folder',
        'lms_label' => 'Google Drive',
        'instructions' => 'Kumpulkan 10 jurnal rujukan IEEE dan ACM tentang evaluasi UX sistem task management dan accessibility guidelines.'
    ],
    [
        'id' => 'task-4',
        'title' => 'Weekly Sync dan Sprint Review Tim',
        'course' => 'Jadwal Harian',
        'deadline' => date('Y-m-d\TH:i', strtotime('-1 day')),
        'status' => 'done',
        'urgency' => 'done',
        'lms_url' => 'https://zoom.us/j/sample123',
        'lms_label' => 'Zoom Meeting',
        'instructions' => 'Presentasi progress mingguan, review sprint backlog, dan sinkronisasi target peluncuran modul baru.'
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="theme-color" content="#4f46e5">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    
    <!-- Pencegahan Flicker Tema (Theme Flash Prevention) -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('tasktrack_theme');
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = savedTheme ? savedTheme : (prefersDark ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {}
        })();
    </script>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Stylesheet Utama (CSS Custom Properties, Flexbox, Grid, Media Queries, Focus-Visible) -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Skip to Content Link untuk Aksesibilitas Keyboard (WCAG 2.4.1) -->
    <a href="#main-content" class="skip-link">Loncat langsung ke konten utama</a>

    <!-- Notifikasi Live Region untuk Pembaca Layar (WCAG 4.1.3) -->
    <div id="a11y-announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <!-- Header Aplikasi dan Navigasi Responsif -->
    <header class="app-header" role="banner">
        <div class="header-container">
            <div class="brand-area">
                <a href="#hero" class="brand-logo" aria-label="TaskTrack: Halaman Utama">
                    <svg class="logo-icon" width="30" height="30" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
                        <rect width="32" height="32" rx="8" fill="url(#logo-grad)"/>
                        <path d="M9 16.5L14 21.5L23 10.5" stroke="white" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="logo-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#4f46e5"/>
                                <stop stop-color="#3730a3"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="brand-text">Task<strong>Track</strong></span>
                </a>
            </div>

            <!-- Tombol Toggle Menu Mobile -->
            <button type="button" id="btn-menu-toggle" class="btn-menu-toggle" aria-expanded="false" aria-controls="primary-nav" aria-label="Buka Menu Navigasi">
                <span class="hamburger-bar" aria-hidden="true"></span>
                <span class="hamburger-bar" aria-hidden="true"></span>
                <span class="hamburger-bar" aria-hidden="true"></span>
            </button>

            <!-- Navigasi Utama -->
            <nav id="primary-nav" class="app-nav" aria-label="Navigasi Utama Aplikasi">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="#hero" class="nav-link">
                            <svg class="nav-svg-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#features" class="nav-link">
                            <svg class="nav-svg-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                            <span>Fitur</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#kanban-section" class="nav-link active">
                            <svg class="nav-svg-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                <line x1="15" y1="3" x2="15" y2="21"></line>
                            </svg>
                            <span>Papan Kanban</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#task-management" class="nav-link">
                            <svg class="nav-svg-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Tambah Tugas</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Aksi Header: Pengalih Tema, Izin Notifikasi, dan Modal Login -->
            <div class="header-actions">
                <button type="button" id="btn-theme-toggle" class="btn-icon" aria-label="Ganti Tema Tampilan (Terang / Gelap)" title="Ganti Tema">
                    <svg class="icon-theme-sun" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
                    <svg class="icon-theme-moon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <button type="button" id="btn-notification-prompt" class="btn-icon" aria-label="Pengaturan Izin Web Push Notification" title="Aktifkan Web Push Notification">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span id="notif-badge" class="badge-dot" aria-label="Status notifikasi aktif"></span>
                </button>

                <div class="user-auth-widget">
                    <button type="button" id="btn-open-auth" class="btn-auth-trigger" aria-haspopup="dialog" aria-expanded="false">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span class="user-name" id="user-display-name">Masuk Akun</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Konten Utama (Main Landmark) -->
    <main id="main-content" class="app-main" tabindex="-1">
        
        <!-- SECTION 1: HERO LANDING PAGE -->
        <section id="hero" class="section-hero" aria-labelledby="heading-hero">
            <div class="section-container">
                <div class="hero-content-wrap">
                    <div class="hero-text-block">
                        <div class="hero-eyebrow">Papan Tugas & Jadwal Fleksibel</div>
                        <h1 id="heading-hero" class="hero-title">
                            Kelola jadwal, proyek, dan tugas harian tanpa hambatan.
                        </h1>
                        <p class="hero-description">
                            Papan visual Kanban fleksibel dengan kalkulasi urgensi real-time, tautan kustom serbaguna, dan notifikasi pengingat tepat waktu untuk produktivitas optimal.
                        </p>
                        
                        <div class="hero-cta-group">
                            <a href="#kanban-section" class="btn btn-primary btn-lg">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="9" y1="3" x2="9" y2="21"></line>
                                    <line x1="15" y1="3" x2="15" y2="21"></line>
                                </svg>
                                <span>Buka Papan Kanban</span>
                            </a>
                            <a href="#features" class="btn btn-secondary btn-lg">
                                <span>Pelajari Fitur</span>
                            </a>
                        </div>
                    </div>

                    <!-- Banner Opt-in Web Push Notification (Peringatan H-1 dan H-3 Jam) -->
                    <div id="notification-banner" class="notification-banner" role="region" aria-label="Pemberitahuan Izin Notifikasi">
                        <div class="notif-banner-content">
                            <div class="notif-banner-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                            </div>
                            <div class="notif-banner-text">
                                <strong>Aktifkan Peringatan Web Push Notification</strong>
                                <p>Dapatkan peringatan otomatis di browser saat tugas mendekati H-1 hari dan H-3 jam sebelum deadline.</p>
                            </div>
                        </div>
                        <div class="notif-banner-actions">
                            <button type="button" id="btn-enable-push" class="btn btn-primary btn-sm">Izinkan Notifikasi</button>
                            <button type="button" id="btn-dismiss-banner" class="btn btn-secondary btn-sm" aria-label="Tutup pemberitahuan notifikasi">Nanti Saja</button>
                        </div>
                    </div>

                    <!-- Reusable Component: Metrik Statistik Tugas (CSS Grid dan Flexbox) -->
                    <div class="metrics-grid" role="region" aria-label="Statistik Ringkasan Tugas">
                        <?php
                        renderStatCard([
                            'id' => 'stat-total-tasks',
                            'label' => 'Total Tugas',
                            'value' => '4',
                            'desc' => 'Tersimpan di sistem',
                            'type' => 'total'
                        ]);
                        renderStatCard([
                            'id' => 'stat-urgent-tasks',
                            'label' => 'Kritis (< 24 Jam)',
                            'value' => '1',
                            'desc' => 'Perlu segera diselesaikan',
                            'type' => 'urgent',
                            'color_class' => 'text-danger'
                        ]);
                        renderStatCard([
                            'id' => 'stat-progress-tasks',
                            'label' => 'Sedang Berjalan',
                            'value' => '2',
                            'desc' => 'Dalam pengerjaan',
                            'type' => 'progress',
                            'color_class' => 'text-warning'
                        ]);
                        renderStatCard([
                            'id' => 'stat-done-tasks',
                            'label' => 'Tugas Selesai',
                            'value' => '1',
                            'desc' => 'Tuntas dikumpulkan',
                            'type' => 'done',
                            'color_class' => 'text-success'
                        ]);
                        ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- SECTION 2: FITUR UNGGULAN (Landing Page Bento Grid) -->
        <section id="features" class="section-features" aria-labelledby="heading-features">
            <div class="section-container">
                <div class="section-header-wrap">
                    <h2 id="heading-features" class="section-heading">Fitur yang Membantu Anda Selesai Tepat Waktu</h2>
                    <p class="section-subtext">Alur kerja terstruktur untuk mengurangi friksi dalam melacak tugas, proyek, dan mengantisipasi batas waktu.</p>
                </div>

                <!-- Bento Grid: Reusable FeatureCard Components -->
                <div class="features-bento-grid">
                    <?php
                    // Cell 1: Wide hero bento (Visual variation: highlight)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',
                        'title' => 'Papan Kanban 3 Kolom Fleksibel',
                        'desc' => 'Kelola alur kerja dari Belum Dimulai, Sedang Dikerjakan, hingga Selesai dengan interaksi drag and drop yang halus atau tombol pindah cepat.',
                        'tag' => 'Alur Visual',
                        'tag_class' => 'tag-primary',
                        'col_span' => 'bento-col-2 bento-highlight'
                    ]);

                    // Cell 2
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>',
                        'title' => 'Indikator Kritis Waktu Real-Time',
                        'desc' => 'Warna kartu berubah otomatis sesuai sisa waktu (merah di bawah 24 jam, kuning di bawah 3 hari, hijau di atas 3 hari, abu-abu selesai).',
                        'tag' => 'Otomasi Status',
                        'tag_class' => 'tag-danger'
                    ]);

                    // Cell 3 (Visual variation: warning amber tint)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>',
                        'title' => 'Web Push Notification H-1 dan H-3 Jam',
                        'desc' => 'Peringatan pop-up browser sebelum waktu pengumpulan berakhir agar tidak ada tugas atau jadwal penting yang terlewat.',
                        'tag' => 'Pengingat Aktif',
                        'tag_class' => 'tag-warning',
                        'col_span' => 'bento-tinted-warning'
                    ]);

                    // Cell 4 (Visual variation: success emerald tint)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
                        'title' => 'Pintasan Tautan Kustom & Multi-Platform',
                        'desc' => 'Lampirkan tautan Google Drive, Zoom, GitHub, LMS, Notion, atau Figma langsung dengan label tombol yang bisa Anda atur sendiri.',
                        'tag' => 'Tautan Bebas',
                        'tag_class' => 'tag-success',
                        'col_span' => 'bento-tinted-success'
                    ]);

                    // Cell 5: Wide bento (Visual variation: subtle elevated)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>',
                        'title' => 'Quick Input Catatan, Agenda, & Instruksi',
                        'desc' => 'Salin instruksi tugas panjang, checklist to-do, atau catatan meeting dari clipboard ke kartu tugas dalam sekali klik.',
                        'tag' => 'Input Cepat',
                        'tag_class' => 'tag-primary',
                        'col_span' => 'bento-col-2 bento-subtle'
                    ]);
                    ?>
                </div>
            </div>
        </section>

        <!-- SECTION 3: LIVE KANBAN WORKSPACE SECTION -->
        <section id="kanban-section" class="section-kanban" aria-labelledby="heading-kanban">
            <div class="section-container">
                <div class="section-title-wrap">
                    <div>
                        <h2 id="heading-kanban" class="section-heading">Papan Kanban Alur Kerja Tugas</h2>
                        <p class="section-subtext">Pindahkan kartu tugas antar kolom dengan drag and drop atau tombol aksi. Tugas terdekat otomatis terurut di posisi teratas.</p>
                    </div>

                    <!-- Petunjuk Warna Indikator Kritis -->
                    <div class="urgency-legend" role="note" aria-label="Keterangan Indikator Urgensi Deadline">
                        <span class="legend-title">Tenggat:</span>
                        <span class="legend-item"><span class="color-dot dot-critical" aria-hidden="true"></span> &lt; 24 Jam (Kritis)</span>
                        <span class="legend-item"><span class="color-dot dot-warning" aria-hidden="true"></span> &lt; 3 Hari (Perhatian)</span>
                        <span class="legend-item"><span class="color-dot dot-safe" aria-hidden="true"></span> &gt; 3 Hari (Aman)</span>
                        <span class="legend-item"><span class="color-dot dot-done" aria-hidden="true"></span> Selesai</span>
                    </div>
                </div>

                <!-- Kontrol Filter dan Pencarian -->
                <div class="filter-toolbar">
                    <form id="filter-form" class="filter-controls" role="search" aria-label="Filter Kategori dan Cari Tugas" onsubmit="event.preventDefault();">
                        <div class="filter-group">
                            <label for="filter-course-select" class="filter-label">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                                <span>Kategori:</span>
                            </label>
                            <select id="filter-course-select" class="form-select filter-select">
                                <option value="all">Semua Kategori</option>
                            </select>
                        </div>

                        <div class="filter-group search-group">
                            <label for="search-task-input" class="filter-label">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <span>Cari:</span>
                            </label>
                            <input type="search" id="search-task-input" class="form-input search-input" placeholder="Cari judul, materi, atau catatan...">
                        </div>

                        <div class="filter-actions">
                            <a href="#task-management" class="btn btn-primary btn-add-shortcut">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                                <span>Tambah Tugas</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- 3 Kolom Papan Kanban (CSS Grid dan Reusable TaskCard Components) -->
                <div class="kanban-grid" role="region" aria-label="Kolom-kolom Kanban">
                    
                    <!-- Kolom 1: To Do -->
                    <div class="kanban-column column-todo" data-status="todo" id="col-todo" aria-labelledby="col-title-todo">
                        <header class="column-header">
                            <div class="col-title-badge">
                                <span class="col-status-indicator indicator-todo" aria-hidden="true"></span>
                                <h3 id="col-title-todo" class="column-title">Belum Dimulai</h3>
                            </div>
                            <span class="column-counter" id="count-todo" aria-label="1 tugas di kolom belum dimulai">1</span>
                        </header>
                        <div class="column-dropzone" id="dropzone-todo" role="list" aria-label="Daftar tugas belum dimulai">
                            <?php renderTaskCard($initial_tasks[0]); ?>
                        </div>
                    </div>

                    <!-- Kolom 2: In Progress -->
                    <div class="kanban-column column-inprogress" data-status="inprogress" id="col-inprogress" aria-labelledby="col-title-inprogress">
                        <header class="column-header">
                            <div class="col-title-badge">
                                <span class="col-status-indicator indicator-inprogress" aria-hidden="true"></span>
                                <h3 id="col-title-inprogress" class="column-title">Sedang Dikerjakan</h3>
                            </div>
                            <span class="column-counter" id="count-inprogress" aria-label="2 tugas di kolom sedang dikerjakan">2</span>
                        </header>
                        <div class="column-dropzone" id="dropzone-inprogress" role="list" aria-label="Daftar tugas sedang dikerjakan">
                            <?php 
                            renderTaskCard($initial_tasks[1]); 
                            renderTaskCard($initial_tasks[2]); 
                            ?>
                        </div>
                    </div>

                    <!-- Kolom 3: Done -->
                    <div class="kanban-column column-done" data-status="done" id="col-done" aria-labelledby="col-title-done">
                        <header class="column-header">
                            <div class="col-title-badge">
                                <span class="col-status-indicator indicator-done" aria-hidden="true"></span>
                                <h3 id="col-title-done" class="column-title">Selesai</h3>
                            </div>
                            <span class="column-counter" id="count-done" aria-label="1 tugas di kolom selesai">1</span>
                        </header>
                        <div class="column-dropzone" id="dropzone-done" role="list" aria-label="Daftar tugas yang sudah selesai">
                            <?php renderTaskCard($initial_tasks[3]); ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECTION 4: FORM MANAJEMEN TUGAS (CRUD) DAN QUICK INPUT LMS -->
        <section id="task-management" class="section-management" aria-labelledby="heading-management">
            <div class="section-container">
                <div class="form-wrapper-card">
                    <header class="form-header">
                        <h2 id="heading-management" class="section-heading">Tambah dan Kustomisasi Tugas / Jadwal</h2>
                        <p class="section-subtext">Sesuaikan judul kegiatan, kategori kustom, tenggat waktu, tautan pendukung, dan catatan alur kerja Anda.</p>
                    </header>

                    <!-- Formulir Manajemen Tugas Lengkap (<form>) -->
                    <form id="task-form" class="task-form" method="post" action="javascript:void(0);" novalidate>
                        <input type="hidden" id="task-id" name="task_id" value="">

                        <fieldset class="form-fieldset">
                            <legend class="form-legend">Informasi Pokok Kegiatan</legend>

                            <div class="form-row">
                                <div class="form-field flex-2">
                                    <label for="task-title" class="field-label">
                                        <span>Judul Tugas / Jadwal</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <input type="text" id="task-title" name="task_title" class="form-input" placeholder="Contoh: Slicing UI Dashboard atau Standup Mingguan" required aria-required="true" aria-describedby="task-title-help">
                                    <small id="task-title-help" class="field-help">Tuliskan nama aktivitas, tugas, atau agenda secara jelas.</small>
                                </div>

                                <div class="form-field flex-1">
                                    <label for="task-course" class="field-label">
                                        <span>Kategori / Topik</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <input type="text" id="task-course" name="task_course" class="form-input" list="course-suggestions" placeholder="Ketik atau pilih kategori..." required aria-required="true" autocomplete="off" aria-describedby="task-course-help">
                                    <datalist id="course-suggestions">
                                        <option value="Proyek Web">
                                        <option value="Sistem Basis Data">
                                        <option value="Riset Skripsi">
                                        <option value="Jadwal Harian">
                                        <option value="Pekerjaan / Freelance">
                                        <option value="Personal">
                                    </datalist>
                                    <small id="task-course-help" class="field-help">Bebas ketik kategori sendiri atau pilih opsi yang ada.</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-field flex-1">
                                    <label for="task-deadline" class="field-label">
                                        <span>Tanggal dan Jam Deadline</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <input type="datetime-local" id="task-deadline" name="task_deadline" class="form-input" required aria-required="true" aria-describedby="task-deadline-help">
                                    <small id="task-deadline-help" class="field-help">Sistem otomatis menghitung sisa waktu dan mewarnai kartu.</small>
                                </div>

                                <div class="form-field flex-1">
                                    <label for="task-status" class="field-label">
                                        <span>Status Alur Kerja</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <select id="task-status" name="task_status" class="form-select" required>
                                        <option value="todo" selected>Belum Dimulai (To Do)</option>
                                        <option value="inprogress">Sedang Dikerjakan (In Progress)</option>
                                        <option value="done">Selesai (Done)</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="form-fieldset">
                            <legend class="form-legend">Tautan Kustom dan Catatan Pendukung</legend>

                            <div class="form-row">
                                <div class="form-field flex-2">
                                    <label for="task-lms-url" class="field-label">
                                        <span>Tautan / URL Pendukung (Opsional)</span>
                                    </label>
                                    <div class="input-with-icon">
                                        <span class="input-addon-icon" aria-hidden="true">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                            </svg>
                                        </span>
                                        <input type="url" id="task-lms-url" name="task_lms_url" class="form-input" placeholder="https://github.com/... atau https://drive.google.com/..." aria-describedby="task-lms-help">
                                    </div>
                                    <small id="task-lms-help" class="field-help">Tautan ke GitHub, Google Drive, Zoom, Figma, atau portal pengumpulan.</small>
                                </div>

                                <div class="form-field flex-1">
                                    <label for="task-lms-label" class="field-label">
                                        <span>Label Tombol Tautan</span>
                                    </label>
                                    <input type="text" id="task-lms-label" name="task_lms_label" class="form-input" list="lms-label-suggestions" placeholder="Contoh: GitHub Repo / Zoom" autocomplete="off" aria-describedby="task-lms-label-help">
                                    <datalist id="lms-label-suggestions">
                                        <option value="Buka Tautan">
                                        <option value="GitHub Repo">
                                        <option value="Google Drive">
                                        <option value="Zoom Meeting">
                                        <option value="Portal LMS">
                                        <option value="Dokumen Notion">
                                        <option value="Figma Board">
                                    </datalist>
                                    <small id="task-lms-label-help" class="field-help">Teks tombol pada kartu (default: "Buka Tautan").</small>
                                </div>
                            </div>

                            <!-- Quick Input Teks Instruksi Panjang -->
                            <div class="form-field">
                                <div class="field-label-group">
                                    <label for="task-instructions" class="field-label">
                                        <span>Quick Input Catatan, Agenda, atau Instruksi</span>
                                    </label>
                                    <button type="button" id="btn-paste-clipboard" class="btn-text-action" title="Tempel otomatis dari Clipboard">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                        </svg>
                                        <span>Salin dari Clipboard</span>
                                    </button>
                                </div>
                                <textarea id="task-instructions" name="task_instructions" class="form-textarea" rows="4" placeholder="Salin instruksi tugas, catatan to-do checklist, format dokumen, atau ringkasan meeting di sini..." aria-describedby="task-instructions-help"></textarea>
                                <small id="task-instructions-help" class="field-help">Memudahkan membaca rincian penting langsung dari kartu tanpa membuka tab lain.</small>
                            </div>
                        </fieldset>

                        <div class="form-actions">
                            <button type="submit" id="btn-save-task" class="btn btn-primary btn-lg">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                <span id="save-button-text">Simpan ke Papan Kanban</span>
                            </button>
                            <button type="button" id="btn-reset-form" class="btn btn-secondary btn-lg">
                                <span>Batal / Reset Form</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal Dialog Autentikasi Pengguna (Google SSO dan Email/Password) -->
    <dialog id="auth-modal" class="app-dialog" aria-labelledby="auth-modal-heading" aria-modal="true">
        <div class="dialog-content">
            <header class="dialog-header">
                <h2 id="auth-modal-heading" class="dialog-title">Masuk ke Akun TaskTrack</h2>
                <button type="button" id="btn-close-auth" class="btn-close-dialog" aria-label="Tutup jendela login">✕</button>
            </header>

            <div class="dialog-body">
                <p class="dialog-desc">Sinkronkan papan tugas dan notifikasi deadline tugas Anda di semua perangkat.</p>

                <!-- Tombol Google SSO -->
                <button type="button" id="btn-google-sso" class="btn-sso-google">
                    <svg class="google-icon" width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                        <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                        <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2s.7 5.5 1.9 7.9l3.7-2.9z"/>
                        <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 16.9C3.7 20.6 7.5 23.5 12 23.5z"/>
                    </svg>
                    <span>Lanjutkan dengan Google SSO</span>
                </button>

                <div class="auth-divider" role="separator">
                    <span>atau masuk dengan email</span>
                </div>

                <!-- Formulir Login Biasa -->
                <form id="auth-form" class="auth-form" method="post" action="javascript:void(0);">
                    <div class="form-field">
                        <label for="login-email" class="field-label">Alamat Email Mahasiswa</label>
                        <input type="email" id="login-email" class="form-input" placeholder="nama@mahasiswa.kampus.ac.id" required autocomplete="email">
                    </div>

                    <div class="form-field">
                        <label for="login-password" class="field-label">Kata Sandi</label>
                        <input type="password" id="login-password" class="form-input" placeholder="••••••••" required autocomplete="current-password">
                    </div>

                    <div class="form-checkbox-field">
                        <input type="checkbox" id="remember-me" name="remember_me" class="form-checkbox">
                        <label for="remember-me" class="checkbox-label">Ingat sesi login saya (Remember Me)</label>
                    </div>

                    <button type="submit" id="btn-submit-auth" class="btn btn-primary btn-block">Masuk ke Dashboard</button>
                </form>
            </div>
        </div>
    </dialog>

    <!-- Footer Aplikasi Semantik -->
    <footer class="app-footer" role="contentinfo">
        <div class="footer-container">
            <div class="footer-col footer-info">
                <div class="brand-area">
                    <span class="brand-text">Task<strong>Track</strong></span>
                </div>
                <p class="footer-desc">Aplikasi visual tracker tugas dan deadline kuliah mahasiswa dengan alur kerja Kanban 3 kolom, desain responsif multi-device, dan aksesibilitas inklusif.</p>
                <p class="copyright-text">&copy; <?php echo $current_year; ?> TaskTrack Mahasiswa. Dibangun untuk Memenuhi Tugas Individu Semester.</p>
            </div>

            <div class="footer-col footer-links">
                <h3 class="footer-heading">Navigasi Halaman</h3>
                <ul class="footer-nav-list">
                    <li><a href="#hero">Beranda dan Ringkasan</a></li>
                    <li><a href="#features">Fitur Unggulan</a></li>
                    <li><a href="#kanban-section">Papan Kanban 3 Kolom</a></li>
                    <li><a href="#task-management">Formulir Tambah Tugas</a></li>
                </ul>
            </div>

            <div class="footer-col footer-a11y">
                <h3 class="footer-heading">Panduan Pintasan Keyboard</h3>
                <ul class="keyboard-guide-list">
                    <li><kbd>Tab</kbd> : Berpindah antar tombol dan formulir</li>
                    <li><kbd>Enter</kbd> / <kbd>Spasi</kbd> : Menjalankan tombol aktif</li>
                    <li><kbd>Esc</kbd> : Menutup jendela dialog / modal</li>
                    <li><kbd>Alt</kbd> + <kbd>N</kbd> : Loncat ke Tambah Tugas</li>
                </ul>
            </div>
        </div>
    </footer>

    <!-- Script Aplikasi Frontend -->
    <script src="js/app.js"></script>
</body>
</html>
