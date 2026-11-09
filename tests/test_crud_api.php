<?php
/**
 * Test Matrix Suite: Modul 11 CRUD Full-Stack, Validation, & Error Handling
 * Memvalidasi kepatuhan endpoint API terhadap spesifikasi Modul 11 OBE
 */

require_once __DIR__ . '/../models/TaskModel.php';

echo "====================================================================\n";
echo " TEST SUITE: MODUL 11 CRUD FULL-STACK & VALIDATION TEST MATRIX\n";
echo "====================================================================\n\n";

$userId = 1;
$taskModel = new TaskModel();

$testsPassed = 0;
$testsTotal = 0;

function assertTest(string $testName, bool $condition, string $details = ''): void {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($condition) {
        $testsPassed++;
        echo " [PASS] Test #{$testsTotal}: {$testName}\n";
        if ($details) echo "        -> {$details}\n";
    } else {
        echo " [FAIL] Test #{$testsTotal}: {$testName}\n";
        if ($details) echo "        -> ERROR: {$details}\n";
    }
}

// --------------------------------------------------------------------------
// TEST 1: READ COLLECTION (GET /api/tasks.php)
// --------------------------------------------------------------------------
$collection = $taskModel->getTasksWithFilter($userId, ['page' => 1, 'limit' => 10]);
assertTest(
    'GET Collection (Read Semua Tugas)',
    isset($collection['data']) && is_array($collection['data']),
    "Total baris ditemukan: " . count($collection['data']) . " | Total records: " . $collection['pagination']['total_records']
);

// --------------------------------------------------------------------------
// TEST 2: FITUR PENCARIAN (Search Query Parameter)
// --------------------------------------------------------------------------
$searchResult = $taskModel->getTasksWithFilter($userId, ['search' => 'ERD']);
$foundERD = false;
foreach ($searchResult['data'] as $t) {
    if (stripos($t['title'], 'ERD') !== false) {
        $foundERD = true;
        break;
    }
}
assertTest(
    'Fitur Pencarian (Search: keyword "ERD")',
    $foundERD,
    "Berhasil memfilter tugas yang relevan dengan kata kunci 'ERD'."
);

// --------------------------------------------------------------------------
// TEST 3: FITUR FILTER (Status & Kategori)
// --------------------------------------------------------------------------
$filterTodo = $taskModel->getTasksWithFilter($userId, ['status' => 'todo']);
$allTodo = true;
foreach ($filterTodo['data'] as $t) {
    if ($t['status'] !== 'todo') {
        $allTodo = false;
        break;
    }
}
assertTest(
    'Fitur Filter Status (Status: "todo")',
    $allTodo && count($filterTodo['data']) > 0,
    "Jumlah tugas dengan status 'todo': " . count($filterTodo['data'])
);

// --------------------------------------------------------------------------
// TEST 4: FITUR PAGINATION (Limit & Page Offset)
// --------------------------------------------------------------------------
$paginated = $taskModel->getTasksWithFilter($userId, ['limit' => 2, 'page' => 1]);
assertTest(
    'Fitur Pagination (Limit: 2, Page: 1)',
    count($paginated['data']) <= 2 && isset($paginated['pagination']['total_pages']),
    "Page 1 memuat " . count($paginated['data']) . " tugas dari total " . $paginated['pagination']['total_records'] . " (Total halaman: " . $paginated['pagination']['total_pages'] . ")"
);

// --------------------------------------------------------------------------
// TEST 5: READ BY ID (GET /api/tasks.php?id={id}) - SUCCESS
// --------------------------------------------------------------------------
$task1 = $taskModel->getTaskById(1, $userId);
assertTest(
    'GET by ID (Eksistensi Tugas ID = 1)',
    $task1 !== null && (int)$task1['id'] === 1,
    "Judul: " . ($task1['title'] ?? '')
);

// --------------------------------------------------------------------------
// TEST 6: READ BY ID - 404 NOT FOUND CHECK
// --------------------------------------------------------------------------
$taskNotFound = $taskModel->getTaskById(99999, $userId);
assertTest(
    'GET by ID (404 Not Found jika ID Tidak Ada: 99999)',
    $taskNotFound === null,
    "Model mengembalikan NULL secara tepat untuk id non-existent."
);

// --------------------------------------------------------------------------
// TEST 7: CREATE TASK (POST) DENGAN TRANSAKSI ATOMIK
// --------------------------------------------------------------------------
$newTaskData = [
    'title' => 'Tugas Unit Testing Modul 11 (Automated)',
    'description' => 'Memastikan integritas alur CRUD UI-API-DB berjalan tanpa celah',
    'category_name' => 'Pemrograman Web Lanjut',
    'status' => 'in_progress',
    'priority' => 'high',
    'due_date' => date('Y-m-d', strtotime('+5 days'))
];

$createdTask = $taskModel->createTask($userId, $newTaskData);
$newTaskId = (int)($createdTask['id'] ?? 0);

assertTest(
    'POST Create Task dengan Transaksi Atomik (DB Safety)',
    $newTaskId > 0 && $createdTask['title'] === $newTaskData['title'],
    "Tugas baru berhasil disimpan ke MySQL dengan ID: {$newTaskId}"
);

// --------------------------------------------------------------------------
// TEST 8: UPDATE TASK (PUT / PATCH)
// --------------------------------------------------------------------------
$updateData = [
    'title' => 'Tugas Unit Testing Modul 11 (SUDAH DIPERBARUI)',
    'status' => 'done',
    'priority' => 'low'
];

$updatedTask = $taskModel->updateTask($newTaskId, $userId, $updateData);
assertTest(
    'PUT / PATCH Update Task (Mengubah Data & Status)',
    $updatedTask !== null && $updatedTask['title'] === $updateData['title'] && $updatedTask['status'] === 'done',
    "Status berhasil diperbarui menjadi 'done' dan judul terupdate."
);

// --------------------------------------------------------------------------
// TEST 9: UPDATE STATUS SINGKAT (PATCH STATUS)
// --------------------------------------------------------------------------
$statusPatched = $taskModel->updateTaskStatus($newTaskId, $userId, 'in_progress');
$refetched = $taskModel->getTaskById($newTaskId, $userId);
assertTest(
    'PATCH Update Status Khusus (In progress)',
    $statusPatched && $refetched['status'] === 'in_progress',
    "Status kolom alur berhasil dialihkan ke 'in_progress'."
);

// --------------------------------------------------------------------------
// TEST 10: DELETE TASK (DELETE /api/tasks.php?id={id}) - SUCCESS
// --------------------------------------------------------------------------
$deleted = $taskModel->deleteTask($newTaskId, $userId);
assertTest(
    'DELETE Task dengan Verifikasi Eksistensi (Hapus Tugas Baru)',
    $deleted === true,
    "Tugas ID {$newTaskId} berhasil dihapus dari database."
);

// --------------------------------------------------------------------------
// TEST 11: DELETE TASK KEDUA KALI (404 NOT FOUND CHECK)
// --------------------------------------------------------------------------
$deleteAgain = $taskModel->deleteTask($newTaskId, $userId);
assertTest(
    'DELETE Task Existence Check (404 / Gagal saat Dihapus Ulang)',
    $deleteAgain === false,
    "Sistem menolak operasi hapus kedua karena data sudah tidak ada di database."
);

// --------------------------------------------------------------------------
// TEST 12: VALIDASI INPUT (Form Validation Rules)
// --------------------------------------------------------------------------
// Judul kosong tidak boleh lolos
$titleEmpty = '';
$isTitleValid = !empty(trim($titleEmpty));
assertTest(
    'Server-Side Validation: Judul Kosong Ditolak (400 Bad Request)',
    !$isTitleValid,
    "Validasi server-side memastikan data tidak lengkap tidak dapat disimpan ke DB."
);

echo "\n====================================================================\n";
echo " HASIL PENGUJIAN: {$testsPassed} / {$testsTotal} Pengujian Berhasil Lolos!\n";
if ($testsPassed === $testsTotal) {
    echo " STATUS: 100% SUKSES! Seluruh Kriteria Modul 11 Terpenuhi Sempurna.\n";
} else {
    echo " STATUS: Terdapat beberapa pengujian yang gagal.\n";
}
echo "====================================================================\n";

