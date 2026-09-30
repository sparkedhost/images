#!/bin/bash
set -Eeuo pipefail

export PS1='container@${HOSTNAME}:${PWD}\$ '
export SERVER_PORT="${SERVER_PORT:-27017}"
export ADMINER_DRIVER=mongo ADMINER_SERVER_LABEL=MongoDB
export ADMIN_USER="${ADMIN_USER:-admin}"
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"

[[ "$SERVER_PORT" =~ ^[0-9]+$ ]] && (( SERVER_PORT >= 1 && SERVER_PORT <= 65535 )) || exit 1

mkdir -p /home/container/{mongodb,etc,run}
/usr/local/bin/prepare-adminer
config=/home/container/etc/mongod.conf
cat > "$config" <<EOF
storage:
  dbPath: /home/container/mongodb
net:
  bindIpAll: true
  port: ${SERVER_PORT}
security:
  authorization: enabled
processManagement:
  pidFilePath: /home/container/run/mongod.pid
EOF

if [[ ! -f /home/container/mongodb/.apollo-initialized ]]; then
    mongod --dbpath /home/container/mongodb --bind_ip 127.0.0.1 --port 27018 --fork --logpath /home/container/run/mongod-init.log
    for _ in $(seq 1 30); do
        mongosh --quiet --port 27018 --eval 'db.runCommand({ping: 1}).ok' 2>/dev/null | grep -q 1 && break
        sleep 1
    done
    mongosh --quiet --port 27018 --eval "db.getSiblingDB('admin').createUser({user: process.env.ADMIN_USER, pwd: process.env.ADMIN_PASSWORD, roles: [{role: 'root', db: 'admin'}]})"
    mongod --dbpath /home/container/mongodb --shutdown --port 27018
    touch /home/container/mongodb/.apollo-initialized
fi

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
