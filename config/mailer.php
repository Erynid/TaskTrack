<?php
/**
 * TaskTrack - Mailer & Email Verification Module
 * Apple HIG Compliant Transactional Email Dispatcher with SMTP & Local Preview Fallback
 */

if (!function_exists('loadMailerEnv')) {
    function loadMailerEnv(): void
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
                    if (!isset($_SERVER[$name]) && !isset($_ENV[$name])) {
                        putenv(sprintf('%s=%s', $name, $value));
                        $_ENV[$name] = $value;
                        $_SERVER[$name] = $value;
                    }
                }
            }
        }
    }
}
loadMailerEnv();

/**
 * Dapatkan konfigurasi mailer dari environment
 */
function getMailerConfig(): array
{
    return [
        'host' => getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? 'smtp.gmail.com'),
        'port' => (int)(getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 587)),
        'encryption' => getenv('SMTP_ENCRYPTION') ?: ($_ENV['SMTP_ENCRYPTION'] ?? 'tls'),
        'user' => getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? ''),
        'pass' => getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? ''),
        'from_email' => getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@tasktrack.local'),
        'from_name' => getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'TaskTrack Studio'),
    ];
}

/**
 * Deteksi Base URL aplikasi TaskTrack
 */
function getBaseAppUrl(): string
{
    // Cek jika API_BASE_URL terdefinisi
    $configuredApi = getenv('API_BASE_URL') ?: ($_ENV['API_BASE_URL'] ?? '');
    if (!empty($configuredApi)) {
        $parent = dirname(rtrim($configuredApi, '/'));
        if (!empty($parent) && $parent !== '.' && $parent !== '/') {
            return $parent;
        }
    }

    $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');

    // Jika dipanggil dari subfolder (misal /api atau /auth atau /config)
    $dirParts = explode('/', trim($dir, '/'));
    if (!empty($dirParts)) {
        $lastPart = end($dirParts);
        if (in_array($lastPart, ['api', 'auth', 'config', 'tests', 'data'])) {
            array_pop($dirParts);
            $dir = '/' . implode('/', $dirParts);
        }
    }

    return rtrim("{$scheme}://{$host}{$dir}", '/');
}

/**
 * Render template HTML email verifikasi bernuansa Apple HIG
 */
function renderVerificationEmailHtml(string $recipientName, string $verificationUrl): string
{
    $safeName = htmlspecialchars($recipientName, ENT_QUOTES, 'UTF-8');
    $safeUrl = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');
    $year = date('Y');

    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akun TaskTrack</title>
</head>
<body style="margin: 0; padding: 0; background-color: #0b0f19; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #f1f5f9; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #0b0f19; width: 100%; min-height: 100vh; padding: 40px 16px;">
        <tr>
            <td align="center" valign="top">
                <!-- Outer Wrapper Card -->
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width: 560px; background: #131926; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);">
                    <!-- Brand Header -->
                    <tr>
                        <td style="padding: 36px 36px 20px 36px; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.05); background: linear-gradient(180deg, rgba(56, 189, 248, 0.08) 0%, rgba(19, 25, 38, 0) 100%);">
                            <div style="display: inline-flex; align-items: center; justify-content: center; width: 52px; height: 52px; border-radius: 14px; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.5); margin-bottom: 14px;">
                                <span style="font-size: 26px; line-height: 1; color: #ffffff;">✦</span>
                            </div>
                            <div style="font-size: 22px; font-weight: 800; letter-spacing: -0.03em; color: #ffffff;">
                                Task<span style="color: #38bdf8;">Track</span>
                            </div>
                            <div style="font-size: 12px; font-weight: 500; color: #94a3b8; letter-spacing: 0.05em; text-transform: uppercase; margin-top: 4px;">
                                Academic Task Management
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 36px 36px 28px 36px;">
                            <h1 style="margin: 0 0 16px 0; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.02em;">
                                Konfirmasi Alamat Email Anda 👋
                            </h1>
                            <p style="margin: 0 0 16px 0; font-size: 15px; line-height: 1.6; color: #cbd5e1;">
                                Halo <strong>{$safeName}</strong>, terima kasih telah bergabung di <strong>TaskTrack</strong>!
                            </p>
                            <p style="margin: 0 0 28px 0; font-size: 14px; line-height: 1.6; color: #94a3b8;">
                                Sebelum dapat masuk dan mengelola tugas perkuliahan, silakan lakukan verifikasi bahwa email ini adalah milik Anda dengan menekan tombol aktivasi di bawah:
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 32px 0;">
                                <a href="{$safeUrl}" target="_blank" style="display: inline-block; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); color: #ffffff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 14px 32px; border-radius: 12px; box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.5); letter-spacing: -0.01em;">
                                    Verifikasi Email Saya &rarr;
                                </a>
                            </div>

                            <!-- Expiry Note Card -->
                            <div style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 12px; padding: 14px 18px; margin-top: 24px;">
                                <p style="margin: 0; font-size: 12.5px; line-height: 1.5; color: #94a3b8;">
                                    ⏱️ <strong>Catatan Keamanan:</strong> Tautan ini berlaku selama <strong>24 jam</strong>. Jika kedaluwarsa, Anda dapat meminta tautan baru melalui formulir masuk.
                                </p>
                            </div>

                            <!-- Fallback Link -->
                            <p style="margin: 28px 0 6px 0; font-size: 12px; color: #64748b; line-height: 1.5;">
                                Jika tombol di atas tidak dapat diklik, salin dan tempel alamat berikut ke peramban web Anda:
                            </p>
                            <p style="margin: 0; font-size: 11.5px; line-height: 1.4; word-break: break-all; color: #38bdf8;">
                                <a href="{$safeUrl}" style="color: #38bdf8; text-decoration: underline;">{$safeUrl}</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 24px 36px 32px 36px; border-top: 1px solid rgba(255, 255, 255, 0.05); text-align: center; background: rgba(0, 0, 0, 0.2);">
                            <p style="margin: 0 0 6px 0; font-size: 12px; color: #64748b;">
                                Anda menerima pesan ini karena mendaftar akun di TaskTrack.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #475569;">
                                Jika Anda tidak merasa melakukan pendaftaran ini, abaikan email ini secara aman.
                            </p>
                            <p style="margin: 14px 0 0 0; font-size: 11px; color: #475569;">
                                &copy; {$year} TaskTrack Studio. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Pure-PHP SMTP Client via Sockets (STARTTLS / TLS / Auth Login)
 */
function sendSmtpRaw(array $config, string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $host = $config['host'];
    $port = $config['port'];
    $user = $config['user'];
    $pass = $config['pass'];
    $fromEmail = $config['from_email'];
    $fromName = $config['from_name'];

    $timeout = 10;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

    $remoteHost = ($port === 465 || $config['encryption'] === 'ssl') ? "ssl://{$host}" : "tcp://{$host}";
    $socket = @stream_socket_client("{$remoteHost}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

    if (!$socket) {
        error_log("[TaskTrack Mailer Error] SMTP Connect failed ({$errno}): {$errstr}");
        return false;
    }

    stream_set_timeout($socket, $timeout);

    $readResponse = function () use ($socket): string {
        $data = '';
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) break;
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };

    $sendCommand = function (string $cmd) use ($socket): void {
        fputs($socket, $cmd . "\r\n");
    };

    $expect = function (int $code) use ($readResponse): bool {
        $res = $readResponse();
        $resCode = (int)substr($res, 0, 3);
        return $resCode === $code;
    };

    if (!$expect(220)) {
        fclose($socket);
        return false;
    }

    $clientDomain = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $sendCommand("EHLO {$clientDomain}");
    if (!$expect(250)) {
        $sendCommand("HELO {$clientDomain}");
        if (!$expect(250)) {
            fclose($socket);
            return false;
        }
    }

    // STARTTLS jika port 587 atau enkripsi tls
    if ($port === 587 || strtolower($config['encryption']) === 'tls') {
        $sendCommand("STARTTLS");
        if ($expect(220)) {
            $crypto = stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            if (!$crypto) {
                fclose($socket);
                return false;
            }
            $sendCommand("EHLO {$clientDomain}");
            if (!$expect(250)) {
                fclose($socket);
                return false;
            }
        }
    }

    // AUTH LOGIN jika credentials ada
    if (!empty($user) && !empty($pass)) {
        $sendCommand("AUTH LOGIN");
        if (!$expect(334)) {
            fclose($socket);
            return false;
        }
        $sendCommand(base64_encode($user));
        if (!$expect(334)) {
            fclose($socket);
            return false;
        }
        $sendCommand(base64_encode($pass));
        if (!$expect(235)) {
            fclose($socket);
            return false;
        }
    }

    $sendCommand("MAIL FROM: <{$fromEmail}>");
    if (!$expect(250)) {
        fclose($socket);
        return false;
    }

    $sendCommand("RCPT TO: <{$toEmail}>");
    if (!$expect(250)) {
        fclose($socket);
        return false;
    }

    $sendCommand("DATA");
    if (!$expect(354)) {
        fclose($socket);
        return false;
    }

    $messageId = sprintf('<%s.%s@%s>', bin2hex(random_bytes(16)), time(), $clientDomain);
    $encodedFromName = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
    $encodedToName = '=?UTF-8?B?' . base64_encode($toName) . '?=';
    $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $headers = [
        "Date: " . date('r'),
        "To: {$encodedToName} <{$toEmail}>",
        "From: {$encodedFromName} <{$fromEmail}>",
        "Subject: {$encodedSubject}",
        "Message-ID: {$messageId}",
        "MIME-Version: 1.0",
        "Content-Type: text/html; charset=UTF-8",
        "Content-Transfer-Encoding: base64",
        "X-Mailer: TaskTrack-Mailer/1.0"
    ];

    $emailData = implode("\r\n", $headers) . "\r\n\r\n" . chunk_split(base64_encode($htmlBody)) . "\r\n.";
    $sendCommand($emailData);

    $success = $expect(250);
    $sendCommand("QUIT");
    fclose($socket);

    return $success;
}

/**
 * Kirim email verifikasi kepada pengguna baru atau permintaan tautan ulang
 *
 * @param string $recipientEmail
 * @param string $recipientName
 * @param string $verificationToken
 * @return array Status pengiriman beserta URL verifikasi & pratinjau lokal
 */
function sendVerificationEmail(string $recipientEmail, string $recipientName, string $verificationToken): array
{
    $baseUrl = getBaseAppUrl();
    $verificationUrl = "{$baseUrl}/verify.php?token=" . urlencode($verificationToken);
    $htmlBody = renderVerificationEmailHtml($recipientName, $verificationUrl);

    // Siapkan folder pratinjau lokal
    $previewDir = __DIR__ . '/../data/mail_previews';
    if (!is_dir($previewDir)) {
        @mkdir($previewDir, 0777, true);
    }
    $previewFile = "{$previewDir}/{$verificationToken}.html";
    file_put_contents($previewFile, $htmlBody);
    $previewUrl = "data/mail_previews/{$verificationToken}.html";

    $config = getMailerConfig();

    // Jika user & pass SMTP belum dikonfigurasi, langsung gunakan mode pratinjau lokal yang aman & instan
    if (empty($config['user']) || empty($config['pass'])) {
        return [
            'success' => true,
            'mode' => 'local_preview',
            'preview_url' => $previewUrl,
            'verification_url' => $verificationUrl,
            'message' => 'Email verifikasi disimpan di pratinjau lokal (SMTP credentials belum diisi di .env).'
        ];
    }

    // Jika kredensial terisi, lakukan pengiriman SMTP
    $subject = 'Verifikasi Alamat Email Anda - TaskTrack';
    $sent = false;
    try {
        $sent = sendSmtpRaw($config, $recipientEmail, $recipientName, $subject, $htmlBody);
    } catch (Throwable $e) {
        error_log('[TaskTrack SMTP Exception] ' . $e->getMessage());
        $sent = false;
    }

    if ($sent) {
        return [
            'success' => true,
            'mode' => 'smtp',
            'preview_url' => $previewUrl,
            'verification_url' => $verificationUrl,
            'message' => 'Email verifikasi berhasil dikirim melalui server SMTP.'
        ];
    }

    // Fallback jika SMTP gagal (misal koneksi diblokir firewall/ISP): user tetap bisa verifikasi via preview
    return [
        'success' => true,
        'mode' => 'local_fallback',
        'preview_url' => $previewUrl,
        'verification_url' => $verificationUrl,
        'message' => 'Pengiriman SMTP gagal, email verifikasi dialihkan ke pratinjau lokal untuk pengujian.'
    ];
}

