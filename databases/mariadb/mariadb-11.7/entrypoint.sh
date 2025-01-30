#!/bin/bash
cd /home/container

# Function to show usage
usage() {
  echo "Usage: $0 --pma-port <port> --db-port <port> --web-enable <0|1> [--upgrade-db]"
  exit 1
}

# Parse arguments
while [[ "$#" -gt 0 ]]; do
  case $1 in
    --pma-port) PMA_PORT="$2"; shift ;;
    --db-port) DB_PORT="$2"; shift ;;
    --web-enable) WEB_ENABLE="$2"; shift ;;
    *) usage ;;
  esac
  shift
done

# Check required arguments
if [ -z "$PMA_PORT" ] || [ -z "$DB_PORT" ] || [ -z "$WEB_ENABLE" ]; then
  usage
fi

if [ "$WEB_ENABLE" != "0" ] && [ "$WEB_ENABLE" != "1" ]; then
  usage
fi

# Make internal Docker IP address available to processes.
INTERNAL_IP=$(ip route get 1 | awk '{print $(NF-2);exit}')
export INTERNAL_IP

# Determine correct executables to use based on MariaDB version
if which mariadb > /dev/null ; then
  # MariaDB 11.x or later
  MARIADB_EXECUTABLE="mariadb"
  MARIADB_INSTALLDB_EXECUTABLE="mariadb-install-db"
  MARIADB_UPGRADE_EXECUTABLE="mariadb-upgrade"
else
  # Likely MariaDB 10.x or earlier (assuming mariadbd-safe is not available)
  MARIADB_EXECUTABLE="mysql"
  MARIADB_INSTALLDB_EXECUTABLE="mysql_install_db"
  MARIADB_UPGRADE_EXECUTABLE="mysql_upgrade"
fi

# Function to get MariaDB version
get_mariadb_version() {
    MARIADB_VERSION=$($MARIADB_EXECUTABLE --version | grep -oP '(?<=Ver )[^ ]+')
}

# Call the function on startup
get_mariadb_version

# Setup NSS Wrapper - Some variables already set in the Dockerfile
export USER_ID=$(id -u)
export GROUP_ID=$(id -g)
envsubst < /passwd.template > ${NSS_WRAPPER_PASSWD}
envsubst < /group.template > ${NSS_WRAPPER_GROUP}
if [ -f /usr/lib/libnss_wrapper.so ]; then
  export LD_PRELOAD=/usr/lib/libnss_wrapper.so
else
  export LD_PRELOAD=/usr/lib/x86_64-linux-gnu/libnss_wrapper.so
fi

# Ensure required folders exist
mkdir -p $HOME/run/mysqld
mkdir -p $HOME/log/mysql
mkdir -p $HOME/mysql
mkdir -p $HOME/etc
mkdir -p $HOME/etc/nginx/
mkdir -p $HOME/usr/share/mysql

# Only run install db if the database is not setup
if [ ! -f $HOME/mysql/mysql.frm ] ; then
  echo "Database not setup - exiting with error"
  exit 1
fi

# Automatically run the functions that --normal would trigger
# Start web server if WEB_ENABLE is set to 1
if [ "$WEB_ENABLE" -eq 1 ]; then
  # Start nginx in the background
  nginx -g "daemon off;" -c /etc/nginx/nginx.conf -p $HTTP_PORT &
  echo "Web server started."
else
  echo "WEB_ENABLE is not set to 1. Skipping web server startup."
fi

# Start MariaDB in the background
$MARIADB_EXECUTABLE --socket=$HOME/run/mysqld/mysqld.sock --host-cache-size=0 --skip-name-resolve --pid-file=$HOME/run/mysqld/mysqld.pid --basedir=/usr --expire_logs_days=10 --character-set-server=utf8mb4 --character-set-collations=utf8mb4=uca1400_ai_ci --port=$DB_PORT --socket=$HOME/run/mysqld/mysqld.sock --basedir=/usr --datadir=$HOME/mysql --tmpdir=/tmp --lc_messages_dir=$HOME/usr/share/mysql --lc_messages=en_US --skip-external-locking --bind-address=0.0.0.0 --max_connections=100 --connect_timeout=5 --wait_timeout=600 --max_allowed_packet=16M --thread_cache_size=128 --sort_buffer_size=4M --bulk_insert_buffer_size=16M --tmp_table_size=32M --max_heap_table_size=32M --myisam_recover_options=BACKUP --key_buffer_size=128M --table_open_cache=400 --myisam_sort_buffer_size=512M --concurrent_insert=2 --read_buffer_size=2M --read_rnd_buffer_size=1M --query_cache_limit=128K --query_cache_size=64M --general_log_file=$HOME/log/mysql/mysql.log --slow_query_log_file=$HOME/log/mysql/mariadb-slow.log --long_query_time=10 --expire_logs_days=10 --max_binlog_size=100M --default_storage_engine=InnoDB --innodb_buffer_pool_size=256M --innodb_log_buffer_size=8M --innodb_file_per_table=1 --innodb_open_files=400 --innodb_io_capacity=400 --innodb_flush_method=O_DIRECT --bind-address=0.0.0.0 --socket=$HOME/run/mysqld/mysqld.sock --host-cache-size=0 --skip-name-resolve --pid-file=$HOME/run/mysqld/mysqld.pid --basedir=/usr --expire_logs_days=10 --character-set-server=utf8mb4 --character-set-collations=utf8mb4=uca1400_ai_ci &

PID=$!

sleep 5

# Check if upgrade is needed and perform upgrade if necessary
if $MARIADB_UPGRADE_EXECUTABLE --check-if-upgrade-is-needed; then
  echo "Upgrading MariaDB..."
  $MARIADB_UPGRADE_EXECUTABLE -u container
fi

# Show MariaDB logs
tail -f /home/container/log/mysql/mysql.log /home/container/log/mysql/mariadb-slow.log &

# Replace Startup Variables
MODIFIED_STARTUP=$(echo ${STARTUP} | sed -e 's/{{/${/g' -e 's/}}/}/g')
echo ":/home/container$ ${MODIFIED_STARTUP}"

# Run the Server
eval ${MODIFIED_STARTUP}

# Wait for the server to stop
wait $PID > /dev/null 2>&1