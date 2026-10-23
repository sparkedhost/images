FROM debian:trixie-slim

ARG DEBIAN_FRONTEND=noninteractive

LABEL org.opencontainers.image.title="Sparked Host database tools" \
      org.opencontainers.image.description="Database backup and restore tools for Wings" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        mariadb-client \
        postgresql-client-common \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL --retry 3 \
        https://pgp.mongodb.com/server-8.0.asc \
        -o /etc/apt/keyrings/mongodb-server-8.0.asc \
    && echo "deb [signed-by=/etc/apt/keyrings/mongodb-server-8.0.asc] https://repo.mongodb.org/apt/debian trixie/mongodb-org/8.0 main" \
        > /etc/apt/sources.list.d/mongodb-org-8.0.list \
    && /usr/share/postgresql-common/pgdg/apt.postgresql.org.sh -y \
    && apt-get install -y --no-install-recommends \
        mongodb-database-tools \
        postgresql-client-14 \
        postgresql-client-15 \
        postgresql-client-16 \
        postgresql-client-17 \
        postgresql-client-18 \
    && apt-get purge -y --auto-remove curl \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/db-tools

RUN chmod 0755 /usr/local/bin/db-tools \
    && useradd --create-home --uid 1000 --shell /usr/sbin/nologin dbtools

USER dbtools

ENTRYPOINT ["/usr/local/bin/db-tools"]
