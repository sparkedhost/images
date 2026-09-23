<?php

require_once __DIR__.'/../plugins/login-servers.php';

return new AdminerLoginServers([
    'PostgreSQL' => [
        'server' => '127.0.0.1:'.(getenv('ADMINER_DATABASE_PORT') ?: '5432'),
        'driver' => 'pgsql',
    ],
]);
