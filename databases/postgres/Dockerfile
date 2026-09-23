ARG POSTGRES_MAJOR=18
FROM debian:trixie-slim

ARG POSTGRES_MAJOR
ARG POSTGRES_RELEASE=18.6
ARG ADMINER_VERSION=6.1.0
ARG ADMINER_SHA256=95bf24b510b41904446f720f4f1212c9e28b1d523f3df44259480d7a12ea181e
ARG CADDY_VERSION=2.11.4
ARG PHP_VERSION=8.5
ARG DEBIAN_FRONTEND=noninteractive

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host PostgreSQL ${POSTGRES_MAJOR}" \
      org.opencontainers.image.version="${POSTGRES_RELEASE}" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl supervisor \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        -o /etc/apt/keyrings/postgresql.asc \
    && echo "deb [signed-by=/etc/apt/keyrings/postgresql.asc] https://apt.postgresql.org/pub/repos/apt trixie-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list \
    && curl -fsSL https://packages.sury.org/php/apt.gpg \
        -o /usr/share/keyrings/deb.sury.org-php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ trixie main" \
        > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        "postgresql-${POSTGRES_MAJOR}" \
        php${PHP_VERSION}-fpm php${PHP_VERSION}-pgsql \
    && test "$("/usr/lib/postgresql/${POSTGRES_MAJOR}/bin/postgres" --version | awk '{print $3}')" \
        = "${POSTGRES_RELEASE}" \
    && pg_dropcluster --stop "${POSTGRES_MAJOR}" main \
    && mkdir -p /var/www/adminer/public/plugins-enabled \
    && curl -fsSL \
        "https://github.com/vrana/adminer/releases/download/v${ADMINER_VERSION}/adminer-${ADMINER_VERSION}.php" \
        -o /var/www/adminer/public/adminer.php \
    && echo "${ADMINER_SHA256}  /var/www/adminer/public/adminer.php" \
        | sha256sum -c - \
    && curl -fsSL "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_linux_amd64.tar.gz" -o /tmp/caddy.tar.gz \
    && curl -fsSL "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_checksums.txt" -o /tmp/caddy-checksums.txt \
    && grep " caddy_${CADDY_VERSION}_linux_amd64.tar.gz$" /tmp/caddy-checksums.txt | awk '{ print $1 "  /tmp/caddy.tar.gz" }' | sha512sum -c - \
    && tar -xzf /tmp/caddy.tar.gz -C /usr/bin caddy \
    && chmod 0755 /usr/bin/caddy \
    && rm /tmp/caddy.tar.gz /tmp/caddy-checksums.txt \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && ln -s "/usr/lib/postgresql/${POSTGRES_MAJOR}/bin/postgres" /usr/local/bin/postgres \
    && apt-get purge -y --auto-remove curl \
    && rm -rf /var/lib/apt/lists/*

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY entrypoint.sh /entrypoint.sh
COPY adminer.sh /usr/local/bin/start-adminer
COPY Caddyfile /etc/caddy/Caddyfile.template
COPY php-fpm.conf /etc/php/8.5/fpm/php-fpm.conf
COPY adminer-index.php /var/www/adminer/public/index.php
COPY adminer-login-servers.php /var/www/adminer/public/plugins-enabled/login-servers.php

RUN chmod 0755 /entrypoint.sh /usr/local/bin/start-adminer \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
ENV PATH="/usr/lib/postgresql/${POSTGRES_MAJOR}/bin:${PATH}"
WORKDIR /home/container
ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
