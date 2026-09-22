# TaskTrack - Web Tracker Tugas & Deadline Kuliah

TaskTrack adalah aplikasi visual tracker berbasis Kanban yang dirancang untuk mempermudah mahasiswa mengelola antrean tugas serta mengantisipasi batas waktu pengumpulan (deadline). Dibangun sebagai **Landing Page responsif dan aksesibel** menggunakan **PHP Standar**, **HTML5 Semantik**, **CSS Custom Properties**, **Flexbox & CSS Grid**, serta **Vanilla JavaScript**.

---

## Struktur Halaman & Komponen Reusable

Halaman dibangun menggunakan PHP standar dengan fokus semantik HTML, hierarki heading logis, serta modularitas komponen:

* **Navigasi (`<nav>`):** Navigasi responsif dengan tautan cepat menuju Beranda (`#hero`), Fitur (`#features`), Kanban Board (`#kanban-section`), dan Form Tambah Tugas (`#task-management`), dilengkapi tombol toggle menu untuk layar mobile.
* **Konten Utama (`<main id="main-content">`):** Menampung seluruh konten dan alur kerja aplikasi dengan skip link di posisi teratas.
* **4 Section Semantik:**
  1. **Section Hero (`<section id="hero">`):** Headline persuasif, CTA ganda, banner opt-in notifikasi web push, dan ringkasan metrik statistik.
  2. **Section Fitur Unggulan (`<section id="features">`):** Value proposition aplikasi menampilkan 6 keunggulan utama mahasiswa.
  3. **Section Papan Kanban (`<section id="kanban-section">`):** Workspace visual 3 kolom (To Do, In Progress, Done) dengan filter mata kuliah dan live search.
  4. **Section Form Tambah Tugas (`<section id="task-management">`):** Formulir CRUD tugas lengkap dengan link LMS dan quick paste instruksi e-learning.
* **Minimal 3 Komponen Reusable:**
  1. **`TaskCard` (`components/task-card.php` / `<article class="task-card">`):** Komponen kartu tugas independen dengan header mata kuliah, lencana urgensi waktu, judul tugas `<h4>`, countdown timer, cuplikan instruksi, link pengumpulan LMS, serta tombol aksi alur kerja (move, edit, delete).
  2. **`StatMetricCard` (`components/stat-card.php` / `<div class="metric-card">`):** Komponen kartu metrik ringkasan dengan label metrik, nilai numerik real-time, dan deskripsi konteks.
  3. **`FeatureCard` (`components/feature-card.php` / `<div class="feature-card">`):** Komponen kartu keunggulan fitur landing page dengan gelembung ikon, tag kategori, judul `<h3>`, dan deskripsi manfaat.
* **Article (`<article>`):** Komponen kartu tugas semantik independen dengan informasi mata kuliah, urgensi waktu, dan link pengumpulan.
* **Form (`<form>`):** Formulir penambahan dan pembaruan tugas dengan validasi input standar HTML5.
* **Footer (`<footer>`):** Metadata hak cipta, navigasi sekunder, serta panduan pintasan tombol keyboard.

---

## Pengujian & Dokumentasi Tampilan Multi-Device

TaskTrack telah diuji pada tiga kelompok ukuran layar utama menggunakan **CSS Grid**, **Flexbox**, **CSS Custom Properties (`:root`)**, dan **Media Queries**:

| Ukuran Layar / Viewport | Tata Letak (Grid & Flexbox) | Penyesuaian Komponen & Responsivitas | Status Uji |
| :--- | :--- | :--- | :---: |
| **Desktop**<br>(`> 1024px`, misal 1440x900) | - Header horizontal penuh dengan menu sejajar.<br>- Metrics Grid: 4 kolom sejajar.<br>- Features Grid: 3 kolom sejajar.<br>- Kanban Grid: 3 kolom sejajar (To Do, In Progress, Done).<br>- Footer: 3 kolom (Info, Navigasi, Panduan Keyboard). | Tata letak luas dengan drag-and-drop antar kolom sangat leluasa, tampilan dashboard profesional, dan efisiensi ruang optimal. | Lulus Uji |
| **Tablet**<br>(`768px - 1024px`, misal iPad 768x1024) | - Metrics Grid bertransformasi menjadi 2 kolom x 2 baris.<br>- Features Grid bertransformasi menjadi 2 kolom.<br>- Kanban Grid mengalir vertikal yang nyaman di-scroll.<br>- Footer bertransformasi menjadi 2 kolom. | Padding dan ukuran font otomatis disesuaikan proporsional, target sentuh tombol tetap nyaman tanpa elemen berhimpitan. | Lulus Uji |
| **Mobile**<br>(`< 768px`, misal iPhone/Android 375x667 - 414x896) | - Header dilengkapi tombol hamburger toggle responsif.<br>- Navigasi collapsible dapat dibuka-tutup dengan aksesibilitas `aria-expanded`.<br>- Metrics Grid, Features Grid, dan Kanban Grid bertransformasi menjadi 1 kolom (single-column flow).<br>- Form row dan tombol aksi bertumpuk vertikal (full-width). | Target sentuh tombol memenuhi standar aksesibilitas mobile (minimal 44x44px), tidak ada horizontal overflow (scroll samping tidak diinginkan). | Lulus Uji |

---

## Checklist Aksesibilitas Dasar (Accessibility Audit)

Sesuai standar WCAG 2.1 AA dan rubrik penilaian proyek:

- [x] **Hierarki Heading Teratur:** Penggunaan `<h1>` tunggal pada judul landing page, diikuti `<h2>` untuk setiap section utama, `<h3>` untuk kolom Kanban dan kartu fitur, serta `<h4>` untuk judul kartu tugas `<article>`.
- [x] **Form Labels & Associations:** Semua input form dihubungkan secara eksplisit menggunakan atribut `for` dan `id` berpasangan, dilengkapi bantuan deskripsi `aria-describedby`.
- [x] **Keyboard Navigation & :focus-visible:** Seluruh elemen interaktif (tombol, tautan, input, dan kartu) memiliki indikator visual `:focus-visible` kontras tinggi (outline 3px solid `#facc15` dengan offset 2px) yang jelas bagi pengguna navigasi keyboard (<kbd>Tab</kbd>, <kbd>Enter</kbd>, <kbd>Spasi</kbd>, <kbd>Esc</kbd>).
- [x] **Skip to Content Link:** Tautan `.skip-link` tersedia di paling atas halaman untuk melompati navigasi langsung ke `<main id="main-content">`.
- [x] **ARIA Semantics & Live Region:** Menggunakan landmark semantik (`role="banner"`, `role="contentinfo"`, `role="region"`, `role="list"`, `role="listitem"`), `aria-label`, serta elemen `<div id="a11y-announcer" aria-live="polite">` untuk mengumumkan perubahan status tugas secara real-time ke pembaca layar (screen reader).
- [x] **Kontras Warna & Teks Alternatif:** Rasio kontras teks terhadap latar belakang memenuhi standar WCAG AA (> 4.5:1). Indikator tenggat waktu tidak hanya mengandalkan warna, melainkan dilengkapi label teks eksplisit (Kritis, Perhatian, Aman, Selesai).

---

## Sistem Desain CSS Custom Properties (Dark Obsidian Theme)

Stylesheet utama ([css/style.css](file:///c:/laragon/www/TaskTrack/css/style.css)) dibangun menggunakan design token variabel CSS bertema Dark Obsidian:
- **Warna Pokok & Aksen:** `--color-accent: #facc15;`, `--color-primary: #facc15;`
- **Warna Permukaan:** `--bg-body: #090a0e;`, `--bg-surface: #141720;`, `--bg-surface-elevated: #1c202c;`, `--border-color: #232838;`
- **Tipografi Kontras Tinggi:** `--text-main: #f1f5f9;`, `--text-muted: #94a3b8;`
- **Indikator Skala Urgensi:**
  - Kritis (< 24 Jam): `--urgency-critical: #ef4444;`
  - Perhatian (< 3 Hari): `--urgency-warning: #f59e0b;`
  - Aman (> 3 Hari): `--urgency-safe: #38bdf8;`
  - Selesai: `--urgency-done: #64748b;`

---

## Modul Pengolahan Data JavaScript (`js/taskProcessor.js`)

TaskTrack mengimplementasikan modul JavaScript ES6 mandiri ([js/taskProcessor.js](file:///c:/laragon/www/TaskTrack/js/taskProcessor.js)) untuk pemrosesan, transformasi, kalkulasi analitik, dan validasi data tugas perkuliahan:

1. **Array of Objects:**
   - Mengelola kumpulan objek tugas perkuliahan `{ id, title, course, deadline, status, priority, lms_url, lms_label, instructions }`.
   - Menghasilkan array of objects terstruktur pada hasil agregasi matkul (`groupTasksByCourse`) dan tugas terkayakan (`enrichTasks`).
2. **Function & Arrow Function:**
   - Fungsi reguler: `validateTask()`, `validateTaskBatch()`, `filterTasks()`, `enrichTasks()`, `sortTasks()`, `calculateTaskStatistics()`, `groupTasksByCourse()`, `groupTasksByDate()`, `parseTasksFromJSON()`, `exportTasksToJSON()`.
   - Arrow functions: `calculateRemainingHours`, `isDeadlineOverdue`, `getUrgencyLevel`, `getUrgentTasks`, `getOverdueTasks`, `getUniqueCourses`, `findTaskById`, `findTaskIndex`, `hasCriticalDeadlines`, `getTopUrgentTasks`, serta seluruh callback array methods.
3. **Method Array Relevan:**
   - `.filter()`: Pemfilteran dinamis berdasarkan matkul, status, prioritas, pencarian teks bebas, serta isolasi tugas overdue/urgent.
   - `.map()`: Transformasi data tugas terkayakan (*enriched*) dengan penambahan properti sisa jam, badge urgensi, dan ekstraksi daftar matkul unik.
   - `.reduce()`: Agregasi metrik statistik dashboard (total, jumlah per-status, jumlah per-prioritas, rasio tuntas %) dan pengelompokan (*grouping*) beban matkul serta agenda per tanggal.
   - `.find()` & `.findIndex()`: Pencarian objek tugas dan indeks posisi berdasarkan ID unik untuk alur edit dan drag-and-drop.
   - `.some()` & `.every()`: Pengecekan adanya deadline kritis (< 24 jam) menggunakan `.some()`, dan validasi integritas seluruh elemen batch menggunakan `.every()`.
   - `.sort()`: Pengurutan non-mutating berdasarkan deadline terdekat, tingkat prioritas (`high` > `medium` > `low`), atau nama judul/matkul.
   - `.slice()`: Pembatasan kuota daftar Top-N tugas paling mendesak pada ringkasan dashboard.
4. **Error Handling Terstruktur:**
   - Kelas kustom `TaskDataError` yang mewarisi `Error` bawaan JavaScript.
   - Validasi ketat tipe data (array checking, string non-kosong, validitas status & prioritas, serta validitas parsing tanggal).
   - Pengamanan operasi berisiko (parsing JSON dan kalkulasi tanggal) menggunakan blok `try...catch` dengan mekanisme pengembalian fallback aman (*graceful degradation*).
5. **Modularitas ES6 (Export / Import):**
   - Menggunakan sintaks `export` (named exports dan default export) pada `js/taskProcessor.js`.
   - Dimuat menggunakan `import` pada `js/app.js` dan dideklarasikan dengan `<script type="module" src="js/app.js"></script>` pada `index.php`.

### Menjalankan Unit Test Mandiri Modul
Modul dilengkapi test suite otomatis ([js/test-taskProcessor.js](file:///c:/laragon/www/TaskTrack/js/test-taskProcessor.js)) yang menguji 47 skenario fungsi, method array, dan error handling:
```bash
& "C:\laragon\bin\nodejs\node-v14\node.exe" js/test-taskProcessor.js
```

---

## Cara Menjalankan Proyek

### Opsi 1: Menggunakan Laragon (Direkomendasikan)
1. Buka aplikasi **Laragon**.
2. Pastikan folder proyek berada di `C:\laragon\www\TaskTrack`.
3. Klik tombol **Start All** pada Laragon.
4. Buka peramban web dan akses:
   ```
   http://localhost/TaskTrack
   atau
   http://localhost:8080/TaskTrack
   ```

### Opsi 2: Menggunakan PHP Built-in Server
Jalankan perintah berikut pada terminal:
```bash
& "C:\laragon\bin\php\php-8.5.10-Win32-vs17-x64\php.exe" -S 127.0.0.1:8000
```
Buka peramban pada alamat: `http://127.0.0.1:8000`