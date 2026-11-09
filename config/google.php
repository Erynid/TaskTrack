<?php
/**
 * TaskTrack - Google OAuth 2.0 Configuration & Helper Functions
 */

if (!function_exists('loadEnvFile')) {
    function loadEnvFile(): void
    {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim($value);
                    putenv(sprintf('%s=%s', $name, $value));
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

// Pastikan env ter-load
loadEnvFile();

require_once __DIR__ . '/database.php';

function getGoogleConfig(): array
{
    $clientId = getenv('GOOGLE_CLIENT_ID') ?: ($_ENV['GOOGLE_CLIENT_ID'] ?? '');
    $clientSecret = getenv('GOOGLE_CLIENT_SECRET') ?: ($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');

    // Deteksi otomatis URL callback
    $configuredRedirect = getenv('GOOGLE_REDIRECT_URI') ?: ($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
    if (empty($configuredRedirect)) {
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname(dirname($scriptPath))), '/');
        $configuredRedirect = "{$scheme}://{$host}{$baseDir}/auth/google-callback.php";
    }

    return [
        'client_id' => trim($clientId),
        'client_secret' => trim($clientSecret),
        'redirect_uri' => trim($configuredRedirect),
    ];
}

/**
 * Buat URL Login Google OAuth 2.0
 */
function getGoogleAuthUrl(?string $state = null): string
{
    $config = getGoogleConfig();
    if (empty($config['client_id'])) {
        throw new RuntimeException('GOOGLE_CLIENT_ID belum dikonfigurasi di file .env');
    }

    if ($state === null) {
        $state = bin2hex(random_bytes(16));
    }
    $_SESSION['google_oauth_state'] = $state;

    $params = [
        'client_id' => $config['client_id'],
        'redirect_uri' => $config['redirect_uri'],
        'response_type' => 'code',
        'scope' => 'openid profile email',
        'state' => $state,
        'access_type' => 'online',
        'prompt' => 'select_account'
    ];

    return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
}

/**
 * Tukar Authorization Code dengan Access Token & ID Token
 */
function exchangeGoogleCode(string $code): ?array
{
    $config = getGoogleConfig();

    $postData = [
        'code' => $code,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("[Google OAuth Error] Token Exchange failed: HTTP {$httpCode} - {$response} (cURL Err: {$error})");
        return null;
    }

    return json_decode($response, true);
}

/**
 * Mengambil profil pengguna dari Google UserInfo endpoint
 */
function getGoogleUserInfo(string $accessToken): ?array
{
    $ch = curl_init('https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ["Authorization: Bearer {$accessToken}"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("[Google OAuth Error] UserInfo failed: HTTP {$httpCode} - {$response}");
        return null;
    }

    return json_decode($response, true);
}

/**
 * Verifikasi Google ID Token (JWT) via Google tokeninfo endpoint
 */
function verifyGoogleIdToken(string $idToken): ?array
{
    $config = getGoogleConfig();
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        error_log("[Google OAuth Error] ID Token verification failed: HTTP {$httpCode}");
        return null;
    }

    $payload = json_decode($response, true);
    if (!isset($payload['aud']) || $payload['aud'] !== $config['client_id']) {
        error_log("[Google OAuth Error] ID Token audience mismatch. Expected {$config['client_id']}");
        return null;
    }

    return $payload;
}

/**
 * Cari atau buat user baru di MySQL berdasarkan data Google
 */
function findOrCreateGoogleUser(array $googleData): array
{
    $pdo = Database::getInstance();

    $email = trim($googleData['email'] ?? '');
    $name = trim($googleData['name'] ?? '');
    $googleId = trim($googleData['sub'] ?? '');
    $avatar = trim($googleData['picture'] ?? '');

    if (empty($email)) {
        throw new InvalidArgumentException('Email pengguna dari Google tidak ditemukan.');
    }

    // 1. Cek berdasarkan email
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Update google_id & avatar_url jika belum tersimpan
        $updateStmt = $pdo->prepare('UPDATE users SET google_id = :gid, avatar_url = :avatar WHERE id = :id');
        $updateStmt->execute([
            'gid' => $googleId ?: ($user['google_id'] ?? null),
            'avatar' => $avatar ?: ($user['avatar_url'] ?? null),
            'id' => $user['id']
        ]);

        return [
            'id' => (int)$user['id'],
            'name' => $name ?: $user['username'],
            'username' => $user['username'],
            'email' => $user['email'],
            'avatar' => $avatar ?: ($user['avatar_url'] ?? null)
        ];
    }

    // 2. Jika user belum ada, buat user baru di database
    $baseUsername = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', explode('@', $email)[0]));
    if (empty($baseUsername)) $baseUsername = 'user';
    $username = $baseUsername;

    // Pastikan username unik
    $chkStmt = $pdo->prepare('SELECT COUNT(*) FROM users WHERE username = :un');
    $chkStmt->execute(['un' => $username]);
    if ($chkStmt->fetchColumn() > 0) {
        $username = $baseUsername . '_' . substr(bin2hex(random_bytes(3)), 0, 4);
    }

    $randomHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    $insertStmt = $pdo->prepare('
        INSERT INTO users (username, email, password_hash, google_id, avatar_url, created_at)
        VALUES (:username, :email, :password_hash, :google_id, :avatar_url, NOW())
    ');
    $insertStmt->execute([
        'username' => $username,
        'email' => $email,
        'password_hash' => $randomHash,
        'google_id' => $googleId ?: null,
        'avatar_url' => $avatar ?: null
    ]);

    $newId = (int)$pdo->lastInsertId();

    return [
        'id' => $newId,
        'name' => $name ?: $username,
        'username' => $username,
        'email' => $email,
        'avatar' => $avatar ?: null
    ];
}

