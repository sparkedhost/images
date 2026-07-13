#!/bin/bash
set -Eeuo pipefail

cd /home/container

export PATH="/opt/mariadb/bin:/opt/mariadb/scripts:/usr/local/bin:${PATH}"
export PS1='container@${HOSTNAME}:${PWD}\$ '
export SERVER_PORT="${SERVER_PORT:-3306}"
export WEB_UI_PORT="${WEB_UI_PORT:-8080}"
export WEB_UI_ENABLED="${WEB_UI_ENABLED:-}"
export MAX_CONNECTIONS="${MAX_CONNECTIONS_ENV:-1000}"

readonly MARIADB_SOCKET=/home/container/run/mysqld/mysqld.sock
readonly MARIADB_DATADIR=/home/container/mysql
readonly MARIADB_CONFIG=/home/container/etc/mariadb/my.cnf

supervisor_pid=""
bash_pid=""
startup_pid=""

shutdown_services() {
  trap - SIGINT SIGTERM
  if [[ -n "$startup_pid" ]] && kill -0 "$startup_pid" 2>/dev/null; then
    kill -TERM "$startup_pid" 2>/dev/null || true
    wait "$startup_pid" || true
  fi
  if [[ -n "$supervisor_pid" ]] && kill -0 "$supervisor_pid" 2>/dev/null; then
    kill -TERM "$supervisor_pid"
    wait "$supervisor_pid" || true
  fi
  if [[ -n "$bash_pid" ]] && kill -0 "$bash_pid" 2>/dev/null; then
    kill -HUP "$bash_pid" 2>/dev/null || true
    wait "$bash_pid" || true
  fi
}

trap 'shutdown_services; exit 130' SIGINT
trap 'shutdown_services; exit 143' SIGTERM

require_integer() {
  local name=$1 value=$2 minimum=$3 maximum=$4
  if [[ ! "$value" =~ ^[0-9]+$ ]] \
      || (( 10#$value < minimum || 10#$value > maximum )); then
    printf '%s must be an integer between %s and %s; got %q\n' \
      "$name" "$minimum" "$maximum" "$value" >&2
    exit 1
  fi
}

require_integer SERVER_PORT "$SERVER_PORT" 1 65535
require_integer WEB_UI_PORT "$WEB_UI_PORT" 1 65535
require_integer MAX_CONNECTIONS_ENV "$MAX_CONNECTIONS" 1 100000

mkdir -p \
  /home/container/etc/caddy \
  /home/container/etc/mariadb/conf.d \
  /home/container/etc/pma \
  /home/container/log/mysql \
  /home/container/run/mysqld \
  /home/container/run/php/log \
  /home/container/run/php/sessions \
  /tmp/pma \
  "$MARIADB_DATADIR"

cp /etc/mariadb/my.cnf.template "$MARIADB_CONFIG"
sed -i \
  -e "s/@@SERVER_PORT@@/${SERVER_PORT}/g" \
  -e "s/@@MAX_CONNECTIONS@@/${MAX_CONNECTIONS}/g" \
  "$MARIADB_CONFIG"

cp /etc/mariadb/root.my.cnf.template /home/container/.my.cnf
chmod 0600 /home/container/.my.cnf

cp /etc/caddy/Caddyfile.template /home/container/etc/caddy/Caddyfile
sed -i "s/@@WEB_UI_PORT@@/${WEB_UI_PORT}/g" \
  /home/container/etc/caddy/Caddyfile

wait_for_mariadb() {
  local tries=0
  until /opt/mariadb/bin/mariadb-admin --no-defaults \
      --user=root --socket="$MARIADB_SOCKET" ping >/dev/null 2>&1; do
    if ! kill -0 "$1" 2>/dev/null; then
      echo "MariaDB exited during initialization"
      wait "$1"
    fi
    tries=$((tries + 1))
    if (( tries >= 60 )); then
      echo "MariaDB failed to start after 60 seconds"
      return 1
    fi
    sleep 1
  done
}

sql_escape() {
  printf '%s' "$1" | sed "s/'/''/g"
}

initialize_database() {
  [[ -d "$MARIADB_DATADIR/mysql" ]] && return 0

  echo "Initializing MariaDB ${MARIADB_VERSION}"
  local install_args=(
    --defaults-file="$MARIADB_CONFIG"
    --basedir=/opt/mariadb
    --datadir="$MARIADB_DATADIR"
  )
  if { /opt/mariadb/scripts/mariadb-install-db --help 2>&1 || true; } \
      | grep -q -- '--auth-root-authentication-method'; then
    install_args+=(--auth-root-authentication-method=normal)
  fi
  /opt/mariadb/scripts/mariadb-install-db "${install_args[@]}" </dev/null

  /opt/mariadb/bin/mariadbd \
    --defaults-file="$MARIADB_CONFIG" \
    --skip-networking &
  startup_pid=$!
  wait_for_mariadb "$startup_pid"

  /opt/mariadb/bin/mariadb --no-defaults \
    --user=root --socket="$MARIADB_SOCKET" \
    --execute="
      DROP DATABASE IF EXISTS test;
      DELETE FROM mysql.global_priv
      WHERE User = '' OR (User = 'root' AND Host <> 'localhost');
      FLUSH PRIVILEGES;
    "

  if [[ -n "${ADMIN_USER:-}" && -n "${ADMIN_PASSWORD:-}" ]]; then
    local admin_user admin_password
    admin_user="$(sql_escape "$ADMIN_USER")"
    admin_password="$(sql_escape "$ADMIN_PASSWORD")"
    /opt/mariadb/bin/mariadb --no-defaults \
      --user=root --socket="$MARIADB_SOCKET" <<SQL
CREATE USER IF NOT EXISTS '${admin_user}'@'%' IDENTIFIED BY '${admin_password}';
ALTER USER '${admin_user}'@'%' IDENTIFIED BY '${admin_password}';
GRANT ALL PRIVILEGES ON *.* TO '${admin_user}'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL
  else
    echo "ADMIN_USER or ADMIN_PASSWORD missing; admin user creation skipped"
  fi

  /opt/mariadb/bin/mariadb-admin --no-defaults \
    --user=root --socket="$MARIADB_SOCKET" shutdown
  wait "$startup_pid"
  startup_pid=""
  echo "MariaDB initialization complete"
}

configure_phpmyadmin() {
  local config=/home/container/etc/pma/pma.conf
  if [[ ! -f "$config" ]]; then
    cp /var/www/phpmyadmin/config.inc.php.template "$config"
    local blowfish_secret
    blowfish_secret="$(openssl rand -hex 16)"
    sed -i \
      "s|\$cfg\['blowfish_secret'\] = ''|\$cfg['blowfish_secret'] = '${blowfish_secret}'|" \
      "$config"
  fi
  sed -i \
    "s|\$cfg\['Servers'\]\[\$i\]\['port'\] = '[^']*'|\$cfg['Servers'][\$i]['port'] = '${SERVER_PORT}'|" \
    "$config"
}

initialize_database
configure_phpmyadmin

/usr/bin/supervisord -c /supervisord.conf </dev/null &
supervisor_pid=$!

/bin/bash --noprofile --norc -i <&0 &
bash_pid=$!
wait "$bash_pid" || true
bash_pid=""
wait "$supervisor_pid" || true
supervisor_pid=""

shutdown_services
