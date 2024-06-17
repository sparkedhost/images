# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-unturned-preview
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt upgrade -y
RUN apt install -y curl wget jq unzip libc6-dev libx11-dev lib32gcc-s1
RUN useradd -d /home/container -m container

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./unturned-preview/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
