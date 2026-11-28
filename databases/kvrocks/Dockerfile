FROM debian:trixie-slim

ARG DEBIAN_FRONTEND=noninteractive

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host KVRocks" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

COPY adminer/install.sh /usr/local/bin/install-adminer
COPY adminer/vendor /usr/share/doc/adminer/vendor
COPY adminer/themes/hydra-dark /usr/share/doc/adminer/themes/hydra-dark

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl libatomic1 libssl3t64 libzstd1 supervisor zlib1g \
    && curl -fsSL --retry 3 https://apollo-assets-s3.sparkedhost.us/kvrocks -o /usr/bin/kvrocks \
    && chmod 0755 /usr/bin/kvrocks \
    && /usr/bin/kvrocks --version \
    && bash /usr/local/bin/install-adminer redis \
    && apt-get purge -y --auto-remove curl \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && rm -rf /var/lib/apt/lists/*

COPY --chmod=0755 adminer/start-adminer /usr/local/bin/start-adminer
COPY --chmod=0755 adminer/prepare.sh /usr/local/bin/prepare-adminer
COPY adminer/Caddyfile /etc/caddy/Caddyfile.template
COPY adminer/php-fpm.conf /etc/php/8.5/fpm/php-fpm.conf
COPY adminer/index.php /var/www/adminer/public/index.php
COPY adminer/login-servers.php /var/www/adminer/public/plugins-enabled/login-servers.php
COPY adminer/supervisord.conf /etc/supervisor/conf.d/adminer.conf
COPY kvrocks/supervisord.conf /etc/supervisor/supervisord.conf
COPY --chmod=0755 kvrocks/entrypoint.sh /entrypoint.sh

RUN chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
STOPSIGNAL SIGTERM
ENTRYPOINT []
CMD ["/entrypoint.sh"]
