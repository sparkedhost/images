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

COPY adminer/install.sh /usr/local/bin/install-adminer

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl libatomic1 libssl3t64 libzstd1 supervisor zlib1g \
    && bash /usr/local/bin/install-adminer redis \
    && apt-get purge -y --auto-remove curl \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY --from=kvrocks /usr/bin/kvrocks /usr/bin/kvrocks
COPY adminer/start-adminer /usr/local/bin/start-adminer
COPY adminer/prepare.sh /usr/local/bin/prepare-adminer
COPY adminer/Caddyfile /etc/caddy/Caddyfile.template
COPY adminer/php-fpm.conf /etc/php/8.5/fpm/php-fpm.conf
COPY adminer/index.php /var/www/adminer/public/index.php
COPY adminer/login-servers.php /var/www/adminer/public/plugins-enabled/login-servers.php
COPY adminer/supervisord.conf /etc/supervisor/conf.d/adminer.conf
COPY kvrocks/supervisord.conf /etc/supervisor/supervisord.conf
COPY kvrocks/entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh /usr/local/bin/start-adminer /usr/local/bin/prepare-adminer \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
STOPSIGNAL SIGTERM
ENTRYPOINT []
CMD ["/entrypoint.sh"]
