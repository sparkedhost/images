# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:php-8.5
# ----------------------------------

FROM php:8.5-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash \
    curl \
    git \
    unzip \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j"$(nproc)" \
    bcmath \
    exif \
    gd \
    intl \
    mysqli \
    pcntl \
    pdo_mysql \
    sockets \
    zip \
  && rm -f /usr/src/php.tar.xz \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && useradd -m -d /home/container container \
  && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]
