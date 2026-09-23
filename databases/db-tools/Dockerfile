FROM debian:trixie-slim

ARG DEBIAN_FRONTEND=noninteractive
ARG MONGODB_TOOLS_VERSION=100.19.0
ARG MONGODB_TOOLS_SHA256=769f86f69545c70837a1b4be63ba48e00c5160c614c4c3d5b8c45f3b03844638

LABEL org.opencontainers.image.title="Sparked Host database tools" \
      org.opencontainers.image.description="Database backup and restore tools for Wings" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libgssapi-krb5-2 \
        mariadb-client \
        tar \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        -o /etc/apt/keyrings/postgresql.asc \
    && echo "deb [signed-by=/etc/apt/keyrings/postgresql.asc] https://apt.postgresql.org/pub/repos/apt trixie-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        postgresql-client-14 \
        postgresql-client-15 \
        postgresql-client-16 \
        postgresql-client-17 \
        postgresql-client-18 \
    && curl -fsSL \
        "https://fastdl.mongodb.org/tools/db/mongodb-database-tools-debian13-x86_64-${MONGODB_TOOLS_VERSION}.tgz" \
        -o /tmp/mongodb-tools.tgz \
    && echo "${MONGODB_TOOLS_SHA256}  /tmp/mongodb-tools.tgz" | sha256sum -c - \
    && tar -xzf /tmp/mongodb-tools.tgz --strip-components=2 -C /usr/local/bin \
        "mongodb-database-tools-debian13-x86_64-${MONGODB_TOOLS_VERSION}/bin/mongodump" \
        "mongodb-database-tools-debian13-x86_64-${MONGODB_TOOLS_VERSION}/bin/mongorestore" \
    && rm /tmp/mongodb-tools.tgz \
    && apt-get purge -y --auto-remove curl \
    && rm -rf /var/lib/apt/lists/*

COPY entrypoint.sh /usr/local/bin/db-tools

RUN chmod 0755 /usr/local/bin/db-tools \
    && useradd --create-home --uid 1000 --shell /usr/sbin/nologin dbtools

USER dbtools

ENTRYPOINT ["/usr/local/bin/db-tools"]
