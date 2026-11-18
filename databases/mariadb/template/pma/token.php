<?php

declare(strict_types=1);

header('Cache-Control: no-store');
header('Referrer-Policy: no-referrer');

ini_set('session.use_cookies', 'true');

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

    $_SESSION['PMA_single_signon_user'] = $payload['username'];
    $_SESSION['PMA_single_signon_password'] = $payload['password'];
    $_SESSION['PMA_single_signon_host'] = $payload['host'];
    $_SESSION['PMA_single_signon_port'] = $payload['port'];
    $_SESSION['PMA_single_signon_cfgupdate'] = [
        'only_db' => [$payload['database']],
        'verbose' => $payload['database'],
    ];
    $_SESSION['PMA_single_signon_HMAC_secret'] = bin2hex(random_bytes(20));
    @session_write_close();

    header('Location: index.php');
    exit;
} catch (RuntimeException | JsonException $exception) {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');

    $message = htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>phpMyAdmin Login</title></head><body>';
    echo '<h1>Unable to open phpMyAdmin</h1>';
    echo '<p>'.$message.'</p>';
    echo '</body></html>';
}

/**
 * @return array{host: string, port: string, database: string, username: string, password: string}
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

    if (! is_array($payload) || ($payload['aud'] ?? null) !== 'apollo-phpmyadmin') {
        throw new RuntimeException('Invalid phpMyAdmin login token.');
    }

    if (! is_int($payload['exp'] ?? null) || $payload['exp'] <= time()) {
        throw new RuntimeException('Expired phpMyAdmin login token.');
    }

    return $payload;
}
