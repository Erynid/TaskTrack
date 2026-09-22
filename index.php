<?php
/**
 * TaskTrack - Papan Tugas Kuliah Dark Theme (Sleek Obsidian Edition)
 * Dibangun dengan PHP Standar, HTML5 Semantik, CSS Custom Properties, dan Vanilla JS.
 * Sesuai panduan .agents/rules: zero raw emojis, standard SVG stroke icons, and zero em-dashes.
 */
require_once __DIR__ . '/components/task-card.php';
require_once __DIR__ . '/components/stat-card.php';

$app_name = "TaskTrack";
$current_year = date("Y");

// Data Sampel Awal (Bebas Emoji)
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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $app_name ?>: Papan Pelacak Tugas Kuliah</title>
    <meta name="description" content="Aplikasi visual pelacak tugas dan jadwal kuliah mahasiswa dengan antarmuka Sleek Dark UI yang cepat, bersih, dan bebas hambatan.">
    <meta name="theme-color" content="#090a0e">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Stylesheet Utama Sleek Dark UI -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Skip Link untuk Aksesibilitas Keyboard (WCAG 2.4.1) -->
    <a href="#main-content" class="skip-link">Loncat ke konten utama</a>

    <!-- Live Announcer untuk Pembaca Layar -->
    <div id="a11y-announcer" class="sr-only" aria-live="polite" aria-atomic="true"></div>

    <div class="app-window">
        <!-- HEADER / NAVIGATION (Sleek Dark Pill Nav) -->
        <header role="banner">
            <a href="index.php" class="brand-logo" aria-label="TaskTrack Beranda">
                <div class="brand-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="3" y="3" width="18" height="18" rx="6" stroke="rgba(255,255,255,0.22)" stroke-width="1.6" fill="#181c26"/>
                        <path d="M8 12.5L11 15.5L16.5 9" stroke="#facc15" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <span><?= $app_name ?></span>
            </a>

            <!-- Navigasi Pill: Dashboard, Tasks (Active Tab), Calendar -->
            <nav aria-label="Navigasi Utama">
                <ul class="nav-pill-list">
                    <li><button type="button" class="nav-tab-btn" data-target="view-dashboard">Dashboard</button></li>
                    <li><button type="button" class="nav-tab-btn active" data-target="view-tasks">Tasks</button></li>
                    <li><button type="button" class="nav-tab-btn" data-target="view-calendar">Calendar</button></li>
                </ul>
            </nav>

            <!-- User Menu Settings & Profile -->
            <div class="user-controls">
                <button type="button" id="btn-open-settings" class="icon-btn" title="Pengaturan dan Notifikasi" aria-label="Pengaturan">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span id="notif-badge" class="badge-dot" title="Notifikasi Aktif"></span>
                </button>
                <button type="button" id="btn-open-profile" class="avatar-btn" title="Muhammad Dzakir Dzakwan" aria-label="Profil Pengguna">
                    MD
                </button>
            </div>
        </header>

        <!-- KONTEN UTAMA -->
        <main id="main-content" tabindex="-1">

            <!-- ============================================================
                 VIEW 1: TASKS VIEW (DEFAULT ACTIVE)
                 ============================================================ -->
            <section id="view-tasks" class="app-view-section active-view" aria-labelledby="page-title">
                
                <!-- Control Bar Top -->
                <div class="toolbar-top">
                    <div class="page-heading">
                        <h1 id="page-title">Tasks</h1>
                    </div>
                    <button type="button" class="btn-new-task" id="btn-new-task-trigger" aria-haspopup="dialog" aria-expanded="false">
                        <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>New task</span>
                    </button>
                </div>

                <!-- Search, Filter & Switcher View -->
                <div class="toolbar-secondary">
                    <div class="search-filter-box">
                        <div class="search-input-wrapper">
                            <span class="search-icon" aria-hidden="true">
                                <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input type="search" id="search-task-input" placeholder="Search" aria-label="Cari tugas kuliah">
                        </div>

                        <div class="filter-dropdown-wrapper">
                            <button type="button" id="btn-filter-toggle" class="btn-filter" aria-expanded="false" aria-haspopup="true" aria-label="Filter tugas kuliah">
                                <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                <span id="filter-current-label">Filter</span>
                            </button>
                            <div id="filter-popover" class="filter-menu-popover" role="dialog" aria-label="Menu Filter Mata Kuliah">
                                <label for="filter-course-select">Mata Kuliah / Kategori:</label>
                                <select id="filter-course-select">
                                    <option value="all">Semua Kategori</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- View Switcher (Kanban view / List view) -->
                    <div class="view-switcher" role="tablist" aria-label="Pilih Mode Tampilan">
                        <button type="button" id="tab-view-kanban" class="view-tab active" role="tab" aria-selected="true" aria-controls="kanban-view-container">
                            <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <line x1="9" y1="3" x2="9" y2="21"></line>
                                <line x1="15" y1="3" x2="15" y2="21"></line>
                            </svg>
                            <span>Kanban view</span>
                        </button>
                        <button type="button" id="tab-view-list" class="view-tab" role="tab" aria-selected="false" aria-controls="list-view-container">
                            <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                            <span>List view</span>
                        </button>
                    </div>
                </div>

                <!-- SECTION 2: KANBAN BOARD (PLANNED, IN PROGRESS, COMPLETED, OVERDUE) -->
                <div id="kanban-view-container">
                    <section class="kanban-grid" aria-label="Papan Alur Tugas Kanban">

                        <!-- KOLOM 1: PLANNED -->
                        <div class="kanban-column" aria-labelledby="col-planned" data-status="todo">
                            <div class="col-header-pill pill-planned">
                                <span class="col-label" id="col-planned">Planned</span>
                                <span class="col-count" id="count-todo">2</span>
                            </div>
                            <div class="column-dropzone" id="dropzone-todo" role="list" aria-label="Daftar tugas direncanakan">
                                <?php 
                                renderTaskCard($initial_tasks[0]);
                                renderTaskCard($initial_tasks[1]);
                                ?>
                            </div>
                        </div>

                        <!-- KOLOM 2: IN PROGRESS -->
                        <div class="kanban-column" aria-labelledby="col-progress" data-status="inprogress">
                            <div class="col-header-pill pill-in-progress">
                                <span class="col-label" id="col-progress">In progress</span>
                                <span class="col-count" id="count-inprogress">1</span>
                            </div>
                            <div class="column-dropzone" id="dropzone-inprogress" role="list" aria-label="Daftar tugas sedang dikerjakan">
                                <?php renderTaskCard($initial_tasks[2]); ?>
                            </div>
                        </div>

                        <!-- KOLOM 3: COMPLETED -->
                        <div class="kanban-column" aria-labelledby="col-completed" data-status="done">
                            <div class="col-header-pill pill-completed">
                                <span class="col-label" id="col-completed">Completed</span>
                                <span class="col-count" id="count-done">1</span>
                            </div>
                            <div class="column-dropzone" id="dropzone-done" role="list" aria-label="Daftar tugas selesai">
                                <?php renderTaskCard($initial_tasks[3]); ?>
                            </div>
                        </div>

                        <!-- KOLOM 4: OVERDUE / < 24H -->
                        <div class="kanban-column" aria-labelledby="col-overdue" data-status="overdue">
                            <div class="col-header-pill pill-overdue">
                                <span class="col-label" id="col-overdue">Overdue / &lt; 24h</span>
                                <span class="col-count" id="count-overdue">1</span>
                            </div>
                            <div class="column-dropzone" id="dropzone-overdue" role="list" aria-label="Daftar tugas mendesak atau terlewat">
                                <?php renderTaskCard($initial_tasks[4]); ?>
                            </div>
                        </div>

                    </section>
                </div>

                <!-- SECTION ALTERNATIF: LIST VIEW CONTAINER -->
                <div id="list-view-container" style="display: none;" aria-label="Tampilan Daftar Tugas">
                    <div class="tasks-list-container">
                        <div class="task-list-header">
                            <span>Judul Tugas</span>
                            <span>Mata Kuliah</span>
                            <span>Tenggat Waktu</span>
                            <span>Prioritas</span>
                            <span>Link LMS</span>
                            <span style="text-align: right;">Aksi</span>
                        </div>
                        <div id="list-view-rows">
                            <!-- Diisi dinamis oleh JavaScript -->
                        </div>
                    </div>
                </div>


            </section>

            <!-- ============================================================
                 VIEW 2: DASHBOARD VIEW
                 ============================================================ -->
            <section id="view-dashboard" class="app-view-section" aria-labelledby="heading-dashboard">
                <div class="toolbar-top">
                    <div class="page-heading">
                        <h1 id="heading-dashboard">Dashboard</h1>
                    </div>
                </div>

                <div class="dashboard-grid" role="region" aria-label="Ringkasan Statistik">
                    <?php
                    renderStatCard([
                        'id' => 'stat-total-tasks',
                        'label' => 'Total Tugas',
                        'value' => '5',
                        'desc' => 'Tersimpan di sistem TaskTrack',
                        'icon_svg' => '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>',
                        'color' => '#ffffff'
                    ]);
                    renderStatCard([
                        'id' => 'stat-planned-tasks',
                        'label' => 'Planned',
                        'value' => '2',
                        'desc' => 'Menunggu dikerjakan',
                        'icon_svg' => '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>',
                        'color' => '#f0abfc'
                    ]);
                    renderStatCard([
                        'id' => 'stat-progress-tasks',
                        'label' => 'In Progress',
                        'value' => '1',
                        'desc' => 'Sedang aktif berlangsung',
                        'icon_svg' => '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>',
                        'color' => '#93c5fd'
                    ]);
                    renderStatCard([
                        'id' => 'stat-overdue-tasks',
                        'label' => 'Overdue / < 24h',
                        'value' => '1',
                        'desc' => 'Tenggat waktu sangat mendesak',
                        'icon_svg' => '<svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
                        'color' => '#f87171'
                    ]);
                    ?>
                </div>

                <div class="dashboard-details-row">
                    <div class="dashboard-panel">
                        <h3>
                            <svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Tugas Paling Mendesak (&lt; 24 Jam)</span>
                        </h3>
                        <div id="urgent-tasks-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <!-- Diisi dinamis oleh JS -->
                        </div>
                    </div>

                    <div class="dashboard-panel">
                        <h3>
                            <svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span>Status Notifikasi dan Akun</span>
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">
                            Web Push Notification memberikan peringatan pop-up pada peramban web saat tugas mendekati batas waktu H-1 dan H-3 jam.
                        </p>
                        <button type="button" id="btn-dashboard-enable-notif" class="btn-new-task" style="width: 100%; justify-content: center;">
                            <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span>Aktifkan Pengingat Browser</span>
                        </button>
                    </div>
                </div>
            </section>

            <!-- ============================================================
                 VIEW 3: CALENDAR VIEW
                 ============================================================ -->
            <section id="view-calendar" class="app-view-section" aria-labelledby="heading-calendar">
                <div class="toolbar-top">
                    <div class="page-heading">
                        <h1 id="heading-calendar">Calendar</h1>
                    </div>
                </div>

                <div class="calendar-view-wrap">
                    <div class="calendar-header-nav">
                        <h2 id="calendar-month-year"><?= date('F Y') ?></h2>
                        <div class="calendar-nav-buttons">
                            <button type="button" class="btn-filter" id="btn-prev-month" aria-label="Bulan sebelumnya">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <button type="button" class="btn-filter" id="btn-today-month">Hari Ini</button>
                            <button type="button" class="btn-filter" id="btn-next-month" aria-label="Bulan berikutnya">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="calendar-table-grid" id="calendar-grid-cells">
                        <!-- Header hari & sel kalender dirender oleh JS -->
                    </div>
                </div>
            </section>

        </main>

        <!-- FOOTER -->
        <footer role="contentinfo">
            <span>&copy; <?= $current_year ?> <?= $app_name ?>. Didesain dengan tema Sleek Dark UI untuk pelacakan deadline mahasiswa.</span>
            <span id="footer-notif-status">
                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span>Status Notifikasi: Aktif</span>
            </span>
        </footer>
    </div>

    <!-- DIALOG / MODAL FORM TAMBAH & EDIT TUGAS -->
    <dialog id="task-modal" class="app-dialog task-dialog" aria-labelledby="form-title" aria-modal="true">
        <div class="dialog-content task-dialog-content">
            <div class="dialog-header">
                <div>
                    <h2 id="form-title" class="dialog-title">Tambah Tugas Kuliah Baru</h2>
                    <p class="form-subtitle">Masukkan rincian penugasan serta tautan e-learning untuk akses langsung pengumpulan.</p>
                </div>
                <button type="button" id="btn-close-task-modal" class="btn-close-dialog" aria-label="Tutup dialog tugas">
                    <svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <form id="task-form" action="javascript:void(0);" novalidate>
                <input type="hidden" id="task-id" name="task_id" value="">

                <div class="form-row">
                    <div class="form-field">
                        <label for="input-judul">Judul Tugas <span style="color: #f87171;">*</span></label>
                        <input type="text" id="input-judul" name="judul" required placeholder="Contoh: Implementasi Normalisasi Database">
                    </div>
                    <div class="form-field">
                        <label for="input-matkul">Mata Kuliah <span style="color: #f87171;">*</span></label>
                        <input type="text" id="input-matkul" name="matkul" list="course-suggestions" required placeholder="Contoh: Pemrograman Web">
                        <datalist id="course-suggestions">
                            <option value="Pemrograman Web Dasar">
                            <option value="Sistem Basis Data">
                            <option value="Jaringan Komputer Lanjut">
                            <option value="Struktur Data & Algoritma">
                            <option value="Pemrograman Berorientasi Objek">
                        </datalist>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field">
                        <label for="input-deadline">Tenggat Waktu (Deadline) <span style="color: #f87171;">*</span></label>
                        <input type="datetime-local" id="input-deadline" name="deadline" required>
                    </div>
                    <div class="form-field">
                        <label for="input-priority">Prioritas Urgensi</label>
                        <select id="input-priority" name="priority">
                            <option value="high">High Priority</option>
                            <option value="medium" selected>Medium Priority</option>
                            <option value="low">Low Priority</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="input-status">Status Kolom</label>
                        <select id="input-status" name="status">
                            <option value="todo" selected>Planned</option>
                            <option value="inprogress">In progress</option>
                            <option value="done">Completed</option>
                            <option value="overdue">Overdue / &lt; 24h</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field" style="flex: 2;">
                        <label for="input-url">Link Pengumpulan LMS / Classroom</label>
                        <input type="url" id="input-url" name="url_pengumpulan" placeholder="https://elearning.kampus.ac.id/mod/assign/...">
                    </div>
                    <div class="form-field" style="flex: 1;">
                        <label for="input-url-label">Label Pintasan Tombol</label>
                        <input type="text" id="input-url-label" name="url_label" placeholder="Contoh: LMS atau Classroom">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-field" style="grid-column: 1 / -1;">
                        <div class="form-field-header">
                            <label for="input-notes">Catatan & Petunjuk Tugas</label>
                            <button type="button" id="btn-paste-clipboard" class="btn-paste-clipboard" title="Tempel otomatis dari Clipboard">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                </svg>
                                <span>Salin dari Clipboard</span>
                            </button>
                        </div>
                        <textarea id="input-notes" name="notes" rows="3" placeholder="Salin instruksi atau ketentuan format pengumpulan di sini..."></textarea>
                    </div>
                </div>

                <div class="form-actions-row">
                    <button type="submit" id="btn-submit-task" class="btn-new-task">
                        <span id="save-btn-label">Simpan Kartu Tugas</span>
                    </button>
                    <button type="button" id="btn-reset-form" class="btn-reset">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <!-- DIALOG / MODAL PENGATURAN & PROFIL -->
    <dialog id="settings-modal" class="app-dialog" aria-labelledby="settings-dialog-title" aria-modal="true">
        <div class="dialog-content">
            <div class="dialog-header">
                <h2 id="settings-dialog-title" class="dialog-title">Profil & Pengaturan Akun</h2>
                <button type="button" id="btn-close-settings" class="btn-close-dialog" aria-label="Tutup dialog">
                    <svg class="icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; background: rgba(0,0,0,0.25); padding: 1rem; border-radius: 12px;">
                <div class="avatar-btn" style="width: 48px; height: 48px; font-size: 1.1rem;">MD</div>
                <div>
                    <strong style="display: block; color: #ffffff;" id="profile-user-name">Muhammad Dzakir Dzakwan</strong>
                    <span style="font-size: 0.8rem; color: var(--text-muted);" id="profile-user-email">dzakir.dzakwan@mahasiswa.ac.id</span>
                </div>
            </div>

            <button type="button" id="btn-google-sso-modal" class="btn-sso-google">
                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 5c1.6 0 3 .6 4.1 1.7l3.1-3.1C17.3 1.8 14.8 1 12 1 7.5 1 3.7 3.6 1.9 7.3l3.7 2.9C6.5 7.4 9 5 12 5z"/>
                    <path fill="#4285F4" d="M23.5 12.3c0-.8-.1-1.6-.2-2.3H12v4.5h6.5c-.3 1.5-1.1 2.8-2.4 3.7l3.7 2.9c2.2-2 3.7-5 3.7-8.8z"/>
                    <path fill="#FBBC05" d="M5.6 14.8c-.2-.7-.4-1.5-.4-2.3s.2-1.6.4-2.3L1.9 7.3C.7 9.7 0 12.3 0 15.2s.7 5.5 1.9 7.9l3.7-2.9z"/>
                    <path fill="#34A853" d="M12 23.5c3.2 0 6-1.1 8-3l-3.7-2.9c-1.1.7-2.5 1.2-4.3 1.2-3 0-5.5-2-6.4-4.8L1.9 16.9C3.7 20.6 7.5 23.5 12 23.5z"/>
                </svg>
                <span>Masuk dengan Google SSO</span>
            </button>

            <div class="auth-divider">
                <span>preferensi notifikasi</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                    <input type="checkbox" id="check-enable-sound" checked style="accent-color: #facc15;">
                    Bunyikan notifikasi deadline mendesak
                </label>
                <label style="display: flex; align-items: center; gap: 0.6rem; font-size: 0.85rem; color: var(--text-main); cursor: pointer;">
                    <input type="checkbox" id="check-auto-archive" style="accent-color: #facc15;">
                    Arsipkan tugas selesai setelah 7 hari
                </label>
            </div>
        </div>
    </dialog>

    <!-- JavaScript Aplikasi Modular ES6 -->
    <script type="module" src="js/app.js"></script>
</body>
</html>
