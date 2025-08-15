# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-rust
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive
ENV NODE_PATH=/home/container/node_modules

RUN apt update \
    && apt upgrade -y \
    && apt install -y curl gnupg unzip iproute2 tzdata libgdiplus lib32gcc-s1 lib32stdc++6 \
    && curl -sL https://deb.nodesource.com/setup_22.x | bash - \
    && apt update \
    && apt install -y nodejs \
    && useradd -d /home/container -m container \
    && cd /home/container && npm install ws 

USER container
ENV USER=container HOME=/home/container

WORKDIR /home/container

COPY ./rust/entrypoint.sh /entrypoint.sh
COPY ./rust/wrapper.js /wrapper.js

CMD ["/bin/bash", "/entrypoint.sh"]