<?php
/**
 * TaskTrack - Central REST API Router (Tugas 10 - Database Integration)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Load .env secara sederhana (jika tidak pakai vlucas/phpdotenv)
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}

require_once __DIR__ . '/../models/TaskModel.php';

function jsonResponse($status, $dataOrMessage, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'payload' => $dataOrMessage
    ]);
    exit;
}

$requestUri = $_SERVER['REQUEST_URI'];

// Routing Endpoint
if (strpos($requestUri, '/dashboard') !== false) {
    try {
        $taskModel = new TaskModel();
        // Ambil data tugas milik user_id = 1 (dzakir)
        $tasks = $taskModel->getTasksByUserId(1);

        jsonResponse('success', [
            'total_tasks' => count($tasks),
            'tasks' => $tasks
        ], 200);
    } catch (Exception $e) {
        jsonResponse('error', 'Gagal mengambil data dari database: ' . $e->getMessage(), 500);
    }
} else {
    jsonResponse('error', 'Endpoint tidak ditemukan', 404);
}