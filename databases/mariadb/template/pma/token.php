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

$token = $_GET['apollo_db_sso'] ?? null;
if ($token === null) {
    require __DIR__.'/token-logout.php';
    exit;
}

try {
    $payload = apollo_decrypt_payload($token);

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

    header('Location: index.php');
    exit;
} catch (RuntimeException | JsonException $exception) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo $exception->getMessage();
}

/**
 * @return array{exp: int, database: string, username: string, password: string}
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

    if (! is_int($payload['exp'] ?? null) || $payload['exp'] <= time()) {
        throw new RuntimeException('Invalid or expired phpMyAdmin login token.');
    }

    return $payload;
}
