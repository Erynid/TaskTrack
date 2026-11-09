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

require_once __DIR__ . '/components/feature-card.php';

$app_name = "TaskTrack";
$is_logged_in = isset($_SESSION['user']);
$logged_user = $_SESSION['user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $app_name ?> &mdash; Papan Alur Tugas &amp; Tenggat Kuliah Mahasiswa</title>
    <meta name="description" content="Pusat kendali visual tugas kuliah mahasiswa. Papan Kanban interaktif, hitung mundur batas waktu presisi, dan integrasi portal e-learning kampus.">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#0a0c10">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>

<body class="site-body">
    <a href="#main-content" class="skip-link">Loncat ke konten utama</a>

    <!-- 1. APPLE FLOATING GLASS DOCK NAVBAR -->
    <div class="dock-nav-wrapper">
        <header class="apple-dock-nav" role="banner">
            <div class="nav-brand-group">
                <a href="index.php" class="dock-brand-logo" aria-label="TaskTrack Beranda">
                    <div class="dock-brand-icon" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="dock-brand-title"><?= $app_name ?></span>
                </a>
                <span class="dock-badge-sub">Akademik</span>
            </div>

            <nav class="dock-nav-links" aria-label="Navigasi Utama">
                <ul class="dock-menu-list">
                    <li><a href="#hero" class="dock-link active">Ikhtisar</a></li>
                    <li><a href="#workflow" class="dock-link">Alur Kerja</a></li>
                    <li><a href="#features" class="dock-link">Kemampuan</a></li>
                    <li><a href="#faq" class="dock-link">FAQ</a></li>
                </ul>
            </nav>

            <div class="dock-action-cluster">
                <button type="button" id="btn-theme-toggle" class="dock-icon-btn" title="Ganti Tampilan (Gelap / Terang)" aria-label="Ganti Tampilan">
                    <svg class="icon-svg" id="theme-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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

                <?php if ($is_logged_in): ?>
                    <a href="dashboard.php" class="btn-apple-prominent">Buka Dashboard &rarr;</a>
                <?php else: ?>
                    <a href="login.php" class="btn-apple-ghost">Masuk</a>
                    <a href="login.php?tab=register" class="btn-apple-prominent">Daftar Akun</a>
                <?php endif; ?>
            </div>
        </header>
    </div>

    <main id="main-content" tabindex="-1">

        <!-- 2. BOLD ASYMMETRIC STUDIO HERO SECTION -->
        <section class="hero-studio-section" id="hero" aria-labelledby="hero-title">
            <div class="site-container hero-container">
                
                <!-- Left Column: Bold Editorial Typography & Control Cluster -->
                <div class="hero-editorial-col">
                    <div class="hero-kicker-pill">
                        <span class="live-pulse-dot" aria-hidden="true"></span>
                        <span class="kicker-label">Semester Genap 2026/2027</span>
                        <span class="kicker-sep">&bull;</span>
                        <span class="kicker-sub">Sistem Manajemen Studi</span>
                    </div>

                    <h1 id="hero-title" class="hero-headline">
                        Kuasai tenggat kuliah.<br>
                        <span class="hero-headline-sub">Bekerja dengan ritme yang tenang.</span>
                    </h1>

                    <p class="hero-lead">
                        Satu pusat kendali untuk memetakan penugasan seluruh mata kuliah, menghitung mundur tenggat secara presisi, dan menghubungkan portal LMS kampus tanpa distraksi.
                    </p>

                    <div class="hero-action-row">
                        <a href="login.php?tab=register" class="btn-apple-hero-primary">
                            <span>Mulai sekarang &mdash; Gratis</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="dashboard.php" class="btn-apple-hero-translucent">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                <line x1="15" y1="3" x2="15" y2="21"></line>
                            </svg>
                            <span>Jelajahi Demo Langsung</span>
                        </a>
                    </div>

                    <!-- Micro Quick-Stats Strip -->
                    <div class="hero-quick-metrics" role="region" aria-label="Sorotan Keunggulan">
                        <div class="metric-item">
                            <span class="metric-num">100%</span>
                            <span class="metric-lbl">Privat &amp; LocalStorage</span>
                        </div>
                        <div class="metric-divider"></div>
                        <div class="metric-item">
                            <span class="metric-num">0</span>
                            <span class="metric-lbl">Iklan atau distraksi</span>
                        </div>
                        <div class="metric-divider"></div>
                        <div class="metric-item">
                            <span class="metric-num">WCAG AA</span>
                            <span class="metric-lbl">Navigasi ramah keyboard</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Studio Command Center -->
                <div class="hero-stage-col" aria-label="Pratinjau Studio Papan Alur">
                    <div class="command-center-frame">
                        <!-- Frame Header / Toolbar -->
                        <div class="command-toolbar">
                            <div class="command-tab-pills" role="tablist" aria-label="Tampilan Studio">
                                <button type="button" class="cmd-pill active" id="tab-kanban" role="tab" aria-selected="true">Papan Alur</button>
                                <button type="button" class="cmd-pill" id="tab-timeline" role="tab" aria-selected="false">Jadwal Kritis</button>
                                <button type="button" class="cmd-pill" id="tab-lms" role="tab" aria-selected="false">Portal Kampus</button>
                            </div>
                            <div class="command-toolbar-right">
                                <span class="sync-status-indicator">
                                    <span class="status-orb" aria-hidden="true"></span>
                                    <span>Tersinkronisasi</span>
                                </span>
                            </div>
                        </div>

                        <!-- Stage Body: Rich Bento Live Canvas -->
                        <div class="command-stage-body" id="stage-body">
                            <!-- Urgent Countdown Hero Tile -->
                            <div class="urgent-task-tile">
                                <div class="tile-header">
                                    <span class="urgency-badge">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        Tenggat Paling Kritis
                                    </span>
                                    <span class="tile-timestamp" id="hero-countdown-clock">03:42:18</span>
                                </div>
                                <div class="tile-content">
                                    <div class="tile-course-tag">Pemrograman Berorientasi Objek &bull; Praktikum Modul 1</div>
                                    <h3 class="tile-task-title">Upload Revisi Laporan Praktikum &amp; Unit Test</h3>
                                    <div class="tile-footer-row">
                                        <a href="https://elearning.kampus.ac.id" target="_blank" rel="noopener" class="tile-btn-lms" aria-label="Buka E-Learning Kampus">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                                                <polyline points="15 3 21 3 21 9"></polyline>
                                                <line x1="10" y1="14" x2="21" y2="3"></line>
                                            </svg>
                                            <span>Buka E-Learning</span>
                                        </a>
                                        <span class="tile-meta-note">Bobot: 15% Nilai Akhir Semester</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mini Kanban Columns Strip -->
                            <div class="kanban-preview-strip" id="kanban-view-content">
                                <!-- Column 1: Planned -->
                                <div class="strip-col">
                                    <div class="strip-col-head">
                                        <span class="col-dot dot-blue" aria-hidden="true"></span>
                                        <span class="col-name">Rencana</span>
                                        <span class="col-count">2</span>
                                    </div>
                                    <div class="strip-card">
                                        <div class="card-course">Sistem Basis Data</div>
                                        <div class="card-title">Desain ERD Relasional &amp; Normalisasi 3NF</div>
                                        <div class="card-footer">
                                            <span class="card-due">3 hari lagi</span>
                                            <span class="card-tag">Classroom</span>
                                        </div>
                                    </div>
                                    <div class="strip-card">
                                        <div class="card-course">Pemrograman Web</div>
                                        <div class="card-title">Struktur Semantik HTML &amp; Aksesibilitas Web</div>
                                        <div class="card-footer">
                                            <span class="card-due">6 hari lagi</span>
                                            <span class="card-tag">Moodle</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Column 2: In Progress -->
                                <div class="strip-col">
                                    <div class="strip-col-head">
                                        <span class="col-dot dot-amber" aria-hidden="true"></span>
                                        <span class="col-name">Sedang Dikerjakan</span>
                                        <span class="col-count">1</span>
                                    </div>
                                    <div class="strip-card active-card">
                                        <div class="card-course">Jaringan Komputer</div>
                                        <div class="card-title">Simulasi Routing OSPF Multi-Area</div>
                                        <div class="card-progress-bar" aria-label="Progres pengerjaan 65%">
                                            <div class="progress-fill" style="width: 65%;"></div>
                                        </div>
                                        <div class="card-footer">
                                            <span class="card-due due-soon">2 hari lagi</span>
                                            <span class="card-tag">Lab Cisco</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Column 3: Done -->
                                <div class="strip-col">
                                    <div class="strip-col-head">
                                        <span class="col-dot dot-green" aria-hidden="true"></span>
                                        <span class="col-name">Selesai</span>
                                        <span class="col-count">1</span>
                                    </div>
                                    <div class="strip-card done-card">
                                        <div class="card-course">Struktur Data</div>
                                        <div class="card-title">Resume Algoritma Dijkstra &amp; Jalur Terpendek</div>
                                        <div class="card-footer">
                                            <span class="card-status-done">&check; Ditandai selesai</span>
                                            <span class="card-tag">SIAKAD</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. METODOLOGI & ALUR KERJA (TIMELINE 3 LANGKAH APPLE STUDIO) -->
        <section class="section-workflow" id="workflow" aria-labelledby="workflow-title">
            <div class="site-container">
                <div class="section-header-block">
                    <span class="apple-eyebrow">Alur Kerja</span>
                    <h2 id="workflow-title" class="apple-section-title">Dari silabus padat hingga semester tuntas</h2>
                    <p class="apple-section-desc">Tiga langkah terstruktur yang memangkas kebingungan saat pekan tugas dan praktikum tiba.</p>
                </div>

                <div class="workflow-steps-layout">
                    <!-- Step 1 -->
                    <div class="step-apple-card">
                        <div class="step-badge-num" aria-hidden="true">01</div>
                        <div class="step-card-content">
                            <h3 class="step-title">Petakan Tugas &amp; Tautkan LMS</h3>
                            <p class="step-desc">Input tugas per mata kuliah beserta link pengumpulan di Google Classroom atau Moodle. Tak ada lagi mencari link tugas di riwayat chat yang tenggelam.</p>
                        </div>
                        <div class="step-card-visual">
                            <span class="v-pill">Moodle Kampus</span>
                            <span class="v-pill">Google Classroom</span>
                            <span class="v-pill">SIAKAD</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="step-apple-card">
                        <div class="step-badge-num" aria-hidden="true">02</div>
                        <div class="step-card-content">
                            <h3 class="step-title">Pantau Countdown Real-Time</h3>
                            <p class="step-desc">Tenggat waktu dihitung mundur secara dinamis. Tugas kritis berpenanda khusus muncul di baris teratas agar Anda tahu persis apa yang harus dieksekusi hari ini.</p>
                        </div>
                        <div class="step-card-visual">
                            <div class="v-countdown-box">
                                <span class="v-countdown-label">Sisa Waktu Kritis</span>
                                <span class="v-countdown-digits">03:42:18</span>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="step-apple-card">
                        <div class="step-badge-num" aria-hidden="true">03</div>
                        <div class="step-card-content">
                            <h3 class="step-title">Perbarui Status Tanpa Gesekan</h3>
                            <p class="step-desc">Perbarui status tugas dari Rencana ke Sedang Dikerjakan hingga Tuntas. Statistik ketuntasan beban belajar Anda terakumulasi secara instan.</p>
                        </div>
                        <div class="step-card-visual">
                            <div class="v-progress-box">
                                <span class="v-stat-num">80%</span>
                                <span class="v-stat-sub">Ketuntasan Semester Ini</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. APPLE BENTO GRID (HIERARKI BERAGAM DENGAN BERBAGAI BOBOT VISUAL) -->
        <section class="section-bento-features" id="features" aria-labelledby="features-title">
            <div class="site-container">
                <div class="section-header-block">
                    <span class="apple-eyebrow">Kemampuan Utama</span>
                    <h2 id="features-title" class="apple-section-title">Semua yang Anda butuhkan untuk kelancaran studi</h2>
                    <p class="apple-section-desc">Dirancang dengan teliti mengikuti prinsip Apple Human Interface Guidelines: bersih, fokus pada esensi, dan bebas hambatan kognitif.</p>
                </div>

                <div class="apple-bento-grid">
                    
                    <!-- Bento 1: Hero Large Feature (Span 2 col) -->
                    <?php
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',
                        'title' => 'Kanban 4 Pilar yang Menghilangkan Kebingungan',
                        'desc' => 'Struktur alur empat kolom: Rencana, Sedang Berjalan, Selesai, dan Mendekati Batas Waktu. Membantu Anda fokus pada satu hal tanpa stres berlebihan.',
                        'tag' => 'Alur Kerja Visual',
                        'tag_class' => 'tag-primary',
                        'col_span' => 'bento-span-2',
                        'variant' => 'hero',
                        'visual_html' => '
                            <div class="bento-mini-kanban">
                                <div class="b-col"><span class="b-head">Rencana (2)</span><div class="b-card">Basis Data</div></div>
                                <div class="b-col active-col"><span class="b-head">Sedang Jalan (1)</span><div class="b-card">Jaringan Komputer</div></div>
                                <div class="b-col"><span class="b-head">Selesai (1)</span><div class="b-card">Struktur Data</div></div>
                            </div>
                        '
                    ]);

                    // Bento 2: Countdown & Urgent Alert (Medium)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
                        'title' => 'Hitung Mundur Presisi & Peringatan < 24 Jam',
                        'desc' => 'Perhitungan otomatis hingga ke detik. Penanda rose aksen lembut aktif saat batas waktu pengumpulan tersisa kurang dari 24 jam.',
                        'tag' => 'Ketepatan Waktu',
                        'tag_class' => 'tag-warning',
                        'col_span' => 'bento-span-1',
                        'variant' => 'medium',
                        'visual_html' => '
                            <div class="bento-pill-preview">
                                <span class="due-badge-rose">Kritis: Sisa 03 Jam 42 Menit</span>
                            </div>
                        '
                    ]);

                    // Bento 3: LMS Direct Launcher (Medium)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
                        'title' => 'Pintasan Sekali Klik ke Portal Perkuliahan',
                        'desc' => 'Simpan tautan tugas langsung ke Classroom, Moodle, atau Teams kampus. Kumpulkan tugas tanpa mencari link di riwayat browser.',
                        'tag' => 'Integrasi Kampus',
                        'tag_class' => 'tag-accent',
                        'col_span' => 'bento-span-1',
                        'variant' => 'medium',
                        'visual_html' => '
                            <div class="bento-lms-row">
                                <span class="lms-chip">Google Classroom</span>
                                <span class="lms-chip">Moodle E-Learning</span>
                            </div>
                        '
                    ]);

                    // Bento 4: Academic Calendar (Compact)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
                        'title' => 'Pemetaan Tanggal Ujian & Praktikum',
                        'desc' => 'Visualisasi kalender akademik untuk mendeteksi tanggal padat sebelum tugas menumpuk bersamaan.',
                        'tag' => 'Jadwal Bulanan',
                        'tag_class' => 'tag-primary',
                        'col_span' => 'bento-span-1',
                        'variant' => 'compact'
                    ]);

                    // Bento 5: Study Load Analytics (Compact)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
                        'title' => 'Analisis Rasio Beban Semester',
                        'desc' => 'Distribusi persentase ketuntasan per mata kuliah untuk mengukur keseimbangan waktu belajar Anda.',
                        'tag' => 'Statistik Studi',
                        'tag_class' => 'tag-success',
                        'col_span' => 'bento-span-1',
                        'variant' => 'compact'
                    ]);

                    // Bento 6: WCAG AA & Ergonomics (Span 2 / Wide)
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><circle cx="12" cy="12" r="10"></circle><path d="m4.93 4.93 4.24 4.24"></path><path d="m14.83 9.17 4.24-4.24"></path><path d="m14.83 14.83 4.24 4.24"></path><path d="m9.17 14.83-4.24 4.24"></path></svg>',
                        'title' => 'Standar Aksesibilitas WCAG 2.1 AA & Navigasi Keyboard Penuh',
                        'desc' => 'Didesain nyaman untuk mata saat belajar larut malam. Didukung screen reader live announcer, kontras teks teruji, dan navigasi ramah keyboard.',
                        'tag' => 'Standar Web & Ergonomi',
                        'tag_class' => 'tag-accent',
                        'col_span' => 'bento-span-2',
                        'variant' => 'wide',
                        'visual_html' => '
                            <div class="bento-keys-row">
                                <span class="apple-keycap">Tab</span>
                                <span class="apple-keycap">Enter</span>
                                <span class="apple-keycap">Esc</span>
                                <span class="apple-keycap">Alt + D</span>
                            </div>
                        '
                    ]);
                    ?>
                </div>
            </div>
        </section>

        <!-- 5. SECTION FAQ: APPLE SPLIT ACCORDION STYLE -->
        <section class="section-apple-faq" id="faq" aria-labelledby="faq-title">
            <div class="site-container">
                <div class="faq-container-split">
                    <div class="faq-left-column">
                        <span class="apple-eyebrow">Tanya Jawab</span>
                        <h2 id="faq-title" class="apple-section-title">Hal yang kerap ditanyakan mahasiswa</h2>
                        <p class="apple-section-desc">Punya pertanyaan seputar privasi data, biaya, atau integrasi TaskTrack? Temukan rangkuman jawabannya di sini.</p>
                        
                        <div class="faq-contact-card">
                            <div class="contact-card-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                            </div>
                            <div class="contact-card-text">
                                <strong>Ingin langsung mencoba?</strong>
                                <p>Jelajahi seluruh fitur interaktif langsung di dashboard demo tanpa perlu login.</p>
                                <a href="dashboard.php" class="contact-link">Buka Dashboard Demo &rarr;</a>
                            </div>
                        </div>
                    </div>

                    <div class="faq-right-column">
                        <details class="apple-faq-item" open>
                            <summary class="faq-summary">
                                <span>Apakah data tugas saya tetap tersimpan jika peramban ditutup?</span>
                                <span class="faq-chevron-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </span>
                            </summary>
                            <div class="faq-body">
                                <p>Ya. TaskTrack menyimpan seluruh data tugas pada penyimpanan lokal peramban (LocalStorage) sehingga data Anda tetap utuh saat halaman ditutup atau komputer dimatikan. Jika basis data MySQL aktif, data juga tersinkronisasi secara aman ke server.</p>
                            </div>
                        </details>

                        <details class="apple-faq-item">
                            <summary class="faq-summary">
                                <span>Apakah ada biaya berlangganan atau batasan jumlah tugas?</span>
                                <span class="faq-chevron-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </span>
                            </summary>
                            <div class="faq-body">
                                <p>TaskTrack 100% gratis untuk seluruh mahasiswa. Tidak ada biaya langganan berbayar, tidak ada iklan pihak ketiga, dan tidak ada batasan kuota jumlah kartu tugas yang dapat Anda tambahkan.</p>
                            </div>
                        </details>

                        <details class="apple-faq-item">
                            <summary class="faq-summary">
                                <span>Apakah saya bisa langsung mencoba tanpa mendaftar akun?</span>
                                <span class="faq-chevron-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </span>
                            </summary>
                            <div class="faq-body">
                                <p>Bisa! Anda dapat langsung membuka Dashboard Demo untuk mengeksplorasi pembuatan tugas, pemindahan kolom Kanban, dan penghitungan tenggat tanpa registrasi akun terlebih dahulu.</p>
                            </div>
                        </details>

                        <details class="apple-faq-item">
                            <summary class="faq-summary">
                                <span>Bagaimana integrasi dengan Google Classroom dan Moodle bekerja?</span>
                                <span class="faq-chevron-icon" aria-hidden="true">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </span>
                            </summary>
                            <div class="faq-body">
                                <p>Anda cukup menempelkan tautan tugas LMS pada kolom saat input kartu. Kartu tugas akan memunculkan tombol pintasan cepat sekali klik yang langsung membuka portal pengumpulan tugas kampus Anda di tab baru.</p>
                            </div>
                        </details>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. SECTION CALL TO ACTION: APPLE STUDIO MINIMALIST CARD -->
        <section class="section-studio-cta">
            <div class="site-container">
                <div class="studio-cta-card">
                    <div class="cta-inner-content">
                        <span class="cta-kicker">Mulai Sekarang</span>
                        <h2 class="cta-heading">Rapikan semester Anda dalam hitungan menit</h2>
                        <p class="cta-subtext">Bebaskan pikiran dari cemas deadline. Mulai petakan tugas kuliah Anda hari ini secara gratis langsung melalui peramban.</p>
                        <div class="cta-actions-cluster">
                            <a href="login.php?tab=register" class="btn-apple-hero-primary">
                                <span>Daftar Akun Baru Sekarang</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                            <a href="dashboard.php" class="btn-apple-hero-translucent">
                                <span>Buka Versi Demo</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- 7. SECTION FOOTER: APPLE MULTI-COLUMN MASTER FOOTER -->
    <footer class="apple-master-footer" role="contentinfo">
        <div class="site-container">
            <div class="footer-top-row">
                <div class="footer-brand-pane">
                    <div class="footer-logo">
                        <div class="dock-brand-icon" style="width: 24px; height: 24px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </div>
                        <span class="footer-brand-name"><?= $app_name ?></span>
                    </div>
                    <p class="footer-tagline">Pusat kendali alur tugas &amp; tenggat perkuliahan mahasiswa dengan standar aksesibilitas web WCAG 2.1 AA.</p>
                    <div class="footer-system-pill">
                        <span class="pulse-green-dot" aria-hidden="true"></span>
                        <span>Semua modul berjalan normal &bull; Versi 1.0</span>
                    </div>
                </div>

                <div class="footer-links-grid">
                    <div class="footer-col">
                        <span class="footer-col-title">Navigasi Halaman</span>
                        <ul class="footer-link-list">
                            <li><a href="#hero">Ikhtisar Utama</a></li>
                            <li><a href="#workflow">Alur Kerja 3 Langkah</a></li>
                            <li><a href="#features">Kemampuan Studio</a></li>
                            <li><a href="#faq">Pertanyaan Umum</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <span class="footer-col-title">Aplikasi</span>
                        <ul class="footer-link-list">
                            <li><a href="dashboard.php">Dashboard Demo</a></li>
                            <li><a href="login.php">Masuk Akun</a></li>
                            <li><a href="login.php?tab=register">Daftar Akun Baru</a></li>
                        </ul>
                    </div>
                    <div class="footer-col">
                        <span class="footer-col-title">Fokus Akademik</span>
                        <ul class="footer-link-list">
                            <li><span class="footer-static-item">Semester Genap 2026</span></li>
                            <li><span class="footer-static-item">Integrasi Moodle / LMS</span></li>
                            <li><span class="footer-static-item">Penyimpanan Offline</span></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer-bottom-bar">
                <div class="footer-copy">
                    &copy; <?= date('Y') ?> <?= $app_name ?>. Dirancang khusus untuk mahasiswa Indonesia.
                </div>
                <div class="footer-meta-shortcut">
                    Tekan <kbd class="apple-keycap-small">Alt</kbd> + <kbd class="apple-keycap-small">D</kbd> untuk akses cepat ke Dashboard
                </div>
            </div>
        </div>
    </footer>

    <!-- INTERACTIVE JAVASCRIPT LOGIC -->
    <script>
        // 1. Theme Toggle Persistence (Dark / Light)
        const themeBtn = document.getElementById('btn-theme-toggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('tasktrack_theme') || 'dark';
        html.setAttribute('data-theme', savedTheme);

        if (themeBtn) {
            themeBtn.addEventListener('click', () => {
                const current = html.getAttribute('data-theme') || 'dark';
                const next = current === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', next);
                localStorage.setItem('tasktrack_theme', next);
            });
        }

        // 2. Real-time Ticking Countdown in Hero Tile
        (function initCountdown() {
            let totalSeconds = 3 * 3600 + 42 * 60 + 18; // 03:42:18
            const clockEl = document.getElementById('hero-countdown-clock');
            if (!clockEl) return;

            setInterval(() => {
                if (totalSeconds > 0) {
                    totalSeconds--;
                    const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
                    const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
                    const s = String(totalSeconds % 60).padStart(2, '0');
                    clockEl.textContent = `${h}:${m}:${s}`;
                }
            }, 1000);
        })();

        // 3. Command Tab Switcher Demo Interaction
        const tabs = document.querySelectorAll('.cmd-pill');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => {
                    t.classList.remove('active');
                    t.setAttribute('aria-selected', 'false');
                });
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
            });
        });

        // 4. Global Keyboard Shortcut: Alt + D -> Dashboard
        window.addEventListener('keydown', (e) => {
            if (e.altKey && (e.key === 'd' || e.key === 'D')) {
                window.location.href = 'dashboard.php';
            }
        });
    </script>
</body>

</html>
