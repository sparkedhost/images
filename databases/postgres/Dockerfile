ARG POSTGRES_MAJOR=18
FROM debian:trixie-slim

ARG POSTGRES_MAJOR
ARG POSTGRES_RELEASE=18.6
ARG DEBIAN_FRONTEND=noninteractive

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host PostgreSQL ${POSTGRES_MAJOR}" \
      org.opencontainers.image.version="${POSTGRES_RELEASE}" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

COPY adminer/install.sh /usr/local/bin/install-adminer
COPY adminer/themes/hydra-dark /usr/share/doc/adminer/themes/hydra-dark

RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl libnss-wrapper supervisor \
    && install -d -m 0755 /etc/apt/keyrings \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc \
        -o /etc/apt/keyrings/postgresql.asc \
    && echo "deb [signed-by=/etc/apt/keyrings/postgresql.asc] https://apt.postgresql.org/pub/repos/apt trixie-pgdg main" \
        > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        "postgresql-${POSTGRES_MAJOR}" \
    && test "$("/usr/lib/postgresql/${POSTGRES_MAJOR}/bin/postgres" --version | awk '{print $3}')" \
        = "${POSTGRES_RELEASE}" \
    && pg_dropcluster --stop "${POSTGRES_MAJOR}" main \
    && bash /usr/local/bin/install-adminer pgsql \
    && useradd --create-home --home-dir /home/container --shell /bin/bash container \
    && ln -s "/usr/lib/postgresql/${POSTGRES_MAJOR}/bin/postgres" /usr/local/bin/postgres \
    && apt-get purge -y --auto-remove curl \
    && rm -rf /var/lib/apt/lists/*

COPY adminer/start-adminer /usr/local/bin/start-adminer
COPY adminer/prepare.sh /usr/local/bin/prepare-adminer
COPY adminer/Caddyfile /etc/caddy/Caddyfile.template
COPY adminer/php-fpm.conf /etc/php/8.5/fpm/php-fpm.conf
COPY adminer/index.php /var/www/adminer/public/index.php
COPY adminer/login-servers.php /var/www/adminer/public/plugins-enabled/login-servers.php
COPY adminer/supervisord.conf /etc/supervisor/conf.d/adminer.conf
COPY postgres/supervisord.conf /etc/supervisor/supervisord.conf
COPY postgres/entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh /usr/local/bin/start-adminer /usr/local/bin/prepare-adminer \
    && chown -R container:container /home/container

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
ENV PATH="/usr/lib/postgresql/${POSTGRES_MAJOR}/bin:${PATH}"
WORKDIR /home/container
ENTRYPOINT ["/bin/bash"]
CMD ["/entrypoint.sh"]
