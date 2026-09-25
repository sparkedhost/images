#!/bin/bash
set -Eeuo pipefail

web_ui_port="${WEB_UI_PORT:-8080}"
[[ "$web_ui_port" =~ ^[0-9]+$ ]] && (( web_ui_port >= 1 && web_ui_port <= 65535 )) || exit 1

mkdir -p /home/container/run/php/sessions
sed "s/@@WEB_UI_PORT@@/${web_ui_port}/g" \
    /etc/caddy/Caddyfile.template > /home/container/run/Caddyfile
