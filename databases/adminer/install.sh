#!/bin/bash
set -Eeuo pipefail

# Shared Adminer, PHP-FPM and Caddy installation for database images.
driver="${1:?Adminer driver is required}"
php_version=8.5

case "$driver" in
    pgsql)
        php_driver="php${php_version}-pgsql"
        ;;
    mongo)
        php_driver="php${php_version}-mongodb"
        adminer_driver=mongo.php
        ;;
    redis)
        php_driver=""
        adminer_driver=kvrocks.php
        ;;
    *)
        echo "Unsupported Adminer driver: $driver" >&2
        exit 1
        ;;
esac

curl -fsSL --retry 3 https://packages.sury.org/debsuryorg-archive-keyring.deb \
    -o /tmp/debsuryorg-archive-keyring.deb
dpkg -i /tmp/debsuryorg-archive-keyring.deb
rm /tmp/debsuryorg-archive-keyring.deb
echo "deb [signed-by=/usr/share/keyrings/debsuryorg-archive-keyring.gpg] https://packages.sury.org/php/ trixie main" \
    > /etc/apt/sources.list.d/php.list
curl -fsSL --retry 3 https://dl.cloudsmith.io/public/caddy/stable/gpg.key \
    -o /usr/share/keyrings/caddy-stable.asc
echo "deb [signed-by=/usr/share/keyrings/caddy-stable.asc] https://dl.cloudsmith.io/public/caddy/stable/deb/debian any-version main" \
    > /etc/apt/sources.list.d/caddy-stable.list
apt-get update
php_packages=("php${php_version}-fpm")
if [[ -n "$php_driver" ]]; then
    php_packages+=("$php_driver")
fi
apt-get install -y --no-install-recommends caddy "${php_packages[@]}"

install -d -m 0755 /var/www/adminer/public/plugins-enabled /etc/caddy
install -m 0644 /usr/share/doc/adminer/vendor/adminer.php \
    /var/www/adminer/public/adminer.php
install -D -m 0644 /usr/share/doc/adminer/vendor/functions.js \
    /var/www/adminer/public/vendor/functions.js

install -m 0644 /usr/share/doc/adminer/themes/hydra-dark/adminer.css \
    /var/www/adminer/public/adminer.css

if [[ "$driver" != pgsql ]]; then
    install -m 0644 "/usr/share/doc/adminer/vendor/plugins/${adminer_driver}" \
        "/var/www/adminer/public/plugins-enabled/${driver}.php"
fi
