<?php

declare(strict_types=1);

session_name('TokenSession');
@session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

header('Location: index.php');
