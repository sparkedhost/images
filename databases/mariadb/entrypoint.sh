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
  MARIADB_SAFE_EXECUTABLE="mariadbd-safe"
  MARIADB_CLIENT_EXECUTABLE="mariadb"
  MARIADB_ADMIN_EXECUTABLE="mariadb-admin"
else
  MARIADB_EXECUTABLE="mysqld"
  MARIADB_INSTALLDB_EXECUTABLE="mysql_install_db"
  MARIADB_UPGRADE_EXECUTABLE="mysql_upgrade"
  MARIADB_SAFE_EXECUTABLE="mysqld_safe"
  MARIADB_CLIENT_EXECUTABLE="mysql"
  MARIADB_ADMIN_EXECUTABLE="mysqladmin"
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

initialize_database() {
  if [ -d "$MARIADB_DATADIR/mysql" ] && [ ! -f /home/container/install.cnf ]; then
    return 0
  fi

  if [ ! -d "$MARIADB_DATADIR/mysql" ]; then
    echo "Installing MariaDB database"
    cat <<'INSTALL_CNF' > /home/container/install.cnf
# MariaDB database server configuration file.
#
# You can copy this file to one of:
# - "/etc/mysql/my.cnf" to set global options,
# - "~/.my.cnf" to set user-specific options.
#
# One can use all long options that the program supports.
# Run program with --help to get a list of available options and with
# --print-defaults to see which it would actually understand and use.
#
# For explanations see
# http://dev.mysql.com/doc/mysql/en/server-system-variables.html

# This will be passed to all mysql clients
# It has been reported that passwords should be enclosed with ticks/quotes
# escpecially if they contain "#" chars...
# Remember to edit /etc/mysql/debian.cnf when changing the socket location.
[client]
port        = 3306
socket      = /home/container/run/mysqld/mysqld.sock

# Here is entries for some specific programs
# The following values assume you have at least 32M ram

# This was formally known as [safe_mysqld]. Both versions are currently parsed.
[mysqld_safe]
socket      = /home/container/run/mysqld/mysqld.sock
nice        = 0

[mysqld]
#
# * Basic Settings
#
#user       = mysql
pid-file    = /home/container/run/mysqld/mysqld.pid
socket      = /home/container/run/mysqld/mysqld.sock
port        = 3306
basedir     = /usr
datadir     = /home/container/mysql
tmpdir      = /tmp
lc_messages_dir = /usr/share/mysql
lc_messages = en_US
skip-external-locking
#
# Instead of skip-networking the default is now to listen only on
# localhost which is more compatible and is not less secure.
#
# * Fine Tuning
#
max_connections        = 100
connect_timeout        = 5
wait_timeout           = 600
max_allowed_packet     = 16M
thread_cache_size      = 128
sort_buffer_size       = 4M
bulk_insert_buffer_size = 16M
tmp_table_size         = 32M
max_heap_table_size    = 32M
#
# * MyISAM
#
# This replaces the startup script and checks MyISAM tables if needed
# the first time they are touched. On error, make copy and try a repair.
myisam_recover_options = BACKUP
key_buffer_size        = 128M
#open-files-limit      = 2000
table_open_cache       = 400
myisam_sort_buffer_size = 512M
concurrent_insert      = 2
read_buffer_size       = 2M
read_rnd_buffer_size   = 1M
#
# * Query Cache Configuration
#
# Cache only tiny result sets, so we can fit more in the query cache.
query_cache_limit      = 128K
query_cache_size       = 64M
# for more write intensive setups, set to DEMAND or OFF
#query_cache_type      = DEMAND
#
# * Logging and Replication
#
# Both location gets rotated by the cronjob.
# Be aware that this log type is a performance killer.
# As of 5.1 you can enable the log at runtime!
general_log_file       = /home/container/log/mysql/mysql.log
#general_log           = 1
#
# Error logging goes to syslog due to /etc/mysql/conf.d/mysqld_safe_syslog.cnf.
#
# we do want to know about network errors and such
#log_warnings          = 2
#
# Enable the slow query log to see queries with especially long duration
#slow_query_log[={0|1}]
slow_query_log_file    = /home/container/log/mysql/mariadb-slow.log
long_query_time = 10
#log_slow_rate_limit   = 1000
#log_slow_verbosity    = query_plan

#log-queries-not-using-indexes
#log_slow_admin_statements
#
# The following can be used as easy to replay backup logs or for replication.
# note: if you are setting up a replication slave, see README.Debian about
#       other settings that you may need to change.
#server-id             = 1
#report_host           = master1
#auto_increment_increment = 2
#auto_increment_offset = 1
#log_bin               = /var/log/mysql/mariadb-bin
#log_bin_index         = /var/log/mysql/mariadb-bin.index
# not fab for performance, but safer
#sync_binlog           = 1
expire_logs_days       = 10
max_binlog_size        = 100M
# slaves
#relay_log             = /var/log/mysql/relay-bin
#relay_log_index       = /var/log/mysql/relay-bin.index
#relay_log_info_file   = /var/log/mysql/relay-bin.info
#log_slave_updates
#read_only
#
# If applications support it, this stricter sql_mode prevents some
# mistakes like inserting invalid dates etc.
#sql_mode              = NO_ENGINE_SUBSTITUTION,TRADITIONAL
#
# * InnoDB
#
# InnoDB is enabled by default with a 10MB datafile in /var/lib/mysql/.
# Read the manual for more InnoDB related options. There are many!
default_storage_engine = InnoDB
# you can't just change log file size, requires special procedure
#innodb_log_file_size  = 50M
innodb_buffer_pool_size = 256M
innodb_log_buffer_size = 8M
innodb_file_per_table  = 1
innodb_open_files      = 400
innodb_io_capacity     = 400
innodb_flush_method    = O_DIRECT
#
# * Security Features
#
# Read the manual, too, if you want chroot!
# chroot = /var/lib/mysql/
#
# For generating SSL certificates I recommend the OpenSSL GUI "tinyca".
#
# ssl-ca=/etc/mysql/cacert.pem
# ssl-cert=/etc/mysql/server-cert.pem
# ssl-key=/etc/mysql/server-key.pem

#
# * Galera-related settings
#
[galera]
# Mandatory settings
#wsrep_on=ON
#wsrep_provider=
#wsrep_cluster_address=
#binlog_format=row
#default_storage_engine=InnoDB
#innodb_autoinc_lock_mode=2
#
# Allow server to accept connections on all interfaces.
#
bind-address=0.0.0.0
#
# Optional setting
#wsrep_slave_threads=1
#innodb_flush_log_at_trx_commit=0

[mysqldump]
quick
quote-names
max_allowed_packet     = 16M

[mysql]
#no-auto-rehash # faster start of mysql but no tab completion

[isamchk]
key_buffer             = 16M

#
# * IMPORTANT: Additional settings that can override those from this file!
#   The files must end with '.cnf', otherwise they'll be ignored.
#
!include /etc/mysql/mariadb.cnf
!includedir /etc/mysql/conf.d/
INSTALL_CNF

    INSTALL_ARGS=(--defaults-file=/home/container/install.cnf)
    if "$MARIADB_INSTALLDB_EXECUTABLE" --help 2>&1 | grep -q -- "--auth-root-authentication-method"; then
      INSTALL_ARGS+=(--auth-root-authentication-method=normal)
    fi

    "$MARIADB_INSTALLDB_EXECUTABLE" "${INSTALL_ARGS[@]}"
  fi

  echo "Starting MariaDB to create users"
  "$MARIADB_SAFE_EXECUTABLE" \
    --defaults-file=/home/container/install.cnf \
    --datadir="$MARIADB_DATADIR" \
    --socket="$MARIADB_SOCKET" \
    --pid-file=/home/container/run/mysqld/mysqld.pid &
  sleep 20

  MAX_TRIES=30
  TRIES=0
  while ! "$MARIADB_CLIENT_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" -e "SELECT 1" >/dev/null 2>&1; do
    sleep 1
    TRIES=$((TRIES+1))
    if [ "$TRIES" -gt "$MAX_TRIES" ]; then
      echo "MariaDB failed to start after $MAX_TRIES seconds"
      exit 1
    fi
  done

  echo "Creating admin user"
  if [ -n "$ADMIN_USER" ] && [ -n "$ADMIN_PASSWORD" ]; then
    if "$MARIADB_CLIENT_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" -e "DROP DATABASE test;" && \
       "$MARIADB_CLIENT_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" -e "CREATE USER '${ADMIN_USER}'@'%' IDENTIFIED BY '${ADMIN_PASSWORD}';" && \
       "$MARIADB_CLIENT_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" -e "GRANT ALL PRIVILEGES ON *.* TO '${ADMIN_USER}'@'%' WITH GRANT OPTION;" && \
       "$MARIADB_CLIENT_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" -e "FLUSH PRIVILEGES;"; then
      echo "Admin user created successfully"
    else
      echo "Failed to create admin user"
      exit 1
    fi
  else
    echo "ADMIN_USER or ADMIN_PASSWORD not set, skipping admin user creation"
  fi

  echo "Stopping MariaDB"
  "$MARIADB_ADMIN_EXECUTABLE" -u root --socket="$MARIADB_SOCKET" shutdown
  rm -rf /home/container/install.cnf

  sleep 5

  echo "Install complete"
}

initialize_database

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
  /usr/bin/supervisorctl -c /supervisord.conf stop mariadb
  /usr/bin/supervisorctl -c /supervisord.conf stop php-fpm
  /usr/bin/supervisorctl -c /supervisord.conf stop caddy
  /usr/bin/supervisorctl -c /supervisord.conf shutdown
  exit 0
}

trap handle_shutdown SIGINT SIGTERM

# Start supervisord in the background
/usr/bin/supervisord -c /supervisord.conf &

# Wait to keep the script running and catch signals
wait
