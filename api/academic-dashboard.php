<?php
/**
 * TaskTrack - API Endpoint: Ringkasan Akademik Mini Dashboard
 * Mengembalikan data terstruktur format JSON untuk dikonsumsi via fetch() di frontend.
 * Header: Content-Type application/json; charset=utf-8
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$jsonPath = __DIR__ . '/../data/academic-dashboard.json';

if (!file_exists($jsonPath)) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'message' => 'Berkas data akademik tidak ditemukan di server.'
    ]);
    exit;
}

$content = file_get_contents($jsonPath);
if ($content === false) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Gagal membaca berkas data akademik.'
    ]);
    exit;
}

http_response_code(200);
echo $content;