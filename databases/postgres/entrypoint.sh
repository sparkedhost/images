#!/bin/bash
set -Eeuo pipefail

export SERVER_PORT="${SERVER_PORT:-5432}"
export WEB_UI_PORT="${WEB_UI_PORT:-8080}"
export WEB_UI_ENABLED="${WEB_UI_ENABLED:-1}"
export ADMIN_USER="${ADMIN_USER:-admin}"
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"
export PGDATA=/home/container/postgres_db

for value in "$SERVER_PORT" "$WEB_UI_PORT"; do
    [[ "$value" =~ ^[0-9]+$ ]] && (( value >= 1 && value <= 65535 )) || exit 1
done

mkdir -p "$PGDATA" /home/container/run/php/sessions
sed "s/@@WEB_UI_PORT@@/${WEB_UI_PORT}/g" \
    /etc/caddy/Caddyfile.template > /home/container/run/Caddyfile
if [[ ! -f "$PGDATA/PG_VERSION" ]]; then
    password_file="$(mktemp)"
    chmod 0600 "$password_file"
    printf '%s' "$ADMIN_PASSWORD" > "$password_file"
    initdb --pgdata="$PGDATA" --username="$ADMIN_USER" --pwfile="$password_file" --auth-host=scram-sha-256 --auth-local=scram-sha-256
    rm -f "$password_file"
    cat >> "$PGDATA/postgresql.conf" <<EOF
listen_addresses = '*'
port = ${SERVER_PORT}
unix_socket_directories = '/home/container/run'
EOF
    echo 'host all all 0.0.0.0/0 scram-sha-256' >> "$PGDATA/pg_hba.conf"
fi

sed -i -E "s/^#?port = .*/port = ${SERVER_PORT}/" "$PGDATA/postgresql.conf"
grep -q "^listen_addresses" "$PGDATA/postgresql.conf" || echo "listen_addresses = '*'" >> "$PGDATA/postgresql.conf"
grep -q "^unix_socket_directories" "$PGDATA/postgresql.conf" || echo "unix_socket_directories = '/home/container/run'" >> "$PGDATA/postgresql.conf"

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
