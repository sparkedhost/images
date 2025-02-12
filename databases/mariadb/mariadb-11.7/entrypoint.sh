#!/bin/bash
cd /home/container

# Define MariaDB variables
export MARIADB_SOCKET="/home/container/run/mysqld/mysqld.sock"
export MARIADB_DATADIR="/home/container/mysql"
export MARIADB_TMPDIR="/tmp"
export MARIADB_LC_MESSAGES_DIR="/home/container/mysql/lc/"
export MARIADB_LOG_DIR="/home/container/log/mysql"
export SERVER_PORT="${SERVER_PORT_ENV:-3306}"
export WEB_UI_PORT="${WEB_UI_PORT_ENV:-8080}"
## WEB_UI_ENABLED is unused
export WEB_UI_ENABLED=""
export MAX_CONNECTIONS="${MAX_CONNECTIONS_ENV:-500}"

# Determine correct executables to use based on MariaDB version
if which mariadb > /dev/null ; then
  MARIADB_EXECUTABLE="mariadbd"
  MARIADB_INSTALLDB_EXECUTABLE="mariadb-install-db"
  MARIADB_UPGRADE_EXECUTABLE="mariadb-upgrade"
else
  MARIADB_EXECUTABLE="mysqld"
  MARIADB_INSTALLDB_EXECUTABLE="mysql_install_db"
  MARIADB_UPGRADE_EXECUTABLE="mysql_upgrade"
fi

# Ensure required folders exist
mkdir -p /home/container/run/mysqld
mkdir -p /home/container/run/php
mkdir -p /home/container/run/php/log
mkdir -p $MARIADB_LOG_DIR
mkdir -p $MARIADB_DATADIR
mkdir -p /home/container/mysql/lc/
mkdir -p /home/container/etc
mkdir -p /home/container/etc/php-fpm
mkdir -p /home/container/etc/caddy/
mkdir -p /usr/share/mysql

# Generate Caddyfile
generate_caddyfile() {
  cat <<EOF > /home/container/etc/caddy/Caddyfile
:$WEB_UI_PORT {
    root * /var/www/phpmyadmin
    file_server

    @forbidden {
        path_regexp forbidden ^/(doc|sql|setup)/
    }
    respond @forbidden 403

    request_body {
        max_size 10GB
    }

    header {
        X-Content-Type-Options "nosniff"
        X-XSS-Protection "1; mode=block"
        X-Robots-Tag "none"
        Content-Security-Policy "frame-ancestors 'self'"
        X-Frame-Options "DENY"
        Referrer-Policy "same-origin"
    }

    php_fastcgi 127.0.0.1:9999 {
            env PHP_VALUE "upload_max_filesize = 10G \n post_max_size=10G"
    }

    @htaccess {
        path_regexp htaccess /\.ht
    }
 
EOF
}

generate_caddyfile

# if ! mysqladmin -u root --socket="$MARIADB_SOCKET" ping > /dev/null 2>&1; then
#   echo "Error: MariaDB failed to start within $TIMEOUT seconds."
#   exit 1
# fi

# Optional: Perform database upgrade
# sleep 5
# if $MARIADB_UPGRADE_EXECUTABLE --check-if-upgrade-is-needed; then
#     echo "Upgrading MariaDB..."
#     $MARIADB_UPGRADE_EXECUTABLE -u container
# fi


if [ ! -d "$MARIADB_DATADIR/mysql" ]; then
  echo "Initializing MariaDB..."
  $MARIADB_INSTALLDB_EXECUTABLE \
    --socket="$MARIADB_SOCKET" \
    --datadir="$MARIADB_DATADIR" \
    --tmpdir="$MARIADB_TMPDIR" \
    --lc-messages-dir="$MARIADB_LC_MESSAGES_DIR"
fi


# Start supervisord
/usr/bin/supervisord -c /supervisord.conf
echo "MariaDB stopped."