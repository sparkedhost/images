# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:generic-wine
# ----------------------------------

FROM alpine:latest

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apk update
RUN apk add wget && apk add unzip bash
RUN apk add wine && ln -s /usr/bin/wine64 /usr/bin/wine

ENV     HOME=/home/container
ENV     WINEPREFIX=/home/container/.wine
ENV     WINEDLLOVERRIDES="mscoree,mshtml="
ENV     DISPLAY=:0
ENV     DISPLAY_WIDTH=1024
ENV     DISPLAY_HEIGHT=768
ENV     DISPLAY_DEPTH=16
ENV     AUTO_UPDATE=1
ENV     XVFB=1
ENV     WINEDEBUG=-all

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
