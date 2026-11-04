<?php
if (!is_writable(session_save_path())) {
    session_save_path(sys_get_temp_dir());
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

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
    <title><?= $app_name ?> - Papan Pelacak Tugas & Deadline Kuliah Mahasiswa</title>
    <meta name="description" content="Aplikasi visual pelacak tugas kuliah mahasiswa dengan alur Kanban interaktif, countdown tenggat waktu, dan integrasi LMS kampus.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css?v=<?= time() ?>">
</head>

<body>
    <a href="#main-content" class="skip-link">Loncat ke konten utama</a>

    <div class="app-window" style="max-width: 1200px;">
        <!-- Header & Navigasi Publik -->
        <header class="landing-nav" role="banner">
            <a href="index.php" class="brand-logo" aria-label="TaskTrack Beranda">
                <div class="brand-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span><?= $app_name ?></span>
            </a>

            <nav aria-label="Navigasi Landing Page">
                <ul class="landing-nav-menu">
                    <li><a href="#features" class="landing-nav-link">Fitur</a></li>
                    <li><a href="#preview" class="landing-nav-link">Pratinjau</a></li>
                    <li><a href="#faq" class="landing-nav-link">FAQ</a></li>
                </ul>
            </nav>

            <div class="landing-nav-actions">
                <button type="button" id="btn-theme-toggle" class="icon-btn" title="Ganti Tema (Dark Mode / Light Mode)" aria-label="Ganti Tema">
                    <svg class="icon-svg" id="theme-toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
                    <a href="dashboard.php" class="btn-nav-primary">Buka Dashboard &rarr;</a>
                <?php else: ?>
                    <a href="login.php" class="btn-nav-ghost">Masuk</a>
                    <a href="login.php?tab=register" class="btn-nav-primary">Daftar Akun</a>
                <?php endif; ?>
            </div>
        </header>

        <main id="main-content" tabindex="-1">

            <!-- HERO SECTION -->
            <section class="landing-hero" id="hero" aria-labelledby="hero-title">
                <div class="landing-badge">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                    <span>Sleek Dark UI &bull; Dirancang Khusus untuk Mahasiswa</span>
                </div>

                <h1 id="hero-title" class="hero-title">
                    Kelola Tugas &amp; Jadwal Kuliah <br>
                    <span class="gradient-text">Bebas Panik Deadline</span>
                </h1>

                <p class="hero-subtitle">
                    Visualisasikan antrean beban perkuliahan dengan papan Kanban interaktif, countdown batas waktu otomatis, dan integrasi tautan LMS kampus dalam satu platform terpadu.
                </p>

                <div class="hero-cta-group">
                    <a href="login.php?tab=register" class="btn-hero-primary">
                        <span>Mulai Sekarang - Gratis</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                            <polyline points="12 5 19 12 12 19"></polyline>
                        </svg>
                    </a>
                    <a href="dashboard.php" class="btn-hero-secondary">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                            <line x1="9" y1="3" x2="9" y2="21"></line>
                            <line x1="15" y1="3" x2="15" y2="21"></line>
                        </svg>
                        <span>Coba Dashboard Demo</span>
                    </a>
                </div>

                <div class="hero-trust-badges">
                    <div class="hero-trust-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>100% Khusus Mahasiswa</span>
                    </div>
                    <div class="hero-trust-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Bebas Distraksi &amp; Iklan</span>
                    </div>
                    <div class="hero-trust-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Bisa Offline via LocalStorage</span>
                    </div>
                </div>
            </section>

            <!-- PRATINJAU INTERAKTIF / MOCKUP TEASER -->
            <section id="preview" class="hero-mockup-wrap" aria-labelledby="preview-title">
                <h2 id="preview-title" class="sr-only">Pratinjau Antarmuka TaskTrack</h2>
                <div class="mockup-window">
                    <div class="mockup-header-bar">
                        <div class="mockup-dots">
                            <span class="mockup-dot mockup-dot-red"></span>
                            <span class="mockup-dot mockup-dot-yellow"></span>
                            <span class="mockup-dot mockup-dot-green"></span>
                        </div>
                        <span class="mockup-title">tasktrack.app/dashboard</span>
                        <a href="dashboard.php" class="btn-demo-fill" style="text-decoration: none;">Buka Full Demo &rarr;</a>
                    </div>
                    <div class="mockup-content">
                        <!-- Mini Mockup Kanban Header -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <span style="font-weight: 700; font-size: 1.1rem; color: var(--text-main);">Papan Alur Tugas</span>
                                <span class="feature-tag tag-primary">4 Kolom Aktif</span>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <span class="feature-tag tag-warning">1 Tugas &lt; 24 Jam</span>
                                <span class="feature-tag tag-success">1 Tugas Selesai</span>
                            </div>
                        </div>

                        <!-- Mini Mockup Kanban Columns -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1rem;">
                            <!-- Column Planned -->
                            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #38bdf8; margin-bottom: 0.65rem;">
                                    <span>Planned</span>
                                    <span>2</span>
                                </div>
                                <div style="background: var(--bg-subtle); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.65rem; margin-bottom: 0.5rem; font-size: 0.8rem;">
                                    <strong style="display: block; color: var(--text-main); margin-bottom: 0.2rem;">Semantik HTML &amp; Aksesibilitas</strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">Pemrograman Web &bull; 3 hari lagi</span>
                                </div>
                                <div style="background: var(--bg-subtle); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.65rem; font-size: 0.8rem;">
                                    <strong style="display: block; color: var(--text-main); margin-bottom: 0.2rem;">Desain ERD Basis Data</strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">Sistem Basis Data &bull; 6 hari lagi</span>
                                </div>
                            </div>

                            <!-- Column In Progress -->
                            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #fbbf24; margin-bottom: 0.65rem;">
                                    <span>In Progress</span>
                                    <span>1</span>
                                </div>
                                <div style="background: var(--bg-subtle); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.65rem; font-size: 0.8rem;">
                                    <strong style="display: block; color: var(--text-main); margin-bottom: 0.2rem;">Routing OSPF &amp; Subnetting</strong>
                                    <span style="color: var(--text-muted); font-size: 0.75rem;">Jaringan Komputer &bull; 2 hari lagi</span>
                                </div>
                            </div>

                            <!-- Column Completed -->
                            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #34d399; margin-bottom: 0.65rem;">
                                    <span>Completed</span>
                                    <span>1</span>
                                </div>
                                <div style="background: var(--bg-subtle); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.65rem; font-size: 0.8rem;">
                                    <strong style="display: block; color: var(--text-main); margin-bottom: 0.2rem;">Resume Algoritma Dijkstra</strong>
                                    <span style="color: #34d399; font-size: 0.75rem;">&check; Selesai Tuntas</span>
                                </div>
                            </div>

                            <!-- Column Overdue -->
                            <div style="background: var(--bg-surface); border: 1px solid var(--border-subtle); border-radius: var(--radius-md); padding: 0.85rem;">
                                <div style="display: flex; justify-content: space-between; font-size: 0.8rem; font-weight: 700; color: #fb7185; margin-bottom: 0.65rem;">
                                    <span>Overdue / &lt; 24h</span>
                                    <span>1</span>
                                </div>
                                <div style="background: var(--bg-subtle); border-left: 3px solid #fb7185; border-radius: var(--radius-md); padding: 0.65rem; font-size: 0.8rem;">
                                    <strong style="display: block; color: var(--text-main); margin-bottom: 0.2rem;">Laporan Praktikum Modul 1</strong>
                                    <span style="color: #fb7185; font-size: 0.75rem; font-weight: 600;">Sisa 4 Jam (Kritis)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <!-- SECTION FITUR UNGGULAN (MENGGUNAKAN KOMPONEN REUSABLE FEATURECARD) -->
            <section class="landing-section" id="features" aria-labelledby="features-title">
                <div class="landing-section-header">
                    <span class="section-eyebrow">Fitur Unggulan</span>
                    <h2 id="features-title" class="landing-section-title">Semua Senjata Pelacak Tugas Kuliah</h2>
                    <p class="landing-section-desc">
                        Fokus pada kepraktisan tanpa menu membingungkan, membantu Anda lulus semua mata kuliah tepat waktu.
                    </p>
                </div>

                <div class="features-grid">
                    <?php
                    // Fitur 1: Kanban 4 Kolom
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line></svg>',
                        'title' => 'Kanban Board 4 Kolom',
                        'desc' => 'Kelola alur pengerjaan tugas dari Planned, In progress, Completed, hingga Overdue secara visual dengan sistem drag and drop responsif.',
                        'tag' => 'Alur Kerja',
                        'tag_class' => 'tag-primary'
                    ]);

                    // Fitur 2: Deadline Alert
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>',
                        'title' => 'Countdown & Peringatan Deadline',
                        'desc' => 'Perhitungan mundur sisa waktu secara otomatis dengan penandaan warna aksen merah untuk batas waktu kritis kurang dari 24 jam.',
                        'tag' => 'Ketepatan Waktu',
                        'tag_class' => 'tag-warning'
                    ]);

                    // Fitur 3: Integrasi LMS
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>',
                        'title' => 'Pintasan Langsung Portal LMS',
                        'desc' => 'Tautkan link Google Classroom, Moodle, atau portal e-learning kampus langsung pada kartu tugas agar pengumpulan tugas sekali klik.',
                        'tag' => 'Integrasi',
                        'tag_class' => 'tag-accent'
                    ]);

                    // Fitur 4: Kalender Perkuliahan
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>',
                        'title' => 'Kalender Akademik Bulanan',
                        'desc' => 'Visualisasi jadwal penyerahan tugas dalam tampilan kalender bulanan interaktif untuk memetakan tanggal padat perkuliahan.',
                        'tag' => 'Jadwal',
                        'tag_class' => 'tag-primary'
                    ]);

                    // Fitur 5: Analitik Beban Studi
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>',
                        'title' => 'Ringkasan & Analitik Beban',
                        'desc' => 'Hitung rasio kelulusan tugas dan distribusi persentase ketuntasan per mata kuliah secara instan untuk mengukur beban studi.',
                        'tag' => 'Statistik',
                        'tag_class' => 'tag-success'
                    ]);

                    // Fitur 6: Aksesibilitas WCAG AA
                    renderFeatureCard([
                        'icon' => '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><path d="m4.93 4.93 4.24 4.24"></path><path d="m14.83 9.17 4.24-4.24"></path><path d="m14.83 14.83 4.24 4.24"></path><path d="m9.17 14.83-4.24 4.24"></path></svg>',
                        'title' => 'Aksesibel & Ergonomis',
                        'desc' => 'Dibangun dengan standar WCAG AA, navigasi ramah keyboard penuh, live announcer screen reader, dan tema Sleek Dark non-silau.',
                        'tag' => 'Standar Web',
                        'tag_class' => 'tag-accent'
                    ]);
                    ?>
                </div>
            </section>

            <!-- FAQ SECTION -->
            <section class="landing-section" id="faq" aria-labelledby="faq-title">
                <div class="landing-section-header">
                    <span class="section-eyebrow">Pertanyaan Umum</span>
                    <h2 id="faq-title" class="landing-section-title">Kerap Ditanyakan Mahasiswa</h2>
                </div>

                <div style="max-width: 740px; margin: 0 auto; display: flex; flex-direction: column; gap: 1rem;">
                    <div class="workflow-card">
                        <h3 style="font-size: 1.05rem;">Apakah data tugas saya tetap tersimpan jika halaman ditutup?</h3>
                        <p>Ya. TaskTrack menyimpan seluruh data tugas pada penyimpanan lokal peramban (localStorage) serta mendukung integrasi basis data MySQL jika server aktif.</p>
                    </div>
                    <div class="workflow-card">
                        <h3 style="font-size: 1.05rem;">Apakah aplikasi ini berbayar?</h3>
                        <p>TaskTrack 100% gratis untuk seluruh mahasiswa tanpa iklan dan tanpa batasan jumlah penambahan kartu tugas.</p>
                    </div>
                    <div class="workflow-card">
                        <h3 style="font-size: 1.05rem;">Apakah saya bisa mencoba tanpa registrasi?</h3>
                        <p>Bisa! Anda dapat langsung membuka <a href="dashboard.php" style="color: var(--accent-primary); font-weight: 600;">Dashboard Demo</a> untuk mencoba seluruh fitur interaktif.</p>
                    </div>
                </div>
            </section>

            <!-- CALL TO ACTION BANNER -->
            <section class="landing-section" style="border-bottom: none; padding-bottom: 2rem;">
                <div class="landing-cta-box">
                    <h2>Siap Taklukkan Deadline Tugas Kuliah?</h2>
                    <p>Bergabunglah sekarang dan kelola penugasan akademik dengan lebih teratur, tenang, dan terstruktur.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="login.php?tab=register" class="btn-hero-primary">
                            <span>Daftar Akun Baru Sekarang</span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                <polyline points="12 5 19 12 12 19"></polyline>
                            </svg>
                        </a>
                        <a href="login.php" class="btn-hero-secondary">
                            <span>Sudah Punya Akun? Masuk</span>
                        </a>
                    </div>
                </div>
            </section>

        </main>

        <footer role="contentinfo" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <div class="brand-icon" style="width: 24px; height: 24px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <span style="font-weight: 700; color: var(--text-main);"><?= $app_name ?></span>
            </div>

            <div style="display: flex; gap: 1.5rem; font-size: 0.85rem;">
                <a href="index.php" style="color: var(--text-muted); text-decoration: none;">Beranda</a>
                <a href="login.php" style="color: var(--text-muted); text-decoration: none;">Masuk</a>
                <a href="login.php?tab=register" style="color: var(--text-muted); text-decoration: none;">Daftar</a>
                <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none;">Dashboard</a>
            </div>

            <span id="footer-notif-status">
                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span>TaskTrack Mahasiswa v1.0</span>
            </span>
        </footer>
    </div>

    <script>
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
    </script>
</body>

</html>
