<?php

namespace apollo {
    function adminer_object() {
        $driver = getenv('ADMINER_DRIVER');
        if ($driver === 'mongo' || $driver === 'redis') {
            require __DIR__.'/plugins-enabled/'.$driver.'.php';
        }

        require_once __DIR__.'/plugins-enabled/login-reverse-proxy.php';
        $plugins = [
            new \AdminerLoginReverseProxy(),
            require __DIR__.'/plugins-enabled/login-servers.php',
        ];

        return new \Adminer\Plugins($plugins);
    }
}

namespace {
    function adminer_object() {
        return \apollo\adminer_object();
    }

    require __DIR__.'/adminer.php';
}
