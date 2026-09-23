#!/bin/sh
set -eu

usage() {
    echo "usage: db-tools {mariadb-dump|mariadb-restore|postgresql-dump|postgresql-restore|mongodb-dump|mongodb-restore} [arguments...]" >&2
    exit 64
}

[ "$#" -gt 0 ] || usage

operation=$1
shift

case "$operation" in
    mariadb-dump)
        exec mariadb-dump "$@"
        ;;
    mariadb-restore)
        exec mariadb "$@"
        ;;
    postgresql-dump|postgresql-restore)
        : "${POSTGRESQL_MAJOR:?POSTGRESQL_MAJOR is required}"
        case "$POSTGRESQL_MAJOR" in
            13|14|15|16|17) ;;
            *)
                echo "unsupported PostgreSQL major: $POSTGRESQL_MAJOR" >&2
                exit 64
                ;;
        esac

        case "$operation" in
            postgresql-dump)
                exec "/usr/lib/postgresql/${POSTGRESQL_MAJOR}/bin/pg_dump" "$@"
                ;;
            postgresql-restore)
                exec "/usr/lib/postgresql/${POSTGRESQL_MAJOR}/bin/pg_restore" "$@"
                ;;
        esac
        ;;
    mongodb-dump)
        if [ -n "${MONGODB_URI:-}" ]; then
            exec mongodump --uri="$MONGODB_URI" "$@"
        fi
        exec mongodump "$@"
        ;;
    mongodb-restore)
        if [ -n "${MONGODB_URI:-}" ]; then
            exec mongorestore --uri="$MONGODB_URI" "$@"
        fi
        exec mongorestore "$@"
        ;;
    *)
        usage
        ;;
esac
