#!/bin/bash
set -Eeuo pipefail

export PS1='container@${HOSTNAME}:${PWD}\$ '
export SERVER_PORT="${SERVER_PORT:-6666}"
export ADMINER_DRIVER=redis ADMINER_SERVER_LABEL=KVRocks
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"

[[ "$SERVER_PORT" =~ ^[0-9]+$ ]] && (( SERVER_PORT >= 1 && SERVER_PORT <= 65535 )) || exit 1

mkdir -p /home/container/{data,run,etc}
/usr/local/bin/prepare-adminer
config=/home/container/etc/kvrocks.conf
if [[ ! -f "$config" ]]; then
    cat > "$config" <<EOF
bind 0.0.0.0
port ${SERVER_PORT}
dir /home/container/data
pidfile /home/container/run/kvrocks.pid
logfile ""
daemonize no
cluster-enabled no
redis-databases 0
requirepass ${ADMIN_PASSWORD}
EOF
fi

# Existing installations retain namespace metadata and credentials. Only update
# runtime port and resource caps managed by Panel.
sed -i \
    -e "s/^port .*/port ${SERVER_PORT}/" \
    -e "s/^requirepass .*/requirepass ${ADMIN_PASSWORD}/" \
    "$config"

supervisor_pid=""
bash_pid=""

shutdown_services() {
    trap - SIGINT SIGTERM
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

/usr/bin/supervisord -c /etc/supervisor/supervisord.conf </dev/null &
supervisor_pid=$!

/bin/bash --noprofile --norc -i <&0 &
bash_pid=$!
wait "$bash_pid" || true
bash_pid=""
wait "$supervisor_pid" || true
supervisor_pid=""

shutdown_services
