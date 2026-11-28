<?php

declare(strict_types=1);

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

$method = $_SERVER['REQUEST_METHOD'] ?? '';
$token = $_POST['apollo_db_sso'] ?? null;
if ($method === 'GET' && !isset($_GET['apollo_db_sso'])) {
    require __DIR__.'/token-logout.php';
    exit;
}

try {
    if ($method !== 'POST') {
        throw new RuntimeException('Invalid phpMyAdmin login request.');
    }

    $payload = apollo_decrypt_payload($token);
    apollo_consume_login_token($payload['id'], $payload['exp']);

    session_name('TokenSession');
    session_start();

    session_regenerate_id(true);

    $_SESSION = [
        'PMA_single_signon_user' => $payload['username'],
        'PMA_single_signon_password' => $payload['password'],
        'PMA_single_signon_cfgupdate' => [
            'only_db' => [$payload['database']],
            'verbose' => $payload['database'],
        ],
    ];
    session_write_close();

    header('Location: index.php', true, 303);
    exit;
} catch (RuntimeException | JsonException $exception) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $exception->getMessage();
}

/**
 * @return array{exp: int, id: string, origin: string, database: string, username: string, password: string}
 */
function apollo_decrypt_payload(mixed $token): array
{
    if (! is_string($token)) {
        throw new RuntimeException('Invalid phpMyAdmin login token.');
    }

    $secret = getenv('DATABASE_WEB_UI_SSO_KEY');
    if (! is_string($secret) || $secret === '') {
        throw new RuntimeException('DATABASE_WEB_UI_SSO_KEY is not configured.');
    }

    $raw = base64_decode($token, true);
    if ($raw === false || strlen($raw) <= 28) {
        throw new RuntimeException('Invalid phpMyAdmin login token.');
    }

    $nonce = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);
    $json = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        hash('sha256', $secret, true),
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
    );

    if ($json === false) {
        throw new RuntimeException('Invalid phpMyAdmin login token.');
    }

    $payload = json_decode($json, true, flags: JSON_THROW_ON_ERROR);

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (! is_int($payload['exp'] ?? null) || $payload['exp'] <= time()
        || $origin === '' || $origin === 'null' || $origin !== ($payload['origin'] ?? null)
        || ! is_string($payload['id'] ?? null) || ! preg_match('/\A[0-9a-f]{32}\z/', $payload['id'])
    ) {
        throw new RuntimeException('Invalid or expired phpMyAdmin login token.');
    }

    return $payload;
}

function apollo_consume_login_token(string $id, int $expiresAt): void
{
    $directory = '/home/container/run/php/sso-used';
    if (! is_dir($directory) && ! @mkdir($directory, 0700) && ! is_dir($directory)) {
        throw new RuntimeException('Unable to record phpMyAdmin login token.');
    }

    $now = time();
    // Exclusive creation lets only one worker redeem this token.
    if ($expiresAt <= $now || ! @mkdir($directory.'/'.$expiresAt.'-'.$id, 0700)) {
        throw new RuntimeException('Invalid, expired, or used phpMyAdmin login token.');
    }

    foreach (glob($directory.'/*', GLOB_ONLYDIR | GLOB_NOSORT) ?: [] as $marker) {
        if ((int) basename($marker) < $now - 60) {
            @rmdir($marker);
        }
    }
}
