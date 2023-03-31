# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:liteloader-bds
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update \
    && apt upgrade -y \
    && apt install -y unzip curl wget curl iproute2 tzdata libgdiplus wine \
    && curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt install -y nodejs \
    && useradd -m -d /home/container -s /bin/bash container 

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

COPY ./liteloader-bds/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
