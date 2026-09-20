# TaskTrack — Papan Manajemen & Pemantau Deadline Tugas Mahasiswa

TaskTrack adalah aplikasi web manajemen tugas kuliah berbasis papan Kanban 3 kolom interaktif (*To Do*, *In Progress*, *Done*) yang dibangun menggunakan **PHP Standar**, **HTML5 Semantik**, **Modern CSS**, dan **Vanilla JavaScript**. Aplikasi ini dirancang khusus untuk mahasiswa agar dapat memprioritaskan tugas kuliah berdasarkan tenggat waktu (*deadline*), mengintegrasikan tautan pengumpulan ke portal LMS kampus (*Moodle, Google Classroom, Canvas*), serta mempermudah input instruksi tugas panjang secara cepat.

---

## 🚀 Fitur Utama

1. **Autentikasi Pengguna**:
   - Pilihan masuk menggunakan **Google SSO** (Single Sign-On Mahasiswa) atau akun email/kata sandi biasa.
   - Fitur **Ingat Sesi (*Remember Me*)** menggunakan `localStorage` untuk sesi persisten atau `sessionStorage` untuk sesi sekali pakai.
2. **Papan Kanban 3 Kolom**:
   - Visualisasi alur kerja tugas terstruktur: **Belum Dimulai (*To Do*)**, **Sedang Dikerjakan (*In Progress*)**, dan **Selesai (*Done*)**.
3. **Drag & Drop Interaktif**:
   - Memindahkan kartu tugas antar kolom secara mulus menggunakan **HTML5 Drag and Drop API** dengan efek visual *dragover*, serta tombol pintas manual untuk pengguna keyboard dan layar sentuh.
4. **Manajemen Tugas (CRUD)**:
   - Menambah tugas baru, menampilkan rincian tugas, memperbarui (*edit*), dan menghapus (*delete*) kartu tugas dengan konfirmasi yang aman.
5. **Link Pengumpulan Tugas (LMS Shortcut)**:
   - Menyimpan URL halaman penugasan e-learning dan menyediakan tombol pintasan langsung `🔗 Portal LMS` yang membuka tab baru secara aman (`target="_blank" rel="noopener noreferrer"`).
6. **Quick Input Teks Instruksi**:
   - Area teks luas khusus untuk menyalin dan menempel (*copy-paste*) instruksi tugas panjang dari portal e-learning dosen, lengkap dengan tombol pintas baca dari *clipboard*.
7. **Web Push Notification & Opt-in Prompt**:
   - Banner opt-in izin notifikasi browser di awal masuk dashboard.
   - Peringatan otomatis (*pop-up notification*) saat tugas mendekati **H-1 Hari** (< 24 jam) dan **H-3 Jam** sebelum tenggat waktu.
8. **Warna Indikator Kritis (Urgency Color Coding)**:
   - Kartu tugas otomatis berubah warna dan label berdasarkan sisa waktu tenggat:
     - 🔴 **Merah (Kritis)**: Sisa waktu < 24 jam atau telah melewati tenggat.
     - 🟡 **Kuning (Perhatian)**: Sisa waktu < 3 hari (72 jam).
     - 🟢 **Hijau (Aman)**: Sisa waktu > 3 hari.
     - ⚪ **Abu-abu (Selesai)**: Tugas telah tuntas dikumpulkan di kolom *Done*.
9. **Auto-Sorting Deadline**:
   - Algoritma pengurutan otomatis dalam setiap kolom menempatkan tugas dengan tenggat waktu paling dekat di posisi teratas.
10. **Filter Mata Kuliah & Live Search**:
    - Dropdown penyaring dinamis untuk melihat tugas mata kuliah tertentu (*Pemrograman Web Lanjut, Sistem Basis Data, RPL, Jaringan Komputer, dll.*) serta input pencarian judul/materi tugas *realtime*.
11. **Desain Responsif & Modern**:
    - Tampilan adaptif untuk layar HP, tablet, maupun laptop dengan tipografi modern (*Plus Jakarta Sans*), aksen *glassmorphism*, dan kontras tinggi.

---

## 🏛️ Struktur Semantik HTML5

Halaman dibangun dengan memenuhi standar HTML5 semantik dan hierarki heading yang logis:

- **Skip to Content Link**: `<a href="#main-content" class="skip-link">` untuk akses cepat pengguna keyboard.
- **Navigasi (`<nav role="navigation">`)**: Menu navigasi utama, branding logo SVG, status notifikasi, dan tombol akun.
- **Konten Utama (`<main id="main-content">`)**:
  - **Section 1 (`<section id="hero-overview">`)**: `<h1>` judul aplikasi, banner opt-in notifikasi web push, kartu metrik statistik tugas (*Total, Kritis, Sedang Berjalan, Selesai*), dan bilah penyaring (*filter toolbar*).
  - **Section 2 (`<section id="kanban-section">`)**: `<h2>` Papan Kanban Alur Kerja, keterangan warna indikator kritis, 3 kolom alur kerja dengan dropzone, serta kartu-kartu tugas semantik.
  - **Section 3 (`<section id="task-management">`)**: `<h2>` Formulir CRUD tugas lengkap (`<form id="task-form">`), fieldset informasi pokok, integrasi link LMS, dan quick input teks instruksi.
  - **Section 4 (`<section id="accessibility-checklist">`)**: `<h2>` Checklist Evaluasi Aksesibilitas Web Dasar (WCAG 2.1 AA).
- **Elemen Kartu Tugas (`<article class="task-card">`)**: Setiap tugas dibungkus dalam tag `<article>` semantik dengan header mata kuliah, heading `<h4>` judul tugas, penghitung waktu tenggat, cuplikan catatan, tombol pintasan LMS, dan tombol aksi (*move, edit, delete*).
- **Footer (`<footer role="contentinfo">`)**: Hak cipta, navigasi sekunder tautan internal, dan panduan pintasan tombol keyboard (*Tab, Enter, Spasi, Esc, Alt+N*).
- **Dialog Modal (`<dialog id="auth-modal">`)**: Jendela dialog autentikasi dengan tombol Google SSO dan formulir login email.

### Hierarki Heading yang Logis
```
└── <h1> TaskTrack — Pemantau Deadline & Papan Tugas Mahasiswa
    ├── <h2> id="heading-overview" : Filter dan Penyaringan Tugas
    ├── <h2> id="heading-kanban"   : Papan Kanban Alur Kerja
    │   ├── <h3> id="col-title-todo"       : Belum Dimulai (To Do)
    │   ├── <h3> id="col-title-inprogress" : Sedang Dikerjakan (In Progress)
    │   └── <h3> id="col-title-done"       : Selesai (Done)
    │       └── <h4> Judul Kartu Tugas Mahasiswa (<article>)
    ├── <h2> id="heading-management" : Manajemen Tugas & Quick Input Instruksi
    ├── <h2> id="heading-accessibility" : Checklist Aksesibilitas Dasar (WCAG 2.1 AA)
    └── <h3> Panduan Navigasi Footer
```

---

## ♿ Checklist Aksesibilitas Dasar (WCAG 2.1 AA)

| No | Kriteria Aksesibilitas | Implementasi pada TaskTrack | Status |
| :-: | :--- | :--- | :---: |
| 1 | **Semantic HTML5 Structure** | Menggunakan elemen `<header>`, `<nav>`, `<main>`, 4x `<section>`, `<article>`, `<form>`, dan `<footer>`. | ✅ Terpenuhi |
| 2 | **Logical Heading Hierarchy** | Struktur heading runtut dari `<h1>` hingga `<h4>` tanpa ada tingkatan yang terlewati. | ✅ Terpenuhi |
| 3 | **Keyboard Navigability & Skip Link** | Tautan `.skip-link` tersedia untuk melompati navigasi. Seluruh interaksi, modal, dan kartu dapat dinavigasi via tombol <kbd>Tab</kbd>, <kbd>Enter</kbd>, <kbd>Spasi</kbd>, dan <kbd>Esc</kbd>. | ✅ Terpenuhi |
| 4 | **Form Labels & Associations** | Setiap field form dihubungkan secara eksplisit dengan `<label for="...">` dan `id`, dilengkapi teks panduan `aria-describedby`. | ✅ Terpenuhi |
| 5 | **High Contrast & Urgency Indicators** | Rasio kontras teks terhadap latar memenuhi rasio minimum 4.5:1 (WCAG AA). Indikator tenggat dilengkapi teks deskriptif eksplisit selain kode warna. | ✅ Terpenuhi |
| 6 | **ARIA Live Region & Announcements** | Tersedia elemen `<div id="a11y-announcer" aria-live="polite">` yang secara otomatis mengumumkan aksi penambahan, pengeditan, penghapusan, dan perpindahan status tugas untuk pembaca layar (*screen reader*). | ✅ Terpenuhi |

---

## 💻 Cara Menjalankan Proyek

### Opsi 1: Menggunakan Web Server Laragon (Rekomendasi)
1. Buka aplikasi **Laragon**.
2. Pastikan proyek berada di direktori `C:\laragon\www\TaskTrack`.
3. Klik tombol **Start All** pada Laragon.
4. Buka browser dan akses alamat:
   ```
   http://localhost/TaskTrack
   atau
   http://localhost:8080/TaskTrack
   ```

### Opsi 2: Menggunakan PHP Built-in Server
Jalankan perintah berikut pada terminal di dalam folder proyek:
```bash
& "C:\laragon\bin\php\php-8.5.10-Win32-vs17-x64\php.exe" -S 127.0.0.1:8000
```
Lalu buka browser di: `http://127.0.0.1:8000`

---

## 🌿 Riwayat Alur Git & Branching

- **Branch Utama**: `main`
- **Feature Branch**: `feature/struktur-home`
- **Daftar Commit Bermakna**:
  1. `cad46a1` — `feat: struktur semantik html5 dasar dengan navigasi, heading hirarkis, dan konten utama`
  2. `0d78193` — `feat: styling kanban modern, form input tugas, modal auth, dan checklist aksesibilitas`
  3. `07feadb` — `feat: interaktivitas kanban drag-and-drop, filter matkul, quick input teks, dan web push notification`
  4. Pembaruan dokumentasi `README.md` dan `AI_USAGE_LOG.md`.
- **Merge**: Penggabungan dari `feature/struktur-home` ke `main` dan sinkronisasi ke remote repository GitHub `origin`.