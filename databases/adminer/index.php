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
    if (isset($_POST['apollo_db_sso']) || isset($_GET['apollo_db_sso'])) {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');

        try {
            $token = $_POST['apollo_db_sso'] ?? null;
            $secret = getenv('DATABASE_WEB_UI_SSO_KEY');
            if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !is_string($token) || !is_string($secret) || $secret === '') {
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
            $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
            if (!is_int($payload['exp'] ?? null) || $payload['exp'] <= time()
                || $origin === '' || $origin === 'null' || $origin !== ($payload['origin'] ?? null)
                || !is_string($payload['id'] ?? null) || !preg_match('/\A[0-9a-f]{32}\z/', $payload['id'])
            ) {
                throw new \RuntimeException('Invalid or expired Adminer login token.');
            }

            apollo_consume_login_token($payload['id'], $payload['exp']);

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
            $_GET = [];
            $_SERVER['REQUEST_URI'] = '/';
            http_response_code(303);
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

    function apollo_consume_login_token(string $id, int $expiresAt): void
    {
        $directory = '/home/container/run/php/sso-used';
        if (!is_dir($directory) && !@mkdir($directory, 0700) && !is_dir($directory)) {
            throw new \RuntimeException('Unable to record Adminer login token.');
        }

        $now = time();
        // Exclusive creation lets only one worker redeem this token.
        if ($expiresAt <= $now || !@mkdir($directory.'/'.$expiresAt.'-'.$id, 0700)) {
            throw new \RuntimeException('Invalid, expired, or used Adminer login token.');
        }

        foreach (glob($directory.'/*', GLOB_ONLYDIR | GLOB_NOSORT) ?: [] as $marker) {
            if ((int) basename($marker) < $now - 60) {
                @rmdir($marker);
            }
        }
    }

    require __DIR__.'/adminer.php';
}
