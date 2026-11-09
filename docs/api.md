# Dokumentasi API - TaskTrack (Modul 11)
**Resource Utama:** `tasks`  
**Base URL:** `/api/tasks.php` (atau `/api/tasks` via routing rewrite)  
**Spesifikasi:** Modul 11 - CRUD Full-Stack, Validation, dan Error Handling  
**Standard Response:** JSON Format konsisten dengan HTTP Status Codes RFC 7231  

---

## 1. Ringkasan Endpoint

| HTTP Method | Endpoint URL | Fungsi / Tujuan | Kode Sukses | Kode Gagal |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/tasks.php` | Mengambil koleksi data tugas (Read All) dengan dukungan Search, Filter, Sort, & Pagination | `200 OK` | `401 Unauthorized`, `500 Server Error` |
| `GET` | `/api/tasks.php?id={id}` | Mengambil data rincian satu tugas spesifik berdasarkan ID (Read Detail) | `200 OK` | `400 Bad Request`, `404 Not Found` |
| `POST` | `/api/tasks.php` | Menambahkan kartu tugas kuliah baru ke database (Create) | `201 Created` | `400 Bad Request`, `500 Server Error` |
| `PUT` | `/api/tasks.php?id={id}` | Memperbarui seluruh data tugas yang sudah ada (Update) | `200 OK` | `400 Bad Request`, `404 Not Found` |
| `PATCH` | `/api/tasks.php?id={id}` | Memperbarui status alur kerja tugas saja (Partial Update: Planned, In progress, Done) | `200 OK` | `400 Bad Request`, `404 Not Found` |
| `DELETE` | `/api/tasks.php?id={id}` | Menghapus tugas dari sistem dengan verifikasi keberadaan data (Delete) | `200 OK` | `400 Bad Request`, `404 Not Found` |

---

## 2. Format Respon Standar (Consistent JSON Envelope)

Setiap respon dari API TaskTrack selalu menggunakan struktur amplop JSON konsisten:

### A. Struktur Respon Sukses (`status: "success"`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "message": "Operasi berhasil dijalankan.",
  "data": { ... },
  "pagination": {
    "total_records": 15,
    "page": 1,
    "limit": 10,
    "total_pages": 2
  }
}
```

### B. Struktur Respon Gagal (`status: "error"`)
```json
{
  "status": "error",
  "timestamp": "2026-11-09 13:30:00",
  "error": {
    "code": 400,
    "message": "Validasi formulir tugas gagal. Periksa kolom yang diisi.",
    "fields": {
      "title": "Judul tugas wajib diisi.",
      "priority": "Prioritas tidak valid. Pilihan: low, medium, high."
    }
  }
}
```

---

## 3. Spesifikasi Rinci Setiap Endpoint

### 3.1. `GET /api/tasks.php` (Read Collection)
Mengambil daftar tugas milik pengguna yang sedang login.

#### Query Parameters
- `search` *(string, opsional)*: Kata kunci pencarian judul, deskripsi, atau kategori. Contoh: `?search=ERD`
- `category_id` *(int, opsional)*: Filter berdasarkan ID kategori mata kuliah. Contoh: `?category_id=1`
- `status` *(string, opsional)*: Filter status alur kerja (`todo`, `in_progress`, `done`). Contoh: `?status=todo`
- `priority` *(string, opsional)*: Filter prioritas (`low`, `medium`, `high`). Contoh: `?priority=high`
- `page` *(int, default: 1)*: Nomor halaman pagination.
- `limit` *(int, default: 10)*: Jumlah data per halaman.
- `sort_by` *(string, default: "due_date")*: Kolom pengurutan (`due_date`, `created_at`, `priority`, `title`).
- `order` *(string, default: "ASC")*: Arah pengurutan (`ASC` atau `DESC`).

#### Contoh Respon Sukses (`200 OK`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 05:35:07",
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "category_id": 1,
      "title": "Implementasi Mini Dashboard & Fetch API",
      "description": "Tugas 9 membuat REST API sederhana PHP",
      "status": "done",
      "priority": "high",
      "due_date": "2026-10-16",
      "created_at": "2026-11-03 18:58:53",
      "category_name": "Pemrograman Web Lanjut",
      "color_code": "#10b981"
    },
    {
      "id": 2,
      "user_id": 1,
      "category_id": 1,
      "title": "Perancangan ERD & Database MySQL",
      "description": "Tugas 10 membuat skema database 3 tabel dan PDO",
      "status": "in_progress",
      "priority": "high",
      "due_date": "2026-10-23",
      "created_at": "2026-11-03 18:58:53",
      "category_name": "Pemrograman Web Lanjut",
      "color_code": "#10b981"
    }
  ],
  "pagination": {
    "total_records": 3,
    "page": 1,
    "limit": 10,
    "total_pages": 1
  }
}
```

---

### 3.2. `GET /api/tasks.php?id={id}` (Read Detail)
Mengambil informasi satu tugas secara lengkap.

#### Request Parameters
- `id` *(int, wajib)*: ID tugas yang ingin diambil.

#### Respon Sukses (`200 OK`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "data": {
    "id": 1,
    "user_id": 1,
    "category_id": 1,
    "title": "Implementasi Mini Dashboard & Fetch API",
    "description": "Tugas 9 membuat REST API sederhana PHP",
    "status": "done",
    "priority": "high",
    "due_date": "2026-10-16",
    "created_at": "2026-11-03 18:58:53",
    "category_name": "Pemrograman Web Lanjut",
    "color_code": "#10b981"
  }
}
```

#### Respon Error (`404 Not Found`)
```json
{
  "status": "error",
  "timestamp": "2026-11-09 13:30:00",
  "error": {
    "code": 404,
    "message": "Tugas dengan ID 99999 tidak ditemukan atau bukan milik Anda."
  }
}
```

---

### 3.3. `POST /api/tasks.php` (Create Task)
Membuat kartu tugas baru. Operasi ini dilindungi oleh transaksi database PDO atomik.

#### Request Headers
- `Content-Type: application/json`
- `Accept: application/json`

#### Request Body
```json
{
  "title": "Laporan Praktikum Modul 11",
  "course": "Pemrograman Web Lanjut",
  "description": "Menyusun dokumentasi REST API dan matriks pengujian",
  "due_date": "2026-11-20",
  "priority": "high",
  "status": "todo"
}
```

#### Aturan Validasi Sisi Server
1. `title`: Wajib diisi, tipe string, minimal 3 karakter, maksimal 150 karakter.
2. `due_date`: Format tanggal harus valid (`YYYY-MM-DD` atau ISO string).
3. `priority`: Harus bernilai salah satu dari: `'low'`, `'medium'`, `'high'`.
4. `status`: Harus bernilai salah satu dari: `'todo'`, `'in_progress'` (atau `'inprogress'`), `'done'`.
5. `category_name` / `course`: Jika kategori belum ada di database, sistem secara otomatis membuatnya dalam transaksi.

#### Respon Sukses (`201 Created`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "message": "Tugas berhasil dibuat.",
  "data": {
    "id": 7,
    "user_id": 1,
    "category_id": 1,
    "title": "Laporan Praktikum Modul 11",
    "description": "Menyusun dokumentasi REST API dan matriks pengujian",
    "status": "todo",
    "priority": "high",
    "due_date": "2026-11-20",
    "created_at": "2026-11-09 13:30:00",
    "category_name": "Pemrograman Web Lanjut",
    "color_code": "#10b981"
  }
}
```

#### Respon Validasi Gagal (`400 Bad Request`)
```json
{
  "status": "error",
  "timestamp": "2026-11-09 13:30:00",
  "error": {
    "code": 400,
    "message": "Validasi formulir tugas gagal. Periksa kolom yang diisi.",
    "fields": {
      "title": "Judul tugas wajib diisi minimal 3 karakter.",
      "priority": "Prioritas tidak valid. Pilihan: low, medium, high."
    }
  }
}
```

---

### 3.4. `PUT /api/tasks.php?id={id}` (Update Task)
Memperbarui data tugas secara menyeluruh.

#### Request Parameters
- `id` *(int, wajib)*: ID tugas yang diperbarui.

#### Request Body
```json
{
  "title": "Laporan Praktikum Modul 11 - Final Revisi",
  "course": "Pemrograman Web Lanjut",
  "description": "Telah diuji dengan unit test dan lulus 12/12 matriks pengujian",
  "due_date": "2026-11-22",
  "priority": "high",
  "status": "in_progress"
}
```

#### Respon Sukses (`200 OK`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "message": "Tugas berhasil diperbarui.",
  "data": {
    "id": 7,
    "user_id": 1,
    "title": "Laporan Praktikum Modul 11 - Final Revisi",
    "status": "in_progress",
    "priority": "high",
    "due_date": "2026-11-22"
  }
}
```

#### Respon Error (`404 Not Found`)
Jika ID tugas tidak ada di database:
```json
{
  "status": "error",
  "timestamp": "2026-11-09 13:30:00",
  "error": {
    "code": 404,
    "message": "Tugas dengan ID 999 tidak ditemukan untuk diperbarui."
  }
}
```

---

### 3.5. `PATCH /api/tasks.php?id={id}` (Update Status / Drag & Drop)
Memperbarui status tugas saja (misalnya ketika kartu di-drag antar kolom pada Kanban board).

#### Request Body
```json
{
  "status": "done"
}
```

#### Respon Sukses (`200 OK`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "message": "Status tugas berhasil diperbarui.",
  "data": {
    "id": 7,
    "status": "done"
  }
}
```

---

### 3.6. `DELETE /api/tasks.php?id={id}` (Delete Task)
Menghapus tugas dari database. Disertai verifikasi eksistensi data (`existence check`) untuk mencegah penghapusan fiktif atau idempotency bug.

#### Respon Sukses (`200 OK`)
```json
{
  "status": "success",
  "timestamp": "2026-11-09 13:30:00",
  "message": "Tugas ID 7 berhasil dihapus secara permanen.",
  "data": {
    "id": 7
  }
}
```

#### Respon Error (`404 Not Found`)
Jika data sudah dihapus atau tidak ditemukan:
```json
{
  "status": "error",
  "timestamp": "2026-11-09 13:30:00",
  "error": {
    "code": 404,
    "message": "Tugas dengan ID 7 tidak ditemukan atau sudah dihapus sebelumnya."
  }
}
```

---

## 4. Keamanan & Database Integrity

1. **SQL Injection Defense**: Seluruh query SQL di `models/TaskModel.php` dieksekusi menggunakan **PDO Prepared Statements** dengan parameter binding bertipe data ketat (`PDO::PARAM_INT`, `PDO::PARAM_STR`). Tidak ada konkatenasi string SQL mentah.
2. **Transaksi Atomik (ACID Safety)**:
   - Operasi `createTask()` dan `updateTask()` dibungkus dalam blok `beginTransaction()`, `commit()`, dan `rollBack()`.
   - Jika pembuatan kategori baru berhasil tetapi penyimpanan tugas gagal, sistem otomatis melakukan rollback penuh sehingga tidak ada orphaned data.
3. **Fail-Safe Generic Error Handling**:
   - Jika terjadi error basis data fatal (`PDOException`), API menangkapnya pada Global Boundary Handler dan mengembalikan `500 Internal Server Error` dengan pesan ramah pengguna tanpa membocorkan nama tabel, password DB, atau stack trace.
4. **Validasi Dua Lapis (Defense in Depth)**:
   - Validasi sisi klien (`js/taskProcessor.js` & `js/app.js`) memberikan feedback instan (0ms).
   - Validasi sisi server (`api/tasks.php`) menjamin integritas data terhadap request via API tool (cURL, Postman, Fetch bypass).

---

## 5. Matriks Hasil Pengujian Otomatis (Test Suite)

Pengujian dilakukan menggunakan automated script `tests/test_crud_api.php` dengan 12 skenario pengujian:

| No | Skenario Pengujian | Endpoint / Model Method | Ekspektasi | Hasil | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| 1 | Read Collection | `GET /api/tasks.php` | Mengembalikan array tugas dan pagination | Ditemukan 3 data, pagination valid | **PASS** |
| 2 | Pencarian Keyword | `GET /api/tasks.php?search=ERD` | Filter tugas yang mengandung kata 'ERD' | Ditemukan tugas ERD secara tepat | **PASS** |
| 3 | Filter Status | `GET /api/tasks.php?status=todo` | Hanya mengambil tugas berstatus 'todo' | Filter akurat, jumlah sesuai | **PASS** |
| 4 | Pagination | `GET /api/tasks.php?page=1&limit=2` | Menampilkan tepat 2 baris dari total data | Page 1 memuat 2 tugas, total page: 2 | **PASS** |
| 5 | Read by ID Valid | `GET /api/tasks.php?id=1` | Mengembalikan rincian data tugas ID = 1 | Ditemukan: Implementasi Mini Dashboard | **PASS** |
| 6 | Read by ID Invalid | `GET /api/tasks.php?id=99999` | Mengembalikan respons `404 Not Found` | Model mengembalikan `null` | **PASS** |
| 7 | Create Task Atomik | `POST /api/tasks.php` | Menyimpan tugas baru via transaksi PDO | Tersimpan dengan auto-increment ID baru | **PASS** |
| 8 | Update Task Data | `PUT /api/tasks.php?id={id}` | Memperbarui judul & status tugas | Data di MySQL terupdate sempurna | **PASS** |
| 9 | Patch Status | `PATCH /api/tasks.php?id={id}` | Mengubah status alur ke `in_progress` | Kolom status berubah di DB | **PASS** |
| 10 | Delete Valid | `DELETE /api/tasks.php?id={id}` | Menghapus record dari tabel `tasks` | Record terhapus permanen dari MySQL | **PASS** |
| 11 | Delete Repeated (404) | `DELETE /api/tasks.php?id={id}` | Menolak penghapusan data non-existent | Mengembalikan `false` / `404 Not Found` | **PASS** |
| 12 | Server-Side Validation | `POST /api/tasks.php` (tanpa judul) | Menolak payload cacat dengan `400 Bad Request` | Error ditolak dengan pesan kolom spesifik | **PASS** |

**Tingkat Keberhasilan:** 12 / 12 Test Cases (100% Passed)

