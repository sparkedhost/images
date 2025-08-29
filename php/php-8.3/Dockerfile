# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:php-8.3
# ----------------------------------

FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends bash libxml2-dev libicu-dev libzip-dev unzip git \
  && docker-php-ext-install dom intl pdo_mysql zip \
  && useradd -m -d /home/container container \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./php8.3/entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]

