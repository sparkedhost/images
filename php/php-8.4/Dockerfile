# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:php-8.4
# ----------------------------------

FROM debian:trixie-slim

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        bash ca-certificates curl git iproute2 unzip \
    && curl -fsSL https://packages.sury.org/php/apt.gpg \
        -o /usr/share/keyrings/deb.sury.org-php.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/deb.sury.org-php.gpg] https://packages.sury.org/php/ trixie main" \
        > /etc/apt/sources.list.d/php.list \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        php8.4-bcmath \
        php8.4-cli \
        php8.4-curl \
        php8.4-gd \
        php8.4-intl \
        php8.4-mbstring \
        php8.4-mysql \
        php8.4-xml \
        php8.4-zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && useradd -m -d /home/container container \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
