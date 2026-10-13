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

## Sistem Desain CSS Custom Properties (GitHub Dark & Discord Light)

Stylesheet utama ([css/style.css](file:///c:/laragon/www/TaskTrack/css/style.css)) dibangun menggunakan design token variabel CSS dengan 2 pilihan tema tampilan:
- **Tema 1: GitHub Dark (Default):**
  - Permukaan & Kanvas: `--bg-body: #0d1117;`, `--card-surface: #161b22;`, `--card-border: #30363d;`
  - Tipografi & Aksen: `--text-main: #e6edf3;`, `--accent-yellow: #238636;` (GitHub Green), `--brand-blue: #2f81f7;`
- **Tema 2: Discord Light:**
  - Permukaan & Kanvas: `--bg-body: #f2f3f5;`, `--card-surface: #ffffff;`, `--card-border: #e3e5e8;`
  - Tipografi & Aksen: `--text-main: #2e3338;`, `--accent-yellow: #5865f2;` (Discord Blurple)
- **Indikator Skala Urgensi & Status:**
  - Kritis (< 24 Jam): `--urgency-critical: #f85149;`
  - Perhatian (< 3 Hari): `--urgency-warning: #d29922;`
  - Aman (> 3 Hari): `--urgency-safe: #2f81f7;`
  - Selesai: `--urgency-done: #3fb950;`

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

---

## Tiga Interaksi Bermakna DOM & Event

TaskTrack menerapkan tiga interaksi bermakna berbasis DOM, Event Listener terstruktur, dan Web Storage (`localStorage`) untuk data non-sensitif sesuai instruksi:

### 1. Interaksi 1: Filter, Pencarian Live, & Sorting Data Proyek
* **Pencarian Real-Time (Live Search):**
  - **Event:** `input` pada `#search-task-input`.
  - **DOM:** Memfilter judul tugas, nama mata kuliah, dan catatan petunjuk tugas secara instan pada tampilan Kanban Board dan List View.
* **Filter Berdasarkan Kategori Mata Kuliah:**
  - **Event:** `change` pada `#filter-course-select`.
  - **DOM:** Membatasi kartu yang ditampilkan hanya untuk mata kuliah yang dipilih atau menampilkan seluruh mata kuliah (`all`). Opsi dropdown di-generate dinamis menggunakan modul `getUniqueCourses(tasks)`.
* **Pengurutan Fleksibel (Multi-Criteria Sorting):**
  - **Event:** `change` pada `#sort-task-select`.
  - **DOM:** Mengurutkan kartu tugas secara non-mutating berdasarkan:
    1. Tenggat Terdekat (`deadline-asc`)
    2. Tenggat Terjauh (`deadline-desc`)
    3. Prioritas Tertinggi (`priority-desc`)
    4. Prioritas Terendah (`priority-asc`)
    5. Judul A-Z (`title-asc`)
    6. Judul Z-A (`title-desc`)
  - **Aksesibilitas:** Setiap pembaruan hasil filter dan pengurutan diumumkan ke pembaca layar melalui elemen `#a11y-announcer`.

### 2. Interaksi 2: Tombol Detail, Pengubah Status, & Ringkasan Analitik
* **Tombol Detail Tugas (`#task-detail-modal`):**
  - **Event:** `click` pada tombol `.btn-detail` kartu tugas atau klik langsung pada judul kartu `.task-card-title`.
  - **DOM:** Membuka dialog modal semantik `<dialog>` yang menyajikan data rincian tugas secara menyeluruh: lencana prioritas, status alur kerja, tautan pengumpulan LMS aktif, hitung mundur sisa waktu, dan catatan instruksi penugasan.
  - **Salin Catatan ke Clipboard:** Tombol `#btn-copy-detail-notes` dengan Clipboard API dan feedback visual otomatis berganti menjadi label *"Tersalin!"*.
* **Aksi Perubahan Status Cepat:**
  - **Event:** `click` pada pill tombol status (`.btn-status-pill` di dalam dialog detail atau `.btn-move` di kartu).
  - **DOM & State:** Langsung memperbarui status tugas (`todo`, `inprogress`, `done`, `overdue`), memindahkan kartu ke dropzone Kanban yang relevan, memperbarui counter angka kolom, dan menyinkronkan data ke `localStorage`.
* **Tombol Ringkasan Cepat (`#summary-modal`):**
  - **Event:** `click` pada `#btn-quick-summary` di toolbar utama.
  - **DOM:** Menghitung dan merender ringkasan analitik akademik: total tugas, tugas selesai, rasio ketuntasan (%), serta grafik progress bar persentase penyelesaian per mata kuliah semester berjalan.

### 3. Interaksi 3: Preferensi Tema & Tampilan Menggunakan Web Storage (`localStorage`)
* **Penyimpanan Data Non-Sensitif:**
  - Seluruh data preferensi disimpan di browser klien tanpa melibatkan data kredensial atau informasi sensitif:
    1. `tasktrack_theme`: Pilihan tema antarmuka (`dark` untuk GitHub Dark, `light` untuk Discord Light).
    2. `tasktrack_density`: Kerapatan kartu Kanban (`comfortable` atau `compact`).
    3. `tasktrack_view_mode`: Mode tampilan terakhir (`kanban` atau `list`).
    4. `tasktrack_sound_pref`: Preferensi bunyi notifikasi (`true` atau `false`).
    5. `tasktrack_agro_tasks`: Kumpulan data kartu tugas perkuliahan lokal.
* **Theme Switcher 2 Tema (GitHub Dark & Discord Light):**
  - **Event:** `click` pada `#btn-theme-toggle` (header) atau `change` pada `#select-theme` (modal pengaturan).
  - **DOM:** Mengubah atribut `data-theme` pada elemen `<html>` antara `dark` (GitHub Dark) dan `light` (Discord Light), yang secara instan mengganti variabel CSS Custom Properties tanpa kedipan (*zero layout shift*).
* **Restorasi State Otomatis:**
  - Saat pengguna membuka atau menyegarkan (*refresh*) halaman, fungsi `initPreferences()` langsung membaca `localStorage` dan menerapkan tema, kerapatan kartu, serta mode tampilan yang dipilih sebelumnya.

---

## Dokumentasi Alur Event & State

```
+---------------------------------------------------------------------------------+
|                                USER INTERACTION                                 |
+---------------------------------------------------------------------------------+
       |                              |                              |
       v                              v                              v
[Input / Change Event]         [Click Event]                  [Click / Change]
Search, Filter, Sort           Detail & Move Status           Theme & Density Toggle
       |                              |                              |
       v                              v                              v
[Update filterState]           [Update tasks state]           [Apply data-theme/density]
- course / search / sort       - findTaskIndex()              - documentElement.setAttribute
- getProcessedTasks()          - update task.status           - localStorage.setItem()
       |                              |                              |
       v                              v                              v
[DOM Re-render]                [DOM Re-render & Storage]      [CSS Token Switch]
- renderBoard()                - saveTasks() -> localStorage  - Instan tanpa reload
- renderListView()             - renderBoard()                - Persisten saat refresh
- update Counters & A11y       - updateDashboard() & Modal    
```

---

## Refleksi Pengerjaan

1. **Pemisahan Logika Data dan Manipulasi DOM:**
   Memisahkan fungsi pemrosesan data murni ke modul `taskProcessor.js` dan logika interaksi DOM ke `app.js` membuat kode jauh lebih terstruktur, mudah diuji, dan modular.
2. **Pengalaman Pengguna (UX) Melalui Web Storage:**
   Penerapan `localStorage` untuk preferensi tema dan kerapatan kartu memberikan pengalaman aplikasi web yang mulus dan personal bagi mahasiswa tanpa membebani penyimpanan server.
3. **Keterbacaan dan Disiplin Kode:**
   Penggunaan komentar singkat terstruktur (seperti `// 1. KONSTANTA & ENUM`, `// 2. ERROR HANDLING`) membuat navigasi kode cepat, rapi, dan to the point tanpa komentar berlebihan.

---

## Fitur Mini Dashboard (Fetch API & Asynchronous JSON Data Feed)

Sesuai instruksi penugasan pengembangan mini dashboard berbasis API/JSON lokal:

### 1. Endpoint & Sumber Data
* **File JSON Lokal:** `data/academic-dashboard.json`
* **API Proxy Endpoint:** `api/academic-dashboard.php` (header `Content-Type: application/json; charset=utf-8` dan proteksi cache `no-store`)
* **Struktur Data:**
  - `student`: Metadata mahasiswa (Nama, NIM, Program Studi, Semester, Target IPK, Total SKS).
  - `summary`: Agregasi metrik (Total matkul, total tugas, tugas terselesaikan, rasio pemenuhan %, tenggat kritis).
  - `courses`: Array data 6 mata kuliah lengkap dengan kode, nama matkul, kategori (Praktikum, Teori, Proyek), SKS, dosen pengampu, ruang, jadwal, progres penyelesaian tugas, dan rincian tenggat terdekat.

### 2. Implementasi Asinkronus JavaScript
* **Fungsi Utama:** `fetchMiniDashboardData()` pada `js/app.js`.
* **Karakteristik Teknis:**
  - Menggunakan fungsi `async` dan operator `await` untuk pemrosesan asinkronus non-blocking.
  - Memanggil `fetch('data/academic-dashboard.json', { headers: { 'Accept': 'application/json' }, cache: 'no-store' })`.
  - Pemeriksaan eksplisit `response.ok` (status HTTP 200 - 299). Jika `response.ok === false`, melempar error baru menggunakan `throw new Error(...)`.
  - Pengamanan blok `try...catch` lengkap untuk menangkap kegagalan koneksi atau format payload invalid.

### 3. Tiga State Tampilan Mini Dashboard
1. **Loading State (`#mini-dashboard-loading`):**
   - Menampilkan spinner animasi berputar dengan teks status: *"Memuat data ringkasan akademik via Fetch API..."* dan petunjuk endpoint.
   - Dilengkapi atribut aksesibilitas `role="status"` dan `aria-live="polite"`.
2. **Error State (`#mini-dashboard-error`):**
   - Muncul otomatis jika berkas JSON tidak ditemukan, status HTTP di luar 200, atau respon gagal di-parse.
   - Menampilkan kartu peringatan dengan ikon SVG, pesan galat kontekstual, dan tombol **"Coba Muat Ulang"** (`#btn-mini-dashboard-retry`) untuk memicu ulang fungsi `fetchMiniDashboardData()`.
   - Dilengkapi atribut aksesibilitas `role="alert"`.
3. **Data Loaded State (`#mini-dashboard-content`):**
   - Menampilkan bilah informasi mahasiswa (*student metadata bar*) dengan agregasi performa akademik.
   - Menampilkan toolbar pencarian dan filter.
   - Merender data mata kuliah ke dalam 2 format tampilan yang dapat diganti sewaktu-waktu.

### 4. Penyajian Data: Mode Kartu & Mode Tabel Semantik
* **Mode Kartu (Grid Cards):**
  - Grid responsif (`.mini-courses-grid`) bertema Sleek Dark Obsidian.
  - Setiap kartu menampilkan kode matkul, badge kategori (Praktikum, Teori, Proyek), pill status, nama dosen, jadwal/ruang, progress bar persentase penyelesaian tugas, serta kotak pengingat tugas terdekat.
* **Mode Tabel (Data Table):**
  - Tabel semantik `<table>` dengan header terstruktur (`<thead>`) dan baris data (`<tbody>`).
  - Kolom lengkap: Kode & Mata Kuliah, Kategori, SKS, Dosen Pengampu, Progres Tugas, Tenggat Terdekat, dan Status.

### 5. Fitur Pencarian Real-Time & Pemfilteran Ganda
Setelah data berhasil dimuat, pengguna dapat memanipulasi data tanpa memuat ulang halaman:
* **Pencarian Real-Time (`#mini-dash-search`):** Mencari berdasarkan nama mata kuliah, kode matkul, nama dosen, atau ruang kuliah secara instan melalui event `input`.
* **Filter Kategori (`#mini-dash-filter-category`):** Memfilter mata kuliah berdasarkan jenis perkuliahan (Semua Kategori, Praktikum, Teori, Proyek).
* **Filter Status (`#mini-dash-filter-status`):** Memfilter berdasarkan status perkuliahan (Semua Status, Aktif, Perhatian, Tuntas).
* **Indikator Jumlah & Empty State:** Indikator dinamis *"Menampilkan X dari Y mata kuliah"* serta tampilan pesan khusus dan tombol reset jika hasil pencarian tidak ditemukan.

### 6. Dokumentasi Pengujian & Screenshot
* **URL Pengujian Lokal:**
  - Buka halaman web: `http://localhost/TaskTrack`
  - Klik tab navigasi **Dashboard** di bagian atas untuk melihat Mini Dashboard Progres Akademik.
  - Uji pencarian dengan mengetik nama matkul (contoh: "Web" atau "Siber").
  - Uji filter kategori (contoh pilih "Praktikum").
  - Uji pengalihan mode tampilan dengan menekan tombol switcher **Kartu** dan **Tabel**.
  - Uji pemuatan ulang dengan menekan tombol **Muat Ulang**.
* **Screenshot Antarmuka:**
  - Tangkapan layar tampilan kartu, tampilan tabel, dan state pencarian dapat dilihat langsung pada aplikasi saat dibuka di peramban web lokal.

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