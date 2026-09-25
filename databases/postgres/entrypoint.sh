#!/bin/bash
set -Eeuo pipefail

export SERVER_PORT="${SERVER_PORT:-5432}"
export ADMINER_DRIVER=pgsql ADMINER_SERVER_LABEL=PostgreSQL
export ADMIN_USER="${ADMIN_USER:-admin}"
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"
export PGDATA=/home/container/postgres_db

for value in "$SERVER_PORT"; do
    [[ "$value" =~ ^[0-9]+$ ]] && (( value >= 1 && value <= 65535 )) || exit 1
done

mkdir -p "$PGDATA"
/usr/local/bin/prepare-adminer

# Wings runs containers as the node's numeric UID/GID, which may not exist in
# this image's passwd database. PostgreSQL requires getpwuid() to resolve it.
if ! getent passwd "$(id -u)" >/dev/null; then
    nss_wrapper="$(find /usr/lib -name libnss_wrapper.so -print -quit)"
    [[ -n "$nss_wrapper" ]] || { echo 'libnss_wrapper.so not found' >&2; exit 1; }

    passwd_file="$(mktemp)"
    group_file="$(mktemp)"
    uid="$(id -u)"
    gid="$(id -g)"
    printf 'root:x:0:0:root:/root:/bin/bash\ncontainer:x:%s:%s::/home/container:/bin/bash\nnobody:x:65534:65534:nobody:/nonexistent:/usr/sbin/nologin\n' \
        "$uid" "$gid" > "$passwd_file"
    printf 'root:x:0:\ncontainer:x:%s:\nnogroup:x:65534:\n' "$gid" > "$group_file"
    export NSS_WRAPPER_PASSWD="$passwd_file"
    export NSS_WRAPPER_GROUP="$group_file"
    export LD_PRELOAD="$nss_wrapper${LD_PRELOAD:+:$LD_PRELOAD}"
fi

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
