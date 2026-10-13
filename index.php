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

            <!-- Navigasi Pill: Dashboard, Tasks (Active Tab), Calendar, Tambah Tugas -->
            <nav aria-label="Navigasi Utama">
                <ul class="nav-pill-list">
                    <li><button type="button" class="nav-tab-btn" data-target="view-dashboard">Dashboard</button></li>
                    <li><button type="button" class="nav-tab-btn active" data-target="view-tasks">Tasks</button></li>
                    <li><button type="button" class="nav-tab-btn" data-target="view-calendar">Calendar</button></li>
                    <li><button type="button" class="nav-tab-btn nav-form-cta" id="nav-btn-open-form" aria-haspopup="dialog" aria-expanded="false">+ Tambah Tugas</button></li>
                </ul>
            </nav>

            <!-- User Menu Settings & Profile -->
            <div class="user-controls">
                <button type="button" id="btn-theme-toggle" class="icon-btn" title="Ganti Tema Tampilan (Obsidian, Midnight, Slate, Light)" aria-label="Ganti Tema Tampilan">
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

                <!-- Search, Filter, Sort & Switcher View -->
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

                        <!-- Interaksi 1: Kontrol Sorting Data Proyek -->
                        <div class="sort-dropdown-wrapper">
                            <label for="sort-task-select" class="sr-only">Urutkan Berdasarkan</label>
                            <div class="select-wrapper">
                                <svg class="icon-svg sort-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="21" y1="10" x2="3" y2="10"></line>
                                    <line x1="21" y1="6" x2="7" y2="6"></line>
                                    <line x1="21" y1="14" x2="3" y2="14"></line>
                                    <line x1="21" y1="18" x2="7" y2="18"></line>
                                </svg>
                                <select id="sort-task-select" class="select-sort" aria-label="Urutkan tugas perkuliahan">
                                    <option value="deadline-asc">Tenggat Terdekat</option>
                                    <option value="deadline-desc">Tenggat Terjauh</option>
                                    <option value="priority-desc">Prioritas Tertinggi</option>
                                    <option value="priority-asc">Prioritas Terendah</option>
                                    <option value="title-asc">Judul (A-Z)</option>
                                    <option value="title-desc">Judul (Z-A)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Interaksi 2: Tombol Ringkasan Cepat -->
                        <button type="button" id="btn-quick-summary" class="btn-filter" aria-label="Buka ringkasan tugas" title="Ringkasan dan Analitik Tugas">
                            <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="20" x2="18" y2="10"></line>
                                <line x1="12" y1="20" x2="12" y2="4"></line>
                                <line x1="6" y1="20" x2="6" y2="14"></line>
                            </svg>
                            <span>Ringkasan</span>
                        </button>
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

                <!-- ============================================================
                     FITUR MINI DASHBOARD: PROGRES AKADEMIK (FETCH API & JSON)
                     Sesuai Instruksi Tugas Kuliah Pemrograman Web
                     ============================================================ -->
                <div class="mini-dashboard-container" id="mini-dashboard-container" role="region" aria-labelledby="heading-mini-dashboard">
                    <div class="mini-dashboard-header">
                        <div class="mini-dashboard-title-group">
                            <div class="mini-dashboard-badge">
                                <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <span>Live Asynchronous Feed (Fetch API)</span>
                            </div>
                            <h2 id="heading-mini-dashboard" class="mini-dashboard-title">Mini Dashboard Progres Akademik</h2>
                            <p class="mini-dashboard-subtitle">
                                Ringkasan performa mata kuliah, kuota tugas terselesaikan, dan jadwal akademik yang dimuat dinamis dari endpoint JSON lokal.
                            </p>
                        </div>
                        <div class="mini-dashboard-actions">
                            <!-- Tombol Muat Ulang / Reload Data -->
                            <button type="button" id="btn-refresh-mini-dashboard" class="btn-filter" title="Muat ulang data dari endpoint JSON" aria-label="Muat ulang data akademik">
                                <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="23 4 23 10 17 10"></polyline>
                                    <polyline points="1 20 1 14 7 14"></polyline>
                                    <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                                </svg>
                                <span>Muat Ulang</span>
                            </button>

                            <!-- Switcher Tampilan: Kartu vs Tabel -->
                            <div class="view-switcher" role="tablist" aria-label="Pilih Mode Tampilan Mini Dashboard">
                                <button type="button" id="tab-mini-cards" class="view-tab active" role="tab" aria-selected="true" aria-controls="mini-dashboard-cards-container">
                                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="3" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="3" width="7" height="7"></rect>
                                        <rect x="14" y="14" width="7" height="7"></rect>
                                        <rect x="3" y="14" width="7" height="7"></rect>
                                    </svg>
                                    <span>Kartu</span>
                                </button>
                                <button type="button" id="tab-mini-table" class="view-tab" role="tab" aria-selected="false" aria-controls="mini-dashboard-table-container">
                                    <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="8" y1="6" x2="21" y2="6"></line>
                                        <line x1="8" y1="12" x2="21" y2="12"></line>
                                        <line x1="8" y1="18" x2="21" y2="18"></line>
                                        <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                        <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                        <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                    </svg>
                                    <span>Tabel</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Profil Mahasiswa & Statistik Agregat Bar (Diisi Dinamis) -->
                    <div id="mini-dashboard-student-bar" class="mini-dashboard-student-bar" style="display: none;">
                        <!-- JS generated content -->
                    </div>

                    <!-- Toolbar Pencarian & Filter -->
                    <div class="mini-dashboard-toolbar" id="mini-dashboard-toolbar" style="display: none;">
                        <div class="mini-toolbar-search">
                            <div class="search-input-wrapper">
                                <span class="search-icon" aria-hidden="true">
                                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </span>
                                <input type="search" id="mini-dash-search" placeholder="Cari mata kuliah, kode, atau dosen..." aria-label="Cari mata kuliah di mini dashboard">
                            </div>
                        </div>

                        <div class="mini-toolbar-filters">
                            <!-- Filter Kategori -->
                            <div class="select-wrapper">
                                <svg class="icon-svg sort-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                <select id="mini-dash-filter-category" class="select-sort" aria-label="Filter kategori mata kuliah">
                                    <option value="all">Semua Kategori</option>
                                    <option value="Praktikum">Praktikum</option>
                                    <option value="Teori">Teori</option>
                                    <option value="Proyek">Proyek</option>
                                </select>
                            </div>

                            <!-- Filter Status -->
                            <div class="select-wrapper">
                                <svg class="icon-svg sort-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12 6 12 12 16 14"></polyline>
                                </svg>
                                <select id="mini-dash-filter-status" class="select-sort" aria-label="Filter status perkuliahan">
                                    <option value="all">Semua Status</option>
                                    <option value="Aktif">Status: Aktif</option>
                                    <option value="Perhatian">Status: Perhatian</option>
                                    <option value="Tuntas">Status: Tuntas</option>
                                </select>
                            </div>

                            <span id="mini-dash-count-indicator" class="mini-dash-count-indicator"></span>
                        </div>
                    </div>

                    <!-- 1. LOADING STATE -->
                    <div id="mini-dashboard-loading" class="mini-dashboard-state mini-dashboard-loading" role="status" aria-live="polite">
                        <div class="mini-loading-spinner" aria-hidden="true"></div>
                        <p class="mini-loading-text">Memuat data ringkasan akademik via Fetch API...</p>
                        <span class="mini-loading-subtext">Mengakses endpoint data/academic-dashboard.json</span>
                    </div>

                    <!-- 2. ERROR STATE -->
                    <div id="mini-dashboard-error" class="mini-dashboard-state mini-dashboard-error" role="alert" style="display: none;">
                        <div class="mini-error-icon" aria-hidden="true">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <h3 class="mini-error-title">Gagal Memuat Data Mini Dashboard</h3>
                        <p id="mini-dashboard-error-message" class="mini-error-message">Terjadi kendala jaringan atau berkas data JSON tidak dapat diakses.</p>
                        <button type="button" id="btn-mini-dashboard-retry" class="btn-new-task" style="margin-top: 1rem;">
                            <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path>
                            </svg>
                            <span>Coba Muat Ulang</span>
                        </button>
                    </div>

                    <!-- 3. DATA LOADED STATE (KARTU & TABEL) -->
                    <div id="mini-dashboard-content" class="mini-dashboard-content" style="display: none;">
                        <!-- Container Kartu -->
                        <div id="mini-dashboard-cards-container" class="mini-courses-grid" role="region" aria-label="Daftar Kartu Mata Kuliah">
                            <!-- Diisi dinamis oleh JS -->
                        </div>

                        <!-- Container Tabel Semantik -->
                        <div id="mini-dashboard-table-container" class="mini-table-wrapper" style="display: none;" role="region" aria-label="Tabel Progres Mata Kuliah">
                            <table class="mini-dashboard-table">
                                <thead>
                                    <tr>
                                        <th scope="col">Kode &amp; Mata Kuliah</th>
                                        <th scope="col">Kategori</th>
                                        <th scope="col">SKS</th>
                                        <th scope="col">Dosen Pengampu</th>
                                        <th scope="col">Progres Tugas</th>
                                        <th scope="col">Tenggat Terdekat</th>
                                        <th scope="col">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="mini-dashboard-table-body">
                                    <!-- Diisi dinamis oleh JS -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Hasil Pencarian Kosong -->
                        <div id="mini-dashboard-empty" class="mini-dashboard-empty" style="display: none;">
                            <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="var(--text-muted)" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <p>Tidak ada mata kuliah yang cocok dengan kriteria pencarian atau filter.</p>
                            <button type="button" id="btn-mini-reset-filter" class="btn-filter" style="margin-top: 0.5rem;">Reset Filter</button>
                        </div>
                    </div>
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

                <!-- KOTAK RINGKASAN ERROR AKSESIBEL (ERROR SUMMARY BOX) -->
                <div id="form-error-summary" class="form-error-summary" role="alert" aria-live="assertive" tabindex="-1" hidden>
                    <div class="error-summary-header">
                        <svg class="icon-svg error-summary-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <strong id="error-summary-title">Terdapat beberapa kesalahan pengisian formulir:</strong>
                    </div>
                    <ul id="error-summary-list" class="error-summary-list"></ul>
                </div>

                <!-- FIELDSET 1: INFORMASI POKOK TUGAS -->
                <fieldset class="form-fieldset">
                    <legend class="form-legend">Informasi Pokok Tugas</legend>

                    <div class="form-row">
                        <div class="form-field">
                            <div class="form-label-row">
                                <label for="input-judul" id="label-judul">
                                    Judul Tugas <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                                <span id="hint-judul" class="field-hint">Min. 5 karakter</span>
                            </div>
                            <input type="text" id="input-judul" name="judul" required aria-required="true" aria-invalid="false" aria-describedby="hint-judul error-judul" placeholder="Contoh: Implementasi Normalisasi Database" maxlength="100">
                            <div id="error-judul" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field">
                            <div class="form-label-row">
                                <label for="input-matkul" id="label-matkul">
                                    Mata Kuliah <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                                <span id="hint-matkul" class="field-hint">Min. 3 karakter</span>
                            </div>
                            <input type="text" id="input-matkul" name="matkul" list="course-suggestions" required aria-required="true" aria-invalid="false" aria-describedby="hint-matkul error-matkul" placeholder="Contoh: Pemrograman Web Dasar" maxlength="60">
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

                <!-- FIELDSET 2: JADWAL & PRIORITAS -->
                <fieldset class="form-fieldset">
                    <legend class="form-legend">Jadwal & Prioritas</legend>

                    <div class="form-row">
                        <div class="form-field" style="flex: 1.4;">
                            <div class="form-label-row">
                                <label for="input-deadline" id="label-deadline">
                                    Tenggat Waktu (Deadline) <span class="required-mark" aria-hidden="true">*</span>
                                    <span class="sr-only">(Wajib diisi)</span>
                                </label>
                                <span id="hint-deadline" class="field-hint">Waktu pengumpulan</span>
                            </div>
                            <input type="datetime-local" id="input-deadline" name="deadline" required aria-required="true" aria-invalid="false" aria-describedby="hint-deadline error-deadline">
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
                                <label for="input-priority" id="label-priority">Prioritas Urgensi</label>
                                <span id="hint-priority" class="field-hint">Tingkat penting</span>
                            </div>
                            <select id="input-priority" name="priority" aria-describedby="hint-priority error-priority" aria-invalid="false">
                                <option value="high">High Priority</option>
                                <option value="medium" selected>Medium Priority</option>
                                <option value="low">Low Priority</option>
                            </select>
                            <div id="error-priority" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field" style="flex: 1;">
                            <div class="form-label-row">
                                <label for="input-status" id="label-status">Status Kolom</label>
                                <span id="hint-status" class="field-hint">Posisi Kanban</span>
                            </div>
                            <select id="input-status" name="status" aria-describedby="hint-status error-status" aria-invalid="false">
                                <option value="todo" selected>Planned</option>
                                <option value="inprogress">In progress</option>
                                <option value="done">Completed</option>
                                <option value="overdue">Overdue / &lt; 24h</option>
                            </select>
                            <div id="error-status" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </fieldset>

                <!-- FIELDSET 3: INTEGRASI E-LEARNING & CATATAN -->
                <fieldset class="form-fieldset">
                    <legend class="form-legend">Integrasi E-Learning & Catatan</legend>

                    <div class="form-row">
                        <div class="form-field" style="flex: 2;">
                            <div class="form-label-row">
                                <label for="input-url" id="label-url">Link Pengumpulan LMS / Classroom</label>
                                <span id="hint-url" class="field-hint">Opsional (http/https)</span>
                            </div>
                            <input type="url" id="input-url" name="url_pengumpulan" placeholder="https://elearning.kampus.ac.id/mod/assign/..." aria-describedby="hint-url error-url" aria-invalid="false">
                            <div id="error-url" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>

                        <div class="form-field" style="flex: 1;">
                            <div class="form-label-row">
                                <label for="input-url-label" id="label-url-label">Label Pintasan Tombol</label>
                                <span id="hint-url-label" class="field-hint">Maks. 20 karakter</span>
                            </div>
                            <input type="text" id="input-url-label" name="url_label" placeholder="Contoh: LMS atau Classroom" maxlength="20" aria-describedby="hint-url-label error-url-label" aria-invalid="false">
                            <div id="error-url-label" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-field" style="grid-column: 1 / -1;">
                            <div class="form-field-header">
                                <div class="form-label-row">
                                    <label for="input-notes" id="label-notes">Catatan & Petunjuk Tugas</label>
                                    <span id="hint-notes" class="field-hint">Maks. 500 karakter</span>
                                </div>
                                <div class="notes-tools">
                                    <span id="notes-char-counter" class="char-counter" aria-live="polite">0 / 500</span>
                                    <button type="button" id="btn-paste-clipboard" class="btn-paste-clipboard" title="Tempel otomatis dari Clipboard">
                                        <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                                        </svg>
                                        <span id="paste-clipboard-label">Salin dari Clipboard</span>
                                    </button>
                                </div>
                            </div>
                            <textarea id="input-notes" name="notes" rows="3" placeholder="Salin instruksi atau ketentuan format pengumpulan di sini..." maxlength="500" aria-describedby="hint-notes error-notes notes-char-counter" aria-invalid="false"></textarea>
                            <div id="error-notes" class="field-error-msg" role="alert" aria-live="polite"></div>
                        </div>
                    </div>
                </fieldset>

                <div class="form-actions-row">
                    <button type="submit" id="btn-submit-task" class="btn-new-task">
                        <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span id="save-btn-label">Simpan Kartu Tugas</span>
                    </button>
                    <button type="button" id="btn-reset-form" class="btn-reset">
                        Batal
                    </button>
                    <span class="form-keyboard-hint">
                        Pintasan: <kbd>Ctrl</kbd> + <kbd>Enter</kbd>
                    </span>
                </div>
            </form>
        </div>
    </dialog>

    <!-- DIALOG / MODAL DETAIL RINCIAN TUGAS (INTERAKSI 2) -->
    <dialog id="task-detail-modal" class="app-dialog task-dialog" aria-labelledby="detail-task-title" aria-modal="true">
        <div class="dialog-content task-dialog-content">
            <div class="dialog-header">
                <div>
                    <span class="detail-course-tag" id="detail-course-name">Mata Kuliah</span>
                    <h2 id="detail-task-title" class="dialog-title" style="margin-top: 0.35rem;">Judul Tugas Kuliah</h2>
                </div>
                <button type="button" id="btn-close-detail-modal" class="btn-close-dialog" aria-label="Tutup rincian tugas">
                    <svg class="icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="detail-meta-grid">
                <div class="detail-meta-card">
                    <span class="meta-label">Batas Waktu (Deadline)</span>
                    <strong id="detail-deadline-date" class="meta-value">-</strong>
                    <span id="detail-countdown" class="meta-sub">-</span>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Prioritas</span>
                    <div id="detail-priority-badge" class="meta-badge-slot"></div>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Status Alur Kerja</span>
                    <div id="detail-status-badge" class="meta-badge-slot"></div>
                </div>
                <div class="detail-meta-card">
                    <span class="meta-label">Tautan E-Learning</span>
                    <div id="detail-lms-slot" class="meta-badge-slot"></div>
                </div>
            </div>

            <div class="detail-section">
                <div class="detail-section-header">
                    <h3 class="detail-section-title">Instruksi & Catatan Penugasan</h3>
                    <button type="button" id="btn-copy-detail-notes" class="btn-copy-clipboard" title="Salin instruksi tugas">
                        <svg class="icon-svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        <span id="copy-notes-label">Salin Catatan</span>
                    </button>
                </div>
                <div id="detail-instructions-box" class="detail-notes-box">
                    Tidak ada catatan instruksi khusus.
                </div>
            </div>

            <div class="detail-section">
                <h3 class="detail-section-title">Ubah Status Cepat</h3>
                <div class="detail-status-actions" id="detail-status-actions-container">
                    <button type="button" class="btn-status-pill" data-status="todo">Planned</button>
                    <button type="button" class="btn-status-pill" data-status="inprogress">In progress</button>
                    <button type="button" class="btn-status-pill" data-status="done">Completed</button>
                    <button type="button" class="btn-status-pill" data-status="overdue">Overdue / &lt; 24h</button>
                </div>
            </div>

            <div class="dialog-footer-actions">
                <button type="button" id="btn-edit-from-detail" class="btn-new-task">
                    <svg class="icon-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    <span>Edit Tugas</span>
                </button>
                <button type="button" id="btn-close-detail-secondary" class="btn-reset">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    <!-- DIALOG / MODAL RINGKASAN & ANALITIK (INTERAKSI 2) -->
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
                    <div class="summary-stat-num" id="sum-completed-tasks" style="color: #86efac;">0</div>
                    <div class="summary-stat-lbl">Selesai</div>
                </div>
                <div class="summary-stat-box">
                    <div class="summary-stat-num" id="sum-rate-tasks" style="color: #facc15;">0%</div>
                    <div class="summary-stat-lbl">Tingkat Tuntas</div>
                </div>
            </div>

            <div style="margin-top: 1.25rem;">
                <h3 class="detail-section-title">Distribusi per Mata Kuliah</h3>
                <div id="summary-course-breakdown" style="display: flex; flex-direction: column; gap: 0.65rem; margin-top: 0.6rem; max-height: 200px; overflow-y: auto;">
                    <!-- Diisi dinamis oleh JS -->
                </div>
            </div>

            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="button" id="btn-close-summary-bottom" class="btn-reset">
                    Tutup
                </button>
            </div>
        </div>
    </dialog>

    <!-- DIALOG / MODAL PENGATURAN & PROFIL (INTERAKSI 3) -->
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

            <!-- Interaksi 3: Preferensi Tampilan Menggunakan Web Storage (localStorage) -->
            <div class="auth-divider">
                <span>preferensi tampilan (localstorage)</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.85rem; margin-bottom: 1.25rem;">
                <div>
                    <label for="select-theme" style="display: block; font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.35rem;">Tema Tampilan:</label>
                    <select id="select-theme" class="select-sort" style="width: 100%;">
                        <option value="obsidian">Dark Obsidian (Default)</option>
                        <option value="midnight">Midnight Navy Blue</option>
                        <option value="slate">Slate Minimalist Dark</option>
                        <option value="light">Clean Pearl Light</option>
                    </select>
                </div>

                <div>
                    <label for="select-density" style="display: block; font-size: 0.825rem; color: var(--text-muted); margin-bottom: 0.35rem;">Kerapatan Kartu (Density):</label>
                    <select id="select-density" class="select-sort" style="width: 100%;">
                        <option value="comfortable">Comfortable (Spacious)</option>
                        <option value="compact">Compact (Padat)</option>
                    </select>
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
