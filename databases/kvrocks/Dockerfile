ARG KVROCKS_VERSION=2.16.0

FROM apache/kvrocks:${KVROCKS_VERSION} AS kvrocks

FROM debian:trixie-slim

ARG KVROCKS_VERSION
ARG DEBIAN_FRONTEND=noninteractive

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host KVRocks ${KVROCKS_VERSION}" \
      org.opencontainers.image.version="${KVROCKS_VERSION}" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates libatomic1 libssl3t64 libzstd1 supervisor zlib1g \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY --from=kvrocks /usr/bin/kvrocks /usr/bin/kvrocks
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
STOPSIGNAL SIGTERM
ENTRYPOINT []
CMD ["/entrypoint.sh"]
