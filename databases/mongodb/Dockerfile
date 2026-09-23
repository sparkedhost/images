FROM debian:trixie-slim

ARG MONGO_VERSION=8.0.30
ARG MONGOSH_VERSION=2.11.1
ARG MONGOSH_SHA256=3cc12c23fcd1ad8da2663316966cfff6b0b6a99e764972e9e16771168a9f4c3a
ARG DEBIAN_FRONTEND=noninteractive

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host MongoDB ${MONGO_VERSION}" \
      org.opencontainers.image.version="${MONGO_VERSION}" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl libcurl4t64 libssl3t64 supervisor \
    && curl -fsSL \
        "https://fastdl.mongodb.org/linux/mongodb-linux-x86_64-debian12-${MONGO_VERSION}.tgz" \
        -o /tmp/mongodb.tgz \
    && curl -fsSL \
        "https://fastdl.mongodb.org/linux/mongodb-linux-x86_64-debian12-${MONGO_VERSION}.tgz.sha256" \
        -o /tmp/mongodb.sha256 \
    && sed "s#mongodb-linux-x86_64-debian12-${MONGO_VERSION}.tgz#/tmp/mongodb.tgz#" \
        /tmp/mongodb.sha256 | sha256sum -c - \
    && tar -xzf /tmp/mongodb.tgz --strip-components=2 -C /usr/bin \
        "mongodb-linux-x86_64-debian12-${MONGO_VERSION}/bin/mongod" \
    && curl -fsSL \
        "https://downloads.mongodb.com/compass/mongosh-${MONGOSH_VERSION}-linux-x64.tgz" \
        -o /tmp/mongosh.tgz \
    && echo "${MONGOSH_SHA256}  /tmp/mongosh.tgz" | sha256sum -c - \
    && tar -xzf /tmp/mongosh.tgz --strip-components=2 -C /usr/local/bin \
        "mongosh-${MONGOSH_VERSION}-linux-x64/bin/mongosh" \
    && rm /tmp/mongodb.tgz /tmp/mongodb.sha256 /tmp/mongosh.tgz \
    && apt-get purge -y --auto-remove curl \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
