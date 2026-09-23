ARG POSTGRES_MAJOR=17
FROM adminer:6.0.1-standalone AS adminer
FROM postgres:${POSTGRES_MAJOR}-bookworm

ARG POSTGRES_MAJOR
ARG CADDY_VERSION=2.11.4

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl php-fpm php-pgsql supervisor \
    && curl -fsSL "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_linux_amd64.tar.gz" -o /tmp/caddy.tar.gz \
    && curl -fsSL "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_checksums.txt" -o /tmp/caddy-checksums.txt \
    && grep " caddy_${CADDY_VERSION}_linux_amd64.tar.gz$" /tmp/caddy-checksums.txt | awk '{ print $1 "  /tmp/caddy.tar.gz" }' | sha512sum -c - \
    && tar -xzf /tmp/caddy.tar.gz -C /usr/bin caddy \
    && chmod 0755 /usr/bin/caddy \
    && rm /tmp/caddy.tar.gz /tmp/caddy-checksums.txt \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && ln -s "/usr/lib/postgresql/${POSTGRES_MAJOR}/bin/postgres" /usr/local/bin/postgres \
    && rm -rf /var/lib/apt/lists/*

COPY --from=adminer /var/www/html/adminer.php /var/www/adminer/public/adminer.php
COPY --from=adminer /var/www/html/plugins /var/www/adminer/public/plugins
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh
COPY adminer.sh /usr/local/bin/start-adminer
COPY Caddyfile /etc/caddy/Caddyfile.template
COPY php-fpm.conf /etc/php/8.2/fpm/php-fpm.conf
COPY adminer-index.php /var/www/adminer/public/index.php
COPY adminer-login-servers.php /var/www/adminer/public/plugins-enabled/login-servers.php

RUN chmod 0755 /entrypoint.sh /usr/local/bin/start-adminer \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
