<?php

namespace apollo {
    function adminer_object() {
        return new \Adminer\Plugins([
            require __DIR__.'/plugins-enabled/login-servers.php',
        ]);
    }
}

namespace {
    function adminer_object() {
        return \apollo\adminer_object();
    }

    require __DIR__.'/adminer.php';
}
