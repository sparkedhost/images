FROM debian:trixie-slim

ARG PHP_VERSION=8.5
ARG CADDY_VERSION=2.11.4
ARG PHPMYADMIN_VERSION=5.2.3
ARG DEBIAN_FRONTEND=noninteractive

ENV MARIADB_BASEDIR=/opt/mariadb \
    PHP_VERSION=${PHP_VERSION}

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates curl galera-4 galera-arbitrator-4 gnupg iproute2 \
        libaio1t64 libcrack2 libjudydebian1 liblzo2-2 libncurses6 libnuma1 \
        libodbc2 libsnappy1v5 libstdc++6 liburing2 openssl perl procps \
        rsync socat supervisor tar unzip \
    && curl -fsSL https://packages.sury.org/php/apt.gpg \
        -o /usr/share/keyrings/deb.sury.org-php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ trixie main" \
        > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php${PHP_VERSION} php${PHP_VERSION}-bcmath php${PHP_VERSION}-cli \
        php${PHP_VERSION}-curl php${PHP_VERSION}-fpm php${PHP_VERSION}-gd \
        php${PHP_VERSION}-mbstring php${PHP_VERSION}-mysql \
        php${PHP_VERSION}-xml php${PHP_VERSION}-zip \
    && ln -s /usr/lib/x86_64-linux-gnu/libaio.so.1t64 \
        /usr/lib/x86_64-linux-gnu/libaio.so.1 \
    && ln -s /usr/lib/x86_64-linux-gnu/libncurses.so.6 \
        /usr/lib/x86_64-linux-gnu/libncurses.so.5 \
    && ln -s /usr/lib/x86_64-linux-gnu/libtinfo.so.6 \
        /usr/lib/x86_64-linux-gnu/libtinfo.so.5 \
    && ln -s /usr/lib/x86_64-linux-gnu/libodbc.so.2 \
        /usr/lib/x86_64-linux-gnu/libodbc.so.1 \
    && apt-get purge -y --auto-remove gnupg \
    && rm -rf /var/lib/apt/lists/*

RUN useradd --create-home --home-dir /home/container --shell /bin/bash container

RUN mkdir -p /var/www/phpmyadmin \
    && curl -fsSL \
        "https://files.phpmyadmin.net/phpMyAdmin/${PHPMYADMIN_VERSION}/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.zip" \
        -o /tmp/phpmyadmin.zip \
    && curl -fsSL \
        "https://files.phpmyadmin.net/phpMyAdmin/${PHPMYADMIN_VERSION}/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.zip.sha256" \
        -o /tmp/phpmyadmin.sha256 \
    && sed "s#phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages.zip#/tmp/phpmyadmin.zip#" \
        /tmp/phpmyadmin.sha256 | sha256sum -c - \
    && unzip -q /tmp/phpmyadmin.zip -d /tmp \
    && cp -a "/tmp/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages/." /var/www/phpmyadmin/ \
    && rm -rf /var/www/phpmyadmin/doc /var/www/phpmyadmin/examples \
        /var/www/phpmyadmin/setup \
    && rm -rf /tmp/phpmyadmin.zip /tmp/phpmyadmin.sha256 \
        "/tmp/phpMyAdmin-${PHPMYADMIN_VERSION}-all-languages" \
    && curl -fsSL \
        "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_linux_amd64.tar.gz" \
        -o /tmp/caddy.tar.gz \
    && curl -fsSL \
        "https://github.com/caddyserver/caddy/releases/download/v${CADDY_VERSION}/caddy_${CADDY_VERSION}_checksums.txt" \
        -o /tmp/caddy-checksums.txt \
    && grep " caddy_${CADDY_VERSION}_linux_amd64.tar.gz$" /tmp/caddy-checksums.txt \
        | awk '{ print $1 "  /tmp/caddy.tar.gz" }' \
        | sha512sum -c - \
    && tar -xzf /tmp/caddy.tar.gz -C /usr/bin caddy \
    && chmod 0755 /usr/bin/caddy \
    && rm /tmp/caddy.tar.gz /tmp/caddy-checksums.txt \
    && mkdir -p /home/container/etc/pma \
    && ln -s /home/container/etc/pma/pma.conf /var/www/phpmyadmin/config.inc.php \
    && chown -R container:container /home/container /var/www/phpmyadmin

ARG MARIADB_VERSION
ARG MARIADB_SERIES

LABEL author="DevOps Team at Sparked Host" \
      maintainer="devops@sparkedhost.com" \
      org.opencontainers.image.title="Sparked Host MariaDB ${MARIADB_SERIES}" \
      org.opencontainers.image.version="${MARIADB_VERSION}" \
      org.opencontainers.image.source="https://github.com/sparkedhost/images"

ENV MARIADB_VERSION=${MARIADB_VERSION} \
    MARIADB_SERIES=${MARIADB_SERIES}

# MariaDB 10.5 S3 artifacts require unavailable CURL_OPENSSL_3 ABI.
RUN mkdir -p ${MARIADB_BASEDIR} \
    && apt-get update \
    && apt-get install -y --no-install-recommends binutils \
    && archive="mariadb-${MARIADB_VERSION}-linux-systemd-x86_64.tar.gz" \
    && curl -fsSL \
        "https://archive.mariadb.org/mariadb-${MARIADB_VERSION}/bintar-linux-systemd-x86_64/${archive}" \
        -o "/tmp/${archive}" \
    && curl -fsSL \
        "https://archive.mariadb.org/mariadb-${MARIADB_VERSION}/bintar-linux-systemd-x86_64/sha256sums.txt" \
        -o /tmp/mariadb-sha256sums.txt \
    && cd /tmp \
    && grep "${archive}$" mariadb-sha256sums.txt | sha256sum -c - \
    && tar -xzf "/tmp/${archive}" --strip-components=1 -C ${MARIADB_BASEDIR} \
    && rm "/tmp/${archive}" /tmp/mariadb-sha256sums.txt \
    && rm -rf \
        ${MARIADB_BASEDIR}/docs \
        ${MARIADB_BASEDIR}/include \
        ${MARIADB_BASEDIR}/man \
        ${MARIADB_BASEDIR}/mariadb-test \
        ${MARIADB_BASEDIR}/sql-bench \
    && rm -f \
        ${MARIADB_BASEDIR}/bin/mariadb-client-test \
        ${MARIADB_BASEDIR}/bin/mariadb-test \
        ${MARIADB_BASEDIR}/bin/mariadb_config \
        ${MARIADB_BASEDIR}/bin/mysql_client_test \
        ${MARIADB_BASEDIR}/bin/mysql_config \
        ${MARIADB_BASEDIR}/bin/mysqltest \
        ${MARIADB_BASEDIR}/bin/test-connect-t \
    && find ${MARIADB_BASEDIR}/lib -type f \
        \( -name '*.a' -o -name '*.la' \) -delete \
    && find ${MARIADB_BASEDIR}/bin ${MARIADB_BASEDIR}/lib -type f \
        -exec sh -c 'for file do strip --strip-unneeded "$file" 2>/dev/null || true; done' sh {} + \
    && if [ "$MARIADB_SERIES" = "10.5" ]; then \
         rm -f ${MARIADB_BASEDIR}/bin/aria_s3_copy \
           ${MARIADB_BASEDIR}/lib/plugin/ha_s3.so; \
       fi \
    && rm ${MARIADB_BASEDIR}/bin/garbd ${MARIADB_BASEDIR}/lib/libgalera_smm.so \
    && ln -s /usr/bin/garbd ${MARIADB_BASEDIR}/bin/garbd \
    && ln -s /usr/lib/galera/libgalera_smm.so ${MARIADB_BASEDIR}/lib/libgalera_smm.so \
    && ln -s ${MARIADB_BASEDIR}/bin/mariadb /usr/local/bin/mariadb \
    && ln -s ${MARIADB_BASEDIR}/bin/mariadb-admin /usr/local/bin/mariadb-admin \
    && ln -s ${MARIADB_BASEDIR}/bin/mariadb-upgrade /usr/local/bin/mariadb-upgrade \
    && ln -s ${MARIADB_BASEDIR}/bin/mariadbd /usr/local/bin/mariadbd \
    && ln -s ${MARIADB_BASEDIR}/scripts/mariadb-install-db /usr/local/bin/mariadb-install-db \
    && missing=0 \
    && for file in \
        $(find ${MARIADB_BASEDIR}/bin ${MARIADB_BASEDIR}/lib -type f) \
        /usr/bin/garbd /usr/lib/galera/libgalera_smm.so; do \
          unresolved="$(ldd "$file" 2>/dev/null | grep 'not found' || true)"; \
          if [ -n "$unresolved" ]; then \
            printf '%s\n%s\n' "$file" "$unresolved"; \
            missing=1; \
          fi; \
        done \
    && test "$missing" -eq 0 \
    && apt-get purge -y --auto-remove binutils \
    && rm -rf /var/lib/apt/lists/*

COPY template/etc/php-fpm.conf /etc/php/${PHP_VERSION}/fpm/php-fpm.conf
COPY template/etc/php.ini /etc/php/${PHP_VERSION}/fpm/php.ini
COPY template/etc/php.ini /etc/php/${PHP_VERSION}/cli/php.ini
COPY template/etc/pma.conf /var/www/phpmyadmin/config.inc.php.template
COPY template/etc/my.cnf /etc/mariadb/my.cnf.template
COPY template/etc/root.my.cnf /etc/mariadb/root.my.cnf.template
COPY template/etc/Caddyfile /etc/caddy/Caddyfile.template
COPY template/etc/supervisord.conf /supervisord.conf
COPY entrypoint.sh /entrypoint.sh

RUN chmod 0755 /entrypoint.sh

USER container
ENV USER=container HOME=/home/container LANG=C.UTF-8
WORKDIR /home/container
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD mariadb-admin --no-defaults --user=root \
        --socket=/home/container/run/mysqld/mysqld.sock ping --silent \
        && curl -fsS "http://127.0.0.1:${WEB_UI_PORT:-8080}/" >/dev/null \
        || exit 1
STOPSIGNAL SIGTERM
CMD ["/entrypoint.sh"]
