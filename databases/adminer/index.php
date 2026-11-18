<?php

namespace apollo {
    function adminer_object() {
        $driver = getenv('ADMINER_DRIVER');
        if ($driver === 'mongo' || $driver === 'redis') {
            require __DIR__.'/plugins-enabled/'.$driver.'.php';
        }

        $plugins = [
            require __DIR__.'/plugins-enabled/login-servers.php',
        ];

        return new \Adminer\Plugins($plugins);
    }
}

namespace {
    if (isset($_GET['apollo_db_sso'])) {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');

        try {
            $token = $_GET['apollo_db_sso'];
            $secret = getenv('DATABASE_WEB_UI_SSO_KEY');
            if (!is_string($token) || !is_string($secret) || $secret === '') {
                throw new \RuntimeException('Invalid Adminer login token.');
            }

            $raw = base64_decode($token, true);
            if ($raw === false || strlen($raw) <= 28) {
                throw new \RuntimeException('Invalid Adminer login token.');
            }

            $json = openssl_decrypt(
                substr($raw, 28),
                'aes-256-gcm',
                hash('sha256', $secret, true),
                OPENSSL_RAW_DATA,
                substr($raw, 0, 12),
                substr($raw, 12, 16),
            );
            $payload = $json === false ? null : json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            if (!is_int($payload['exp'] ?? null) || $payload['exp'] <= time()) {
                throw new \RuntimeException('Invalid or expired Adminer login token.');
            }

            $driver = getenv('ADMINER_DRIVER');
            $isRedis = $driver === 'redis';
            define('APOLLO_SSO_TOKEN_VERIFIED', true);
            $_POST = ['auth' => [
                'driver' => $driver,
                'server' => getenv('ADMINER_SERVER_LABEL') ?: 'Database',
                'username' => $isRedis ? '' : $payload['username'],
                'password' => $payload['password'],
                'db' => $isRedis ? '' : $payload['database'],
            ]];
            unset($_GET['apollo_db_sso']);
            $_SERVER['REQUEST_URI'] = '/';
        } catch (\RuntimeException | \JsonException) {
            http_response_code(400);
            header('Content-Type: text/plain; charset=utf-8');
            echo 'Invalid or expired Adminer login token.';
            exit;
        }
    }
    if (($_GET['file'] ?? null) === 'functions.js') {
        header('Content-Type: text/javascript; charset=utf-8');
        header('Cache-Control: no-store');
        readfile(__DIR__.'/vendor/functions.js');
        exit;
    }

    function adminer_object() {
        return \apollo\adminer_object();
    }

    require __DIR__.'/adminer.php';
}
