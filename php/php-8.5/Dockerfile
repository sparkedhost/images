# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:php-8.5
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
        php8.5-bcmath \
        php8.5-cli \
        php8.5-curl \
        php8.5-gd \
        php8.5-intl \
        php8.5-mbstring \
        php8.5-mysql \
        php8.5-xml \
        php8.5-zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && useradd -m -d /home/container container \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
