# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-unturned
# ----------------------------------

FROM ubuntu:18.04

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update
RUN apt upgrade -y
RUN apt install -y curl wget jq screen htop unzip lib32stdc++6 libc6 libgdiplus libgl1-mesa-glx libxcursor1 libxrandr2 libc6-dev
RUN useradd -d /home/container -m container

USER container
ENV  USER=container HOME=/home/container

WORKDIR /home/container

COPY ./unturned/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
