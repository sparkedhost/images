#!/bin/bash
set -Eeuo pipefail

# Shared Adminer, PHP-FPM and Caddy installation for database images.
driver="${1:?Adminer driver is required}"
php_version=8.5
adminer_version=6.1.1
caddy_version=2.11.4

case "$driver" in
    pgsql)
        php_driver="php${php_version}-pgsql"
        ;;
    mongo)
        php_driver="php${php_version}-mongodb"
        adminer_driver=mongo.php
        ;;
    redis)
        php_driver="php${php_version}-redis"
        adminer_driver=kvrocks.php
        ;;
    *)
        echo "Unsupported Adminer driver: $driver" >&2
        exit 1
        ;;
esac

curl -fsSL https://packages.sury.org/php/apt.gpg \
    -o /usr/share/keyrings/deb.sury.org-php.gpg
echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ trixie main" \
    > /etc/apt/sources.list.d/php.list
apt-get update
apt-get install -y --no-install-recommends "php${php_version}-fpm" "$php_driver"

install -d -m 0755 /var/www/adminer/public/plugins-enabled /etc/caddy
install -m 0644 /usr/share/doc/adminer/vendor/adminer.php \
    /var/www/adminer/public/adminer.php

install -m 0644 /usr/share/doc/adminer/themes/hydra-dark/adminer.css \
    /var/www/adminer/public/adminer.css

install -m 0644 /usr/share/doc/adminer/vendor/plugins/login-reverse-proxy.php \
    /var/www/adminer/public/plugins-enabled/login-reverse-proxy.php

if [[ "$driver" != pgsql ]]; then
    install -m 0644 "/usr/share/doc/adminer/vendor/plugins/${adminer_driver}" \
        "/var/www/adminer/public/plugins-enabled/${driver}.php"
fi

curl -fsSL \
    "https://github.com/caddyserver/caddy/releases/download/v${caddy_version}/caddy_${caddy_version}_linux_amd64.tar.gz" \
    -o /tmp/caddy.tar.gz
curl -fsSL \
    "https://github.com/caddyserver/caddy/releases/download/v${caddy_version}/caddy_${caddy_version}_checksums.txt" \
    -o /tmp/caddy-checksums.txt
grep " caddy_${caddy_version}_linux_amd64.tar.gz$" /tmp/caddy-checksums.txt \
    | awk '{ print $1 "  /tmp/caddy.tar.gz" }' | sha512sum -c -
tar -xzf /tmp/caddy.tar.gz -C /usr/bin caddy
chmod 0755 /usr/bin/caddy
rm /tmp/caddy.tar.gz /tmp/caddy-checksums.txt
