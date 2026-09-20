<?php
/**
 * TaskTrack - Web Tracker Tugas & Deadline Kuliah
 * Landing Page Responsif & Aksesibel berbasis PHP Standar, Flexbox/Grid, dan Komponen Reusable.
 */
require_once __DIR__ . '/templates/components/stat-card.php';
require_once __DIR__ . '/templates/components/feature-card.php';
require_once __DIR__ . '/templates/components/task-card.php';

$current_year = date('Y');
$page_title = "TaskTrack — Web Tracker Tugas & Deadline Kuliah Mahasiswa";
$meta_description = "Landing page dan aplikasi visual tracker berbasis Kanban 3 kolom untuk mahasiswa. Drag and drop interaktif, auto-sorting deadline, indikator warna kritis, dan integrasi link LMS.";

// Data Awal Tugas Mahasiswa
$initial_tasks = [
    [
        'id' => 'task-1',
        'title' => 'Implementasi Arsitektur MVC & Routing Native',
        'course' => 'Pemrograman Web Lanjut',
        'deadline' => date('Y-m-d\TH:i', strtotime('+18 hours')),
        'status' => 'todo',
        'urgency' => 'critical',
        'lms_url' => 'https://lms.universitas.ac.id/mod/assign/view.php?id=101',
        'instructions' => 'Buat struktur folder MVC murni menggunakan PHP native tanpa framework. Sertakan file index.php, Router.php, dan Controller dasar sesuai modul praktikum 4.'
    ],
    [
        'id' => 'task-2',
        'title' => 'Normalisasi Basis Data Relasional 3NF',
        'course' => 'Sistem Basis Data',
        'deadline' => date('Y-m-d\TH:i', strtotime('+2 days 4 hours')),
        'status' => 'inprogress',
        'urgency' => 'warning',
        'lms_url' => 'https://lms.universitas.ac.id/mod/assign/view.php?id=204',
        'instructions' => 'Lakukan perancangan ERD dan normalisasi tabel transaksi klinik hingga bentuk 3NF beserta DDL script MySQL.'
    ],
    [
        'id' => 'task-3',
        'title' => 'Analisis Kebutuhan Sistem & Pembuatan SRS',
        'course' => 'Rekayasa Perangkat Lunak',
        'deadline' => date('Y-m-d\TH:i', strtotime('+5 days 10 hours')),
        'status' => 'inprogress',
        'urgency' => 'safe',
        'lms_url' => 'https://lms.universitas.ac.id/mod/assign/view.php?id=305',
        'instructions' => 'Susun dokumen SRS standar IEEE 830 mencakup use case diagram, activity diagram, dan non-functional requirements.'
    ],
    [
        'id' => 'task-4',
        'title' => 'Konfigurasi Subnetting & Routing Statis Cisco',
        'course' => 'Jaringan Komputer',
        'deadline' => date('Y-m-d\TH:i', strtotime('-1 day')),
        'status' => 'done',
        'urgency' => 'done',
        'lms_url' => 'https://lms.universitas.ac.id/mod/assign/view.php?id=402',
        'instructions' => 'Praktikum Packet Tracer menghubungkan 3 router dengan routing statis dan konfigurasi DHCP server.'
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

    <!-- Header Aplikasi & Navigasi Responsif -->
    <header class="app-header" role="banner">
        <div class="header-container">
            <div class="brand-area">
                <a href="#hero" class="brand-logo" aria-label="TaskTrack - Halaman Utama">
                    <svg class="logo-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img">
                        <rect width="32" height="32" rx="8" fill="url(#logo-grad)"/>
                        <path d="M9 16.5L14 21.5L23 10.5" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <defs>
                            <linearGradient id="logo-grad" x1="0" y1="0" x2="32" y2="32" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6366F1"/>
                                <stop stop-color="#4338CA"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <span class="brand-text">Task<strong>Track</strong></span>
                </a>
                <span class="brand-badge" aria-label="Versi Proyek">v1.2 Landing</span>
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
                            <span class="nav-icon" aria-hidden="true">🏠</span>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#features" class="nav-link">
                            <span class="nav-icon" aria-hidden="true">✨</span>
                            <span>Fitur</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#kanban-section" class="nav-link active">
                            <span class="nav-icon" aria-hidden="true">📋</span>
                            <span>Papan Kanban</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#task-management" class="nav-link">
                            <span class="nav-icon" aria-hidden="true">➕</span>
                            <span>Tambah Tugas</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#accessibility-checklist" class="nav-link">
                            <span class="nav-icon" aria-hidden="true">♿</span>
                            <span>Aksesibilitas</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Aksi Header: Izin Notifikasi & Modal Login Pengguna -->
            <div class="header-actions">
                <button type="button" id="btn-notification-prompt" class="btn-icon" aria-label="Pengaturan Izin Web Push Notification" title="Aktifkan Web Push Notification">
                    <span class="icon-bell" aria-hidden="true">🔔</span>
                    <span id="notif-badge" class="badge-dot" aria-label="Status notifikasi aktif"></span>
                </button>

                <div class="user-auth-widget">
                    <button type="button" id="btn-open-auth" class="btn-auth-trigger" aria-haspopup="dialog" aria-expanded="false">
                        <span class="user-avatar" aria-hidden="true">👤</span>
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
                        <div class="hero-pill-badge" role="note">
                            <span class="badge-icon" aria-hidden="true">🚀</span>
                            <span>Visual Tracker Tugas & Deadline Kuliah</span>
                        </div>
                        <h1 id="heading-hero" class="hero-title">
                            Kelola Tugas Kuliah Tanpa Panik <span class="text-gradient">Terlewat Deadline</span>
                        </h1>
                        <p class="hero-description">
                            TaskTrack memadukan fleksibilitas <strong>Papan Kanban 3 Kolom</strong>, integrasi instan ke portal <strong>LMS kampus</strong>, peringatan otomatis <strong>Web Push H-1 & H-3 Jam</strong>, serta indikator visual warna kritis untuk memprioritaskan antrean tugas kuliah Anda.
                        </p>
                        
                        <div class="hero-cta-group">
                            <a href="#kanban-section" class="btn btn-primary btn-lg">
                                <span aria-hidden="true">📋</span>
                                <span>Buka Papan Kanban</span>
                            </a>
                            <a href="#features" class="btn btn-secondary btn-lg">
                                <span aria-hidden="true">🔍</span>
                                <span>Pelajari Fitur Unggulan</span>
                            </a>
                        </div>
                    </div>

                    <!-- Banner Opt-in Web Push Notification (Peringatan H-1 & H-3 Jam) -->
                    <div id="notification-banner" class="notification-banner" role="region" aria-label="Pemberitahuan Izin Notifikasi">
                        <div class="notif-banner-content">
                            <span class="notif-banner-icon" aria-hidden="true">⏰</span>
                            <div class="notif-banner-text">
                                <strong>Aktifkan Peringatan Web Push Notification</strong>
                                <p>Dapatkan peringatan otomatis di browser & ponsel saat tugas mendekati <strong>H-1 Hari</strong> dan <strong>H-3 Jam</strong> sebelum deadline.</p>
                            </div>
                        </div>
                        <div class="notif-banner-actions">
                            <button type="button" id="btn-enable-push" class="btn btn-primary btn-sm">Izinkan Notifikasi</button>
                            <button type="button" id="btn-dismiss-banner" class="btn btn-secondary btn-sm" aria-label="Tutup pemberitahuan notifikasi">Nanti Saja</button>
                        </div>
                    </div>

                    <!-- Reusable Component: Metrik Statistik Tugas (CSS Grid & Flexbox) -->
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

        <!-- SECTION 2: FITUR UNGGULAN (Landing Page Value Proposition) -->
        <section id="features" class="section-features" aria-labelledby="heading-features">
            <div class="section-container">
                <div class="section-header-center">
                    <span class="section-tag">Keunggulan Sistem</span>
                    <h2 id="heading-features" class="section-heading">Mengapa Mahasiswa Memilih TaskTrack?</h2>
                    <p class="section-subtext">Dirancang dengan alur kerja modern berbasis Kanban, aksesibilitas inklusif, dan otomasi tenggat waktu untuk meningkatkan produktivitas belajar.</p>
                </div>

                <!-- Reusable Component: FeatureCard Grid -->
                <div class="features-grid">
                    <?php
                    renderFeatureCard([
                        'icon' => '📌',
                        'title' => 'Papan Kanban 3 Kolom Interaktif',
                        'desc' => 'Visualisasikan perjalanan tugas dari Belum Dimulai (To Do), Sedang Dikerjakan (In Progress), hingga Selesai (Done) dengan drag & drop mulus.',
                        'tag' => 'Alur Kerja Visual',
                        'tag_class' => 'tag-primary'
                    ]);
                    renderFeatureCard([
                        'icon' => '🚨',
                        'title' => 'Warna Indikator Kritis Waktu',
                        'desc' => 'Kartu otomatis berubah warna: Merah (< 24 jam), Kuning (< 3 hari), Hijau (> 3 hari), dan Abu-abu (selesai), lengkap dengan auto-sorting tenggat terdekat.',
                        'tag' => 'Anti Ketinggalan',
                        'tag_class' => 'tag-danger'
                    ]);
                    renderFeatureCard([
                        'icon' => '🔔',
                        'title' => 'Web Push Notification H-1 & H-3 Jam',
                        'desc' => 'Notifikasi browser otomatis memberi peringatan pop-up di ponsel atau laptop saat deadline mendekati H-1 hari dan H-3 jam sebelum ditutup.',
                        'tag' => 'Pengingat Aktif',
                        'tag_class' => 'tag-warning'
                    ]);
                    renderFeatureCard([
                        'icon' => '🌐',
                        'title' => 'Shortcut Portal LMS & Quick Paste',
                        'desc' => 'Tautan langsung 1-klik ke halaman tugas Moodle / Google Classroom kampus, disertai area tempel instruksi tugas panjang dari clipboard.',
                        'tag' => 'Integrasi Kampus',
                        'tag_class' => 'tag-success'
                    ]);
                    renderFeatureCard([
                        'icon' => '♿',
                        'title' => 'Aksesibilitas Penuh (WCAG 2.1 AA)',
                        'desc' => 'Dukungan tombol fokus (:focus-visible), navigasi keyboard runtut (Tab/Esc), skip to content, live announcer pembaca layar, dan kontras warna teruji.',
                        'tag' => 'Inklusif & Ramah',
                        'tag_class' => 'tag-info'
                    ]);
                    renderFeatureCard([
                        'icon' => '🔐',
                        'title' => 'Google SSO & Fitur Remember Me',
                        'desc' => 'Masuk instan menggunakan akun Google Mahasiswa atau email biasa dengan penyimpanan sesi persisten di perangkat Anda.',
                        'tag' => 'Autentikasi Cepat',
                        'tag_class' => 'tag-dark'
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
                        <span class="section-tag">Ruang Kerja Langsung</span>
                        <h2 id="heading-kanban" class="section-heading">Papan Kanban Alur Kerja Tugas</h2>
                        <p class="section-subtext">Pindahkan kartu tugas antar kolom secara <strong>Drag & Drop</strong> atau tombol pindah. Kartu otomatis terurut berdasarkan tenggat terdekat.</p>
                    </div>

                    <!-- Petunjuk Warna Indikator Kritis -->
                    <div class="urgency-legend" role="note" aria-label="Keterangan Indikator Urgensi Deadline">
                        <span class="legend-title">Indikator Tenggat:</span>
                        <span class="legend-item"><span class="color-dot dot-critical" aria-hidden="true"></span> &lt; 24 Jam (Kritis)</span>
                        <span class="legend-item"><span class="color-dot dot-warning" aria-hidden="true"></span> &lt; 3 Hari (Perhatian)</span>
                        <span class="legend-item"><span class="color-dot dot-safe" aria-hidden="true"></span> &gt; 3 Hari (Aman)</span>
                        <span class="legend-item"><span class="color-dot dot-done" aria-hidden="true"></span> Selesai</span>
                    </div>
                </div>

                <!-- Kontrol Filter & Pencarian -->
                <div class="filter-toolbar">
                    <form id="filter-form" class="filter-controls" role="search" aria-label="Filter Mata Kuliah dan Cari Tugas" onsubmit="event.preventDefault();">
                        <div class="filter-group">
                            <label for="filter-course-select" class="filter-label">
                                <span class="filter-icon" aria-hidden="true">📚</span>
                                <span>Mata Kuliah:</span>
                            </label>
                            <select id="filter-course-select" class="form-select filter-select">
                                <option value="all">Semua Mata Kuliah</option>
                                <option value="Pemrograman Web Lanjut">Pemrograman Web Lanjut</option>
                                <option value="Sistem Basis Data">Sistem Basis Data</option>
                                <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                                <option value="Jaringan Komputer">Jaringan Komputer</option>
                            </select>
                        </div>

                        <div class="filter-group search-group">
                            <label for="search-task-input" class="filter-label">
                                <span class="filter-icon" aria-hidden="true">🔍</span>
                                <span>Cari Tugas:</span>
                            </label>
                            <input type="search" id="search-task-input" class="form-input search-input" placeholder="Cari judul atau materi tugas...">
                        </div>

                        <div class="filter-actions">
                            <a href="#task-management" class="btn btn-primary btn-add-shortcut">
                                <span aria-hidden="true">➕</span>
                                <span>Tugas Baru</span>
                            </a>
                        </div>
                    </form>
                </div>

                <!-- 3 Kolom Papan Kanban (CSS Grid & Reusable TaskCard Components) -->
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

        <!-- SECTION 4: FORM MANAJEMEN TUGAS (CRUD) & QUICK INPUT LMS -->
        <section id="task-management" class="section-management" aria-labelledby="heading-management">
            <div class="section-container">
                <div class="form-wrapper-card">
                    <header class="form-header">
                        <span class="section-tag">Manajemen & Entri</span>
                        <h2 id="heading-management" class="section-heading">Tambah & Kelola Tugas Kuliah</h2>
                        <p class="section-subtext">Isi formulir terstruktur di bawah ini untuk menambahkan tugas baru ke papan Kanban atau memperbarui data tugas kuliah Anda.</p>
                    </header>

                    <!-- Formulir Manajemen Tugas Lengkap (<form>) -->
                    <form id="task-form" class="task-form" method="post" action="javascript:void(0);" novalidate>
                        <input type="hidden" id="task-id" name="task_id" value="">

                        <fieldset class="form-fieldset">
                            <legend class="form-legend">Informasi Pokok Tugas</legend>

                            <div class="form-row">
                                <div class="form-field flex-2">
                                    <label for="task-title" class="field-label">
                                        <span>Judul Tugas</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <input type="text" id="task-title" name="task_title" class="form-input" placeholder="Contoh: Makalah Etika Profesi IT & AI" required aria-required="true" aria-describedby="task-title-help">
                                    <small id="task-title-help" class="field-help">Tuliskan nama tugas atau proyek secara jelas dan ringkas.</small>
                                </div>

                                <div class="form-field flex-1">
                                    <label for="task-course" class="field-label">
                                        <span>Mata Kuliah</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <select id="task-course" name="task_course" class="form-select" required aria-required="true">
                                        <option value="" disabled selected>Pilih Mata Kuliah...</option>
                                        <option value="Pemrograman Web Lanjut">Pemrograman Web Lanjut</option>
                                        <option value="Sistem Basis Data">Sistem Basis Data</option>
                                        <option value="Rekayasa Perangkat Lunak">Rekayasa Perangkat Lunak</option>
                                        <option value="Jaringan Komputer">Jaringan Komputer</option>
                                        <option value="Kecerdasan Buatan">Kecerdasan Buatan</option>
                                        <option value="Lainnya">Lainnya / Umum</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-field flex-1">
                                    <label for="task-deadline" class="field-label">
                                        <span>Tanggal & Jam Deadline</span>
                                        <span class="required-indicator" aria-hidden="true">*</span>
                                    </label>
                                    <input type="datetime-local" id="task-deadline" name="task_deadline" class="form-input" required aria-required="true" aria-describedby="task-deadline-help">
                                    <small id="task-deadline-help" class="field-help">Sistem otomatis menghitung sisa waktu & mewarnai kartu.</small>
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
                            <legend class="form-legend">Integrasi Portal E-Learning / LMS</legend>

                            <div class="form-field">
                                <label for="task-lms-url" class="field-label">
                                    <span>Link Pengumpulan Tugas (LMS URL)</span>
                                </label>
                                <div class="input-with-icon">
                                    <span class="input-addon-icon" aria-hidden="true">🌐</span>
                                    <input type="url" id="task-lms-url" name="task_lms_url" class="form-input" placeholder="https://kuliah.kampus.ac.id/mod/assign/view.php?id=..." aria-describedby="task-lms-help">
                                </div>
                                <small id="task-lms-help" class="field-help">Tautan langsung ke halaman unggah tugas di Moodle/Google Classroom/Canvas.</small>
                            </div>

                            <!-- Quick Input Teks Instruksi Panjang -->
                            <div class="form-field">
                                <div class="field-label-group">
                                    <label for="task-instructions" class="field-label">
                                        <span>Quick Input Teks Instruksi Dosen / Portal E-Learning</span>
                                    </label>
                                    <button type="button" id="btn-paste-clipboard" class="btn-text-action" title="Tempel otomatis dari Clipboard">
                                        <span aria-hidden="true">📋</span> Salin dari Clipboard
                                    </button>
                                </div>
                                <textarea id="task-instructions" name="task_instructions" class="form-textarea" rows="4" placeholder="Salin & tempel instruksi panjang dari portal e-learning (format laporan, batas halaman, ketentuan penamaan file, dll)..." aria-describedby="task-instructions-help"></textarea>
                                <small id="task-instructions-help" class="field-help">Memudahkan membaca rincian tugas tanpa harus berulang kali membuka portal kampus.</small>
                            </div>
                        </fieldset>

                        <div class="form-actions">
                            <button type="submit" id="btn-save-task" class="btn btn-primary btn-lg">
                                <span class="btn-icon-symbol" aria-hidden="true">💾</span>
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

        <!-- SECTION 5: CHECKLIST AKSESIBILITAS DASAR (Accessibility Audit) -->
        <section id="accessibility-checklist" class="section-accessibility" aria-labelledby="heading-accessibility">
            <div class="section-container">
                <div class="section-header-block">
                    <span class="section-tag">Audit Kualitas Web</span>
                    <h2 id="heading-accessibility" class="section-heading">Checklist Aksesibilitas Dasar (WCAG 2.1 AA)</h2>
                    <p class="section-subtext">Evaluasi kepatuhan aksesibilitas web sesuai standar proyek semester untuk memastikan aplikasi dapat diakses semua pengguna, termasuk pengguna keyboard dan pembaca layar.</p>
                </div>

                <div class="a11y-table-responsive">
                    <table class="a11y-table" aria-label="Tabel Evaluasi Aksesibilitas TaskTrack">
                        <thead>
                            <tr>
                                <th scope="col" style="width: 24%;">Kriteria Aksesibilitas</th>
                                <th scope="col" style="width: 20%;">Implementasi pada TaskTrack</th>
                                <th scope="col" style="width: 44%;">Rincian & Bukti Kode Semantik</th>
                                <th scope="col" style="width: 12%;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th scope="row">1. Semantic HTML5 Structure</th>
                                <td>Navigasi, Main, 5 Section, Article, Form, Footer</td>
                                <td>Penggunaan elemen semantik native (<code>&lt;header&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, 5x <code>&lt;section&gt;</code>, <code>&lt;article&gt;</code> kartu tugas, <code>&lt;form&gt;</code>, dan <code>&lt;footer&gt;</code>).</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <th scope="row">2. Logical Heading Hierarchy</th>
                                <td>Tingkatan H1 hingga H4 runtut</td>
                                <td>Halaman diawali satu <code>&lt;h1&gt;</code> utama, diikuti <code>&lt;h2&gt;</code> untuk setiap section, <code>&lt;h3&gt;</code> untuk kolom dan rincian formulir/fitur, serta <code>&lt;h4&gt;</code> untuk judul kartu tugas.</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <th scope="row">3. Keyboard Navigability & Focus-Visible</th>
                                <td>Fokus Tab runtut, Skip Link & :focus-visible</td>
                                <td>Tersedia <code>.skip-link</code> untuk melompati navigasi. Seluruh tombol, link, dan kartu memiliki styling <code>:focus-visible</code> tegas (3px solid dengan offset) dan dapat dinavigasi via keyboard.</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <th scope="row">4. Form Labels & Associations</th>
                                <td>Pasangan <code>&lt;label for&gt;</code> & <code>id</code></td>
                                <td>Semua field input, select, dan textarea memiliki pasangan label yang terhubung secara eksplisit menggunakan atribut <code>for</code> dan <code>id</code>, dilengkapi petunjuk <code>aria-describedby</code>.</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <th scope="row">5. High Contrast & Urgency Indicators</th>
                                <td>Kontras warna WCAG AA (&gt; 4.5:1)</td>
                                <td>Indikator urgensi deadline tidak hanya mengandalkan warna, melainkan disertai teks status eksplisit (Kritis, Perhatian, Aman, Selesai) dan rasio kontras teks teruji.</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                            <tr>
                                <th scope="row">6. ARIA Roles & Screen Reader Alerts</th>
                                <td>Landmark ARIA & <code>aria-live</code></td>
                                <td>Perubahan status tugas dan aksi interaktif diumumkan via elemen <code>aria-live="polite"</code> sehingga pengguna pembaca layar mendapatkan konteks pembaruan.</td>
                                <td><span class="badge-status badge-success">✓ Terpenuhi</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <!-- Modal Dialog Autentikasi Pengguna (Google SSO & Email/Password) -->
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
                    <svg class="google-icon" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
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

    <!-- Footer Aplikasi Semantik (WCAG & Panduan Aksesibilitas) -->
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
                    <li><a href="#hero">Beranda & Ringkasan</a></li>
                    <li><a href="#features">Fitur Unggulan</a></li>
                    <li><a href="#kanban-section">Papan Kanban 3 Kolom</a></li>
                    <li><a href="#task-management">Formulir Tambah Tugas</a></li>
                    <li><a href="#accessibility-checklist">Checklist Aksesibilitas</a></li>
                </ul>
            </div>

            <div class="footer-col footer-a11y">
                <h3 class="footer-heading">Panduan Pintasan Keyboard</h3>
                <ul class="keyboard-guide-list">
                    <li><kbd>Tab</kbd> : Berpindah antar tombol & formulir</li>
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
