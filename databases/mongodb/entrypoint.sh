#!/bin/bash
set -Eeuo pipefail

export SERVER_PORT="${SERVER_PORT:-27017}"
export ADMIN_USER="${ADMIN_USER:-admin}"
export ADMIN_PASSWORD="${ADMIN_PASSWORD:?ADMIN_PASSWORD is required}"

for value in "$SERVER_PORT"; do
    [[ "$value" =~ ^[0-9]+$ ]] && (( value >= 1 && value <= 65535 )) || exit 1
done

mkdir -p /home/container/{mongodb,etc,run}
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

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
