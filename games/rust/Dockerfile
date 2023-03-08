# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-rust
# ----------------------------------

FROM ubuntu:18.04

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update \
    && apt upgrade -y \
    && apt install -y lib32gcc1 lib32stdc++6 unzip curl iproute2 tzdata libgdiplus \
    && curl -sL https://deb.nodesource.com/setup_6.x | bash - \
    && apt install -y nodejs npm \
    && mkdir /node_modules \
    && npm install --prefix / ws \
    && useradd -d /home/container -m container

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./rust/entrypoint.sh /entrypoint.sh
COPY ./rust/wrapper.js /wrapper.js

CMD ["/bin/bash", "/entrypoint.sh"]
