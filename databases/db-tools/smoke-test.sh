#!/bin/sh
set -eu

db-tools mariadb-dump --version
db-tools mariadb-restore --version
POSTGRESQL_MAJOR=13 db-tools postgresql-dump --version
POSTGRESQL_MAJOR=13 db-tools postgresql-restore --version
POSTGRESQL_MAJOR=14 db-tools postgresql-dump --version
POSTGRESQL_MAJOR=14 db-tools postgresql-restore --version
POSTGRESQL_MAJOR=15 db-tools postgresql-dump --version
POSTGRESQL_MAJOR=15 db-tools postgresql-restore --version
POSTGRESQL_MAJOR=16 db-tools postgresql-dump --version
POSTGRESQL_MAJOR=16 db-tools postgresql-restore --version
POSTGRESQL_MAJOR=17 db-tools postgresql-dump --version
POSTGRESQL_MAJOR=17 db-tools postgresql-restore --version
db-tools mongodb-dump --version
db-tools mongodb-restore --version

if db-tools postgresql-dump --version; then
    echo "PostgreSQL major is required" >&2
    exit 1
fi

if POSTGRESQL_MAJOR=12 db-tools postgresql-dump --version; then
    echo "Unsupported PostgreSQL major was accepted" >&2
    exit 1
fi

if db-tools invalid-operation; then
    echo "Invalid operation was accepted" >&2
    exit 1
fi
