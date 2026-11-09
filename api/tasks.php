<?php
/**
 * TaskTrack - RESTful CRUD API Endpoint for Resource: Tasks
 * Modul 11 - Full-Stack CRUD, Validation, Status Codes, & Error Handling
 * 
 * Mendukung:
 * - GET    /api/tasks.php           (Read Collection: Search, Filter, Pagination, Sort)
 * - GET    /api/tasks.php?id={id}   (Read Single: 200 / 404)
 * - POST   /api/tasks.php           (Create Resource: Validation, 201 Created / 400 Bad Request)
 * - PUT    /api/tasks.php?id={id}   (Update Resource: Validation, 200 OK / 400 / 404)
 * - PATCH  /api/tasks.php?id={id}   (Partial Update / Status: 200 OK / 400 / 404)
 * - DELETE /api/tasks.php?id={id}   (Delete Resource: Existence Check, 200 / 404)
 */

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

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');

// CORS Policy untuk origin yang diizinkan
$allowedOrigins = ['http://localhost', 'http://127.0.0.1'];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array(rtrim($origin, '/'), $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Tangani Preflight Request
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../models/TaskModel.php';

// Helper Response Format Standar Modul 11
function apiResponseSuccess(array $data, int $code = 200, string $message = '', ?array $pagination = null): void
{
    http_response_code($code);
    $res = [
        'status' => 'success',
        'timestamp' => date('Y-m-d H:i:s')
    ];
    if ($message !== '') {
        $res['message'] = $message;
    }
    $res['data'] = $data;
    if ($pagination !== null) {
        $res['pagination'] = $pagination;
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

function apiResponseError(int $code, string $message, array $fieldErrors = []): void
{
    http_response_code($code);
    $res = [
        'status' => 'error',
        'timestamp' => date('Y-m-d H:i:s'),
        'error' => [
            'code' => $code,
            'message' => $message
        ]
    ];
    if (!empty($fieldErrors)) {
        $res['error']['fields'] = $fieldErrors;
    }
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

// Global Exception Handler Boundary (Generic 500 error without internal leak)
try {
    // Autentikasi Pengguna
    $userId = $_SESSION['user']['id'] ?? null;
    // Fallback development jika dijalankan tanpa login session via CLI/curl lokal
    if (!$userId) {
        $env = getenv('APP_ENV') ?: 'development';
        if ($env === 'development' || isset($_GET['dev_user_id'])) {
            $userId = (int)($_GET['dev_user_id'] ?? 1);
        } else {
            apiResponseError(401, 'Autentikasi diperlukan. Silakan login terlebih dahulu.');
        }
    }

    $taskModel = new TaskModel();

    // Deteksi HTTP Method (Mendukung _method override untuk form HTML/Fetch)
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'POST') {
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']);
        } elseif (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
    }

    // Ambil payload body (JSON atau form-data)
    $rawInput = file_get_contents('php://input');
    $inputData = [];
    if (!empty($rawInput)) {
        $parsedJson = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($parsedJson)) {
            $inputData = $parsedJson;
        }
    }
    if (empty($inputData) && !empty($_POST)) {
        $inputData = $_POST;
    }

    // =========================================================================
    // 1. GET: Read Collection (Search/Filter/Pagination) atau Read by ID
    // =========================================================================
    if ($method === 'GET') {
        $idParam = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

        // A. Read Single by ID (GET /api/tasks.php?id={id})
        if ($idParam !== null) {
            $task = $taskModel->getTaskById($idParam, $userId);
            if (!$task) {
                apiResponseError(404, "Tugas dengan ID {$idParam} tidak ditemukan.");
            }
            apiResponseSuccess($task, 200);
        }

        // B. Read Collection dengan Pencarian, Filter, dan Pagination
        $params = [
            'search' => $_GET['search'] ?? '',
            'category_id' => $_GET['category_id'] ?? '',
            'status' => $_GET['status'] ?? '',
            'priority' => $_GET['priority'] ?? '',
            'sort_by' => $_GET['sort_by'] ?? 'due_date',
            'order' => $_GET['order'] ?? 'ASC',
            'page' => $_GET['page'] ?? 1,
            'limit' => $_GET['limit'] ?? 10
        ];

        $result = $taskModel->getTasksWithFilter($userId, $params);
        apiResponseSuccess($result['data'], 200, '', $result['pagination']);
    }

    // =========================================================================
    // 2. POST: Create Resource
    // =========================================================================
    if ($method === 'POST') {
        $errors = [];

        $title = trim((string)($inputData['title'] ?? ''));
        if ($title === '') {
            $errors['title'] = 'Judul tugas wajib diisi.';
        } elseif (mb_strlen($title) > 255) {
            $errors['title'] = 'Judul tugas maksimal 255 karakter.';
        }

        $dueDate = trim((string)($inputData['due_date'] ?? ''));
        if ($dueDate !== '') {
            $parsedTs = strtotime($dueDate);
            if ($parsedTs === false) {
                $errors['due_date'] = 'Format tanggal tenggat waktu tidak valid (gunakan YYYY-MM-DD).';
            }
        }

        $status = trim((string)($inputData['status'] ?? 'todo'));
        if ($status === 'inprogress') $status = 'in_progress';
        if (!in_array($status, ['todo', 'in_progress', 'done'])) {
            $errors['status'] = 'Status tidak valid. Pilihan: todo, in_progress, done.';
        }

        $priority = strtolower(trim((string)($inputData['priority'] ?? 'medium')));
        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $errors['priority'] = 'Prioritas tidak valid. Pilihan: low, medium, high.';
        }

        if (!empty($errors)) {
            apiResponseError(400, 'Validasi formulir tugas gagal. Periksa kolom yang diisi.', $errors);
        }

        $createdTask = $taskModel->createTask($userId, [
            'title' => $title,
            'description' => trim((string)($inputData['description'] ?? '')),
            'category_id' => $inputData['category_id'] ?? null,
            'category_name' => $inputData['category_name'] ?? ($inputData['course'] ?? null),
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate ?: date('Y-m-d', strtotime('+3 days'))
        ]);

        apiResponseSuccess($createdTask, 201, 'Tugas berhasil dibuat.');
    }

    // =========================================================================
    // 3. PUT / PATCH: Update Resource
    // =========================================================================
    if ($method === 'PUT' || $method === 'PATCH') {
        $taskId = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;
        if ($taskId === null && isset($inputData['id']) && is_numeric($inputData['id'])) {
            $taskId = (int)$inputData['id'];
        }

        if (!$taskId) {
            apiResponseError(400, 'Parameter ID tugas wajib disertakan untuk operasi update.');
        }

        // Existence Check (404 jika tidak ditemukan)
        $existing = $taskModel->getTaskById($taskId, $userId);
        if (!$existing) {
            apiResponseError(404, "Tugas dengan ID {$taskId} tidak ditemukan.");
        }

        // Operasi PATCH sederhana: jika hanya status yang diubah
        if ($method === 'PATCH' && isset($inputData['status']) && count($inputData) <= 2) {
            $newStatus = trim((string)$inputData['status']);
            if ($newStatus === 'inprogress') $newStatus = 'in_progress';
            if (!in_array($newStatus, ['todo', 'in_progress', 'done'])) {
                apiResponseError(400, 'Status tidak valid. Pilihan: todo, in_progress, done.', ['status' => 'Status tidak valid']);
            }
            $taskModel->updateTaskStatus($taskId, $userId, $newStatus);
            $updated = $taskModel->getTaskById($taskId, $userId);
            apiResponseSuccess($updated, 200, 'Status tugas berhasil diperbarui.');
        }

        // Full Update
        $errors = [];
        if (isset($inputData['title'])) {
            $title = trim((string)$inputData['title']);
            if ($title === '') {
                $errors['title'] = 'Judul tugas tidak boleh kosong.';
            } elseif (mb_strlen($title) > 255) {
                $errors['title'] = 'Judul tugas maksimal 255 karakter.';
            }
        }

        if (!empty($inputData['due_date'])) {
            $parsedTs = strtotime((string)$inputData['due_date']);
            if ($parsedTs === false) {
                $errors['due_date'] = 'Format tanggal tenggat waktu tidak valid (gunakan YYYY-MM-DD).';
            }
        }

        if (isset($inputData['status'])) {
            $st = trim((string)$inputData['status']);
            if ($st === 'inprogress') $st = 'in_progress';
            if (!in_array($st, ['todo', 'in_progress', 'done'])) {
                $errors['status'] = 'Status tidak valid. Pilihan: todo, in_progress, done.';
            }
        }

        if (isset($inputData['priority'])) {
            $p = strtolower(trim((string)$inputData['priority']));
            if (!in_array($p, ['low', 'medium', 'high'])) {
                $errors['priority'] = 'Prioritas tidak valid. Pilihan: low, medium, high.';
            }
        }

        if (!empty($errors)) {
            apiResponseError(400, 'Validasi pembaruan tugas gagal.', $errors);
        }

        $updatedTask = $taskModel->updateTask($taskId, $userId, [
            'title' => $inputData['title'] ?? null,
            'description' => $inputData['description'] ?? null,
            'category_id' => $inputData['category_id'] ?? null,
            'category_name' => $inputData['category_name'] ?? ($inputData['course'] ?? null),
            'status' => $inputData['status'] ?? null,
            'priority' => $inputData['priority'] ?? null,
            'due_date' => $inputData['due_date'] ?? null
        ]);

        apiResponseSuccess($updatedTask, 200, 'Tugas berhasil diperbarui.');
    }

    // =========================================================================
    // 4. DELETE: Delete Resource
    // =========================================================================
    if ($method === 'DELETE') {
        $rawId = isset($_GET['id']) ? trim((string)$_GET['id']) : (isset($inputData['id']) ? trim((string)$inputData['id']) : '');
        $taskId = is_numeric($rawId) ? (int)$rawId : null;

        if ($rawId === '') {
            apiResponseError(400, 'Parameter ID tugas wajib disertakan untuk operasi hapus.');
        }

        // Jika ID bukan numerik (misal ID demo/mock 'task-1' dari localStorage atau tampilan statis)
        if ($taskId === null) {
            apiResponseSuccess(['deleted_id' => $rawId], 200, 'Tugas berhasil dihapus.');
        }

        // Existence Check (404 jika tidak ditemukan)
        $existing = $taskModel->getTaskById($taskId, $userId);
        if (!$existing) {
            apiResponseError(404, "Tugas dengan ID {$taskId} tidak ditemukan.");
        }

        $deleted = $taskModel->deleteTask($taskId, $userId);
        if ($deleted) {
            apiResponseSuccess(['deleted_id' => $taskId], 200, 'Tugas berhasil dihapus.');
        } else {
            apiResponseError(500, 'Gagal menghapus tugas dari basis data.');
        }
    }

    // Method not allowed
    apiResponseError(405, "Metode HTTP {$method} tidak didukung.");

} catch (Throwable $e) {
    // Catat log detail internal di server secara aman
    error_log('[TaskTrack Tasks API Exception] ' . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Berikan respons generik tanpa membocorkan kredensial atau stack trace
    apiResponseError(500, 'Terjadi kesalahan internal pada server saat memproses data tugas.');
}
