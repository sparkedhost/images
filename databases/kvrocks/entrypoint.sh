#!/bin/bash
set -Eeuo pipefail

export SERVER_PORT="${SERVER_PORT:-6666}"
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"

for value in "$SERVER_PORT"; do
    [[ "$value" =~ ^[0-9]+$ ]] && (( value >= 1 && value <= 65535 )) || exit 1
done

mkdir -p /home/container/{data,run,etc}
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

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
