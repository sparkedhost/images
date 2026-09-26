#!/bin/bash
set -Eeuo pipefail

# Shared Adminer, PHP-FPM and Caddy installation for database images.
driver="${1:?Adminer driver is required}"
php_version=8.5
adminer_version=6.1.1
adminer_sha256=49c4d400994ef74bbb1b1b4d06c05445c1ba49ba3766b9092dce157750e09303
login_reverse_proxy_sha256=cd8fcbeed32d8aa8b8213e88f56f0620f772f85aad44415cf953906c164c4f81
mongo_driver_sha256=d4765e771a6dc1fedd03ff97d2fe7eafdb3f2921f090eda034d0621dbc7d1510
redis_driver_sha256=d338a029743f4fe81b8062a06d9a42c216ce5ff0e5df705e6fd754c5f527ff76
kvrocks_driver_sha256=bd7b8025d78f09ee390d5dfe26481e50044d4a143bb2917c0812068e7976afa2
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

# Adminer treats a boolean's hidden input and checkbox as one DOM element.
# Patch the compiled JavaScript to select the visible field, preserving both values.
php -r '$_GET["file"] = "functions.js"; require "/var/www/adminer/public/adminer.php";' \
    > /tmp/adminer-functions.js
php <<'PHP'
<?php
$path = '/var/www/adminer/public/adminer.php';
$javascript = str_replace(
    "this.form[this.name.replace(/^function/,'fields')]",
    "Array.from(this.form.elements).find(input => input.name === this.name.replace(/^function/,'fields') && input.type !== 'hidden')",
    file_get_contents('/tmp/adminer-functions.js'),
    $lookupCount
);
$source = preg_replace_callback(
    '~(elseif\(\$_GET\["file"\]=="functions\.js"\)\{[^}]*?echo\s+)decompress_string\(\x27[^\x27]*\x27\)~',
    static fn ($match) => $match[1].var_export($javascript, true),
    file_get_contents($path),
    -1,
    $assetCount
);
if ($lookupCount !== 1 || $assetCount !== 1) {
    throw new RuntimeException('Adminer boolean field patch no longer matches.');
}
// Invalidate cached scripts as well as Adminer's matching asset version check.
$source = str_replace('6.1.1+43f4678f', '6.1.1+43f4678f-apollo1', $source);
file_put_contents($path, $source);
PHP
rm /tmp/adminer-functions.js

install -m 0644 /usr/share/doc/adminer/themes/hydra-dark/adminer.css \
    /var/www/adminer/public/adminer.css

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
