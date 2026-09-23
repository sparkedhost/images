FROM debian:bookworm-slim

ARG DEBIAN_FRONTEND=noninteractive
ARG MONGODB_TOOLS_VERSION=100.14.0

LABEL org.opencontainers.image.title="Sparked Host database tools" \
      org.opencontainers.image.description="Database backup and restore tools for Wings" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        gnupg \
        mariadb-client \
        xz-utils \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        | gpg --dearmor -o /etc/apt/keyrings/postgresql.gpg \
    && echo "deb [signed-by=/etc/apt/keyrings/postgresql.gpg] http://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        postgresql-client-13 \
        postgresql-client-14 \
        postgresql-client-15 \
        postgresql-client-16 \
        postgresql-client-17 \
    && curl -fsSL \
        "https://fastdl.mongodb.org/tools/db/mongodb-database-tools-debian12-x86_64-${MONGODB_TOOLS_VERSION}.deb" \
        -o /tmp/mongodb-database-tools.deb \
    && dpkg -i /tmp/mongodb-database-tools.deb \
    && rm /tmp/mongodb-database-tools.deb \
    && apt-get purge -y --auto-remove curl gnupg xz-utils \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/db-tools
COPY smoke-test.sh /usr/local/bin/smoke-test

RUN chmod 0755 /usr/local/bin/db-tools /usr/local/bin/smoke-test \
    && useradd --create-home --uid 1000 --shell /usr/sbin/nologin dbtools

USER dbtools

ENTRYPOINT ["/usr/local/bin/db-tools"]
