# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:php-8.3
# ----------------------------------

FROM php:8.3-alpine

LABEL org.opencontainers.image.authors="sysadmin@sparkedhost.com"

RUN apk add --no-cache --update bash composer \
  && adduser --disabled-password --home /home/container container

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh
CMD ["/bin/bash", "/entrypoint.sh"]

