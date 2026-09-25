#!/bin/bash
set -Eeuo pipefail

# Shared Adminer, PHP-FPM and Caddy installation for database images.
driver="${1:?Adminer driver is required}"
php_version=8.5
adminer_version=6.1.0
adminer_sha256=95bf24b510b41904446f720f4f1212c9e28b1d523f3df44259480d7a12ea181e
theme_sha256=5eb534b1c595697cdfcee9b1cd4371b5367b53892069bd69f5f0a3abafaa58a5
login_reverse_proxy_sha256=6f702191760e91b5ffeab86306f545ae03d4f618ccff3f12088635eb94b9bc25
mongo_driver_sha256=5cad54273126b473c90e45a8771a28c337ad882aecf44d3dad51e1f9a0f23957
redis_driver_sha256=c66ed53fbc9071e3de8f5592e15011431bae11848d9c3afedc62b8f49ffaec00
kvrocks_driver_sha256=ea498d6d472f1286aa3a15d3769c050a70da06ec3afcc38a2806bbb3de3916d2
caddy_version=2.11.4

case "$driver" in
    pgsql)
        php_driver="php${php_version}-pgsql"
        ;;
    mongo)
        php_driver="php${php_version}-mongodb"
        driver_sha256="$mongo_driver_sha256"
        ;;
    redis)
        php_driver="php${php_version}-redis"
        driver_sha256="$redis_driver_sha256"
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
curl -fsSL \
    "https://github.com/vrana/adminer/releases/download/v${adminer_version}/adminer-${adminer_version}.php" \
    -o /var/www/adminer/public/adminer.php
echo "${adminer_sha256}  /var/www/adminer/public/adminer.php" | sha256sum -c -

curl -fsSL https://raw.githubusercontent.com/devknown/simple-theme/master/adminer.css \
    -o /var/www/adminer/public/adminer.css
echo "${theme_sha256}  /var/www/adminer/public/adminer.css" | sha256sum -c -

curl -fsSL \
    "https://raw.githubusercontent.com/vrana/adminer/v${adminer_version}/plugins/login-reverse-proxy.php" \
    -o /var/www/adminer/public/plugins-enabled/login-reverse-proxy.php
echo "${login_reverse_proxy_sha256}  /var/www/adminer/public/plugins-enabled/login-reverse-proxy.php" | sha256sum -c -

if [[ "$driver" != pgsql ]]; then
    driver_path="/var/www/adminer/public/plugins-enabled/${driver}.php"
    curl -fsSL \
        "https://www.adminer.org/static/download/${adminer_version}/drivers/${driver}.php" \
        -o "$driver_path"
    echo "${driver_sha256}  ${driver_path}" | sha256sum -c -

    if [[ "$driver" == redis ]]; then
        # KVRocks authenticates admin and namespace tokens with AUTH <password>.
        sed -i 's/array("AUTH", $username, $password)/array("AUTH", $password)/' "$driver_path"
        echo "${kvrocks_driver_sha256}  ${driver_path}" | sha256sum -c -
    fi
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
