#!/bin/bash
cd /home/container

# Define MariaDB variables
export MARIADB_SOCKET="/home/container/run/mysqld/mysqld.sock"
export MARIADB_DATADIR="/home/container/mysql"
export MARIADB_TMPDIR="/tmp"
export MARIADB_LC_MESSAGES_DIR="/home/container/mysql/lc/"
export MARIADB_LOG_DIR="/home/container/log/mysql"
export SERVER_PORT="${SERVER_PORT:-3306}"
export WEB_UI_PORT="${WEB_UI_PORT:-8080}"
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
mkdir -p /home/container/run/php/sessions
mkdir -p $MARIADB_LOG_DIR
mkdir -p $MARIADB_DATADIR
mkdir -p /home/container/mysql/lc/
mkdir -p /home/container/etc
mkdir -p /home/container/etc/php-fpm
mkdir -p /home/container/etc/caddy/
mkdir -p /home/container/etc/pma/
mkdir -p /tmp/pma/

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
}
EOF
}

generate_caddyfile

## Configure PHPMyAdmin
configure_phpmyadmin() {
  echo "Configuring phpMyAdmin..."

  # Copy the modified config file to the persistent storage location
  cp /var/www/phpmyadmin/config.inc.php.template /home/container/etc/pma/pma.conf

  # Set the phpMyAdmin host to 127.0.0.1:$SERVER_PORT
  # sed -i "s|\$cfg['Servers'][\$i]['host'] = '127.0.0.1';|\$cfg['Servers'][\$i]['host'] = '127.0.0.1';|g" /home/container/etc/pma/pma.conf
  sed -i "s|\$cfg\['Servers'\]\[\$i\]\['port'\] = ''|\$cfg['Servers'][\$i]['port'] = '${SERVER_PORT}'|g" /home/container/etc/pma/pma.conf

  BLOWFISH_SECRET=$(openssl rand -base64 22)
  sed -i "s|\$cfg\['blowfish_secret'\] = ''|\$cfg['blowfish_secret'] = '${BLOWFISH_SECRET}'|g" /home/container/etc/pma/pma.conf
}

# Configure phpMyAdmin
configure_phpmyadmin

handle_shutdown() {
  echo "Received shutdown signal. Stopping services..."
  /usr/bin/supervisorctl -c /supervisord.conf shutdown
  exit 0
}

trap handle_shutdown SIGINT SIGTERM

# Start supervisord in the background
/usr/bin/supervisord -c /supervisord.conf &

# Wait to keep the script running and catch signals
wait

