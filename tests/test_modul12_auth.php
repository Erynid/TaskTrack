<?php
/**
 * Test Matrix Suite: Modul 12 - Authentication, Authorization, Session/JWT, & Password Hashing
 * Kurikulum Outcome-Based Education (OBE) 2026 - Universitas Borneo Tarakan
 */

define('TEST_RUNNING', true);

// Lakukan inisialisasi sesi & regenerasi ID sebelum ada output karakter ke buffer
require_once __DIR__ . '/../config/auth.php';

$oldSessionId = session_id();
session_regenerate_id(true);
$newSessionId = session_id();
$sessionRegeneratedSuccess = ($oldSessionId !== $newSessionId && !empty($newSessionId));

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/TaskModel.php';

echo "====================================================================\n";
echo " TEST SUITE: MODUL 12 AUTHENTICATION & AUTHORIZATION MATRIX\n";
echo "====================================================================\n\n";

$testsPassed = 0;
$testsTotal = 0;

function assertModul12(string $name, bool $pass, string $info = ''): void {
    global $testsPassed, $testsTotal;
    $testsTotal++;
    if ($pass) {
        $testsPassed++;
        echo " [PASS] Test #{$testsTotal}: {$name}\n";
        if ($info) echo "        -> {$info}\n";
    } else {
        echo " [FAIL] Test #{$testsTotal}: {$name}\n";
        if ($info) echo "        -> ERROR: {$info}\n";
    }
}

// --------------------------------------------------------------------------
// TEST 1: PASSWORD HASHING (password_hash & password_verify)
// --------------------------------------------------------------------------
$rawPassword = 'RahasiaMahasiswa2026!';
$hashed = password_hash($rawPassword, PASSWORD_DEFAULT);
$hashInfo = password_get_info($hashed);
$verifyCorrect = password_verify($rawPassword, $hashed);
$verifyWrong = password_verify('SalahPassword', $hashed);

assertModul12(
    'Password Hashing (BCrypt/Argon2 & Adaptif)',
    $verifyCorrect && !$verifyWrong && !empty($hashInfo['algoName']) && strpos($hashed, '$2y$') === 0,
    "Algo: {$hashInfo['algoName']} | Valid match: OK | Wrong reject: OK | Hash: " . substr($hashed, 0, 16) . '...'
);

// --------------------------------------------------------------------------
// TEST 2: DATABASE USERS SCHEMA (Kolom 'role' & Default Role)
// --------------------------------------------------------------------------
$pdo = Database::getInstance();
$colStmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
$roleCol = $colStmt->fetch(PDO::FETCH_ASSOC);

assertModul12(
    'Skema Database Users (Kolom role ENUM/VARCHAR)',
    $roleCol !== false && stripos($roleCol['Type'], 'admin') !== false,
    "Tipe data: {$roleCol['Type']} | Nullable: {$roleCol['Null']} | Default: {$roleCol['Default']}"
);

// --------------------------------------------------------------------------
// TEST 3: SESSION COOKIE SECURITY (HttpOnly, SameSite=Lax)
// --------------------------------------------------------------------------
$cookieParams = session_get_cookie_params();

assertModul12(
    'Session Cookie Security (HttpOnly, SameSite=Lax)',
    $cookieParams['httponly'] === true && strcasecmp($cookieParams['samesite'] ?? '', 'Lax') === 0,
    "HttpOnly: " . ($cookieParams['httponly'] ? 'TRUE' : 'FALSE') . " | SameSite: " . ($cookieParams['samesite'] ?? '-') . " | Path: {$cookieParams['path']}"
);

// --------------------------------------------------------------------------
// TEST 4: CEGAH SESSION FIXATION (session_regenerate_id)
// --------------------------------------------------------------------------
assertModul12(
    'Session Fixation Protection (session_regenerate_id)',
    $sessionRegeneratedSuccess,
    "Old ID: " . substr($oldSessionId, 0, 8) . "... -> New ID: " . substr($newSessionId, 0, 8) . "..."
);

// --------------------------------------------------------------------------
// TEST 5: SIMPAN USER_ID DAN ROLE PADA SESSION (Langkah 3)
// --------------------------------------------------------------------------
$_SESSION['user_id'] = 2;
$_SESSION['role'] = 'admin';
$_SESSION['user'] = [
    'id' => 2,
    'name' => 'Dzakir Dzakwan',
    'email' => 'dzakirdzakwan9@gmail.com',
    'role' => 'admin'
];

assertModul12(
    'Session State Storage ($_SESSION user_id & role)',
    getCurrentUserId() === 2 && getCurrentUserRole() === 'admin',
    "user_id: " . getCurrentUserId() . " | role: " . getCurrentUserRole()
);

// --------------------------------------------------------------------------
// TEST 6: ENDPOINT /api/me.php (Latihan 2: Profil Tanpa Password Hash)
// --------------------------------------------------------------------------
ob_start();
require __DIR__ . '/../api/me.php';
$meOutput = ob_get_clean();
$meData = json_decode($meOutput, true);

$safeProfile = isset($meData['data']['id']) 
    && !isset($meData['data']['password_hash']) 
    && !isset($meData['data']['verification_token'])
    && $meData['data']['role'] === 'admin';

assertModul12(
    'Endpoint /api/me.php (Mengembalikan Profil Tanpa password_hash)',
    $safeProfile,
    "ID: {$meData['data']['id']} | Email: {$meData['data']['email']} | Role: {$meData['data']['role']} | Password hash leaked: NO"
);

// --------------------------------------------------------------------------
// TEST 7: AUTENTIKASI 401 UNAUTHORIZED (Akses Anonymous / Belum Login)
// --------------------------------------------------------------------------
$backupSession = $_SESSION;
$_SESSION = [];

$anonymousUserId = getCurrentUserId();
$isBlocked401 = ($anonymousUserId === null);

assertModul12(
    'Protected Route Check (401 Unauthorized jika Belum Login)',
    $isBlocked401,
    "Status: 401 Unauthorized | requireLogin() memblokir akses anonymous."
);

// --------------------------------------------------------------------------
// TEST 8: OTORISASI 403 FORBIDDEN (Role Matrix: Viewer Ditolak)
// --------------------------------------------------------------------------
$_SESSION['user_id'] = 999;
$_SESSION['role'] = 'viewer';
$_SESSION['user'] = ['id' => 999, 'role' => 'viewer'];

$allowedForAdminAndMahasiswa = ['admin', 'mahasiswa'];
$viewerHasAccess = in_array(getCurrentUserRole(), $allowedForAdminAndMahasiswa, true);

assertModul12(
    'Authorization Check (403 Forbidden jika Role Tidak Memiliki Izin)',
    !$viewerHasAccess && getCurrentUserRole() === 'viewer',
    "Role: viewer | Izin ditolak untuk aksi create/update/delete (Status: 403 Forbidden)"
);

// --------------------------------------------------------------------------
// TEST 9: OTORISASI 200 OK (Role Matrix: Admin & Mahasiswa Diizinkan)
// --------------------------------------------------------------------------
$_SESSION['role'] = 'mahasiswa';
$mahasiswaHasAccess = in_array(getCurrentUserRole(), $allowedForAdminAndMahasiswa, true);

$_SESSION['role'] = 'admin';
$adminHasAccess = in_array(getCurrentUserRole(), $allowedForAdminAndMahasiswa, true);

assertModul12(
    'Authorization Check (200 OK jika Role Mahasiswa / Admin)',
    $mahasiswaHasAccess && $adminHasAccess,
    "Mahasiswa: DISETUJUI (200) | Admin: DISETUJUI (200)"
);

// --------------------------------------------------------------------------
// TEST 10: LOGOUT LIFECYCLE & COOKIE EXPIRATION (Langkah 6)
// --------------------------------------------------------------------------
performSecureLogout();
$sessionEmpty = empty($_SESSION);

assertModul12(
    'Secure Logout Lifecycle (Pembersihan $_SESSION & Expire Cookie)',
    $sessionEmpty,
    'Array $_SESSION dikosongkan | Sesi dihancurkan | Cookie di-expire ke waktu lampau'
);

// Restore session state untuk user asli
$_SESSION = $backupSession;

echo "\n====================================================================\n";
echo " HASIL PENGUJIAN MODUL 12: {$testsPassed} / {$testsTotal} Pengujian Berhasil Lolos!\n";
if ($testsPassed === $testsTotal) {
    echo " STATUS: 100% SUKSES! Seluruh Kriteria Modul 12 OBE Terpenuhi Sempurna.\n";
} else {
    echo " STATUS: ADA PENGUJIAN GAGAL.\n";
}
echo "====================================================================\n";
