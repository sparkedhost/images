#!/bin/bash
set -Eeuo pipefail

# Adminer must never receive database-host administration credentials.
unset ADMIN_USER ADMIN_PASSWORD

exec /usr/sbin/php-fpm8.5 --nodaemonize --fpm-config /etc/php/8.5/fpm/php-fpm.conf
