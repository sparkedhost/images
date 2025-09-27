# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-unturned
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive

RUN apt update \
    && apt upgrade -y \
    && apt install -y curl wget jq unzip libc6 libc6-dev libx11-dev lib32gcc-s1 musl-dev \
    && useradd -d /home/container -m container \
    && ln -sf /usr/lib/x86_64-linux-gnu/libdl.so.2 /usr/lib/x86_64-linux-gnu/libdl.so \
    && ln -sf /usr/lib/x86_64-linux-gnu/libdl.so.2 /lib/libdl.so

USER container
ENV USER=container
ENV HOME=/home/container

WORKDIR /home/container

COPY ./unturned/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
