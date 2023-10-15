# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-unturned
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt upgrade -y
RUN apt install -y curl wget jq unzip libc6-dev libx11-dev lib32gcc-s1 musl-dev
RUN useradd -d /home/container -m container
RUN ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./unturned/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
