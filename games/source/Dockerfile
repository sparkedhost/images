# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-source
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN dpkg --add-architecture i386 \
    && apt update \
    && apt upgrade -y \
    && apt install -y tar curl gcc g++ lib32gcc-s1 libgcc1 libcurl4-gnutls-dev:i386 libssl1.1:i386 libcurl4:i386 lib32tinfo6 libtinfo6:i386 lib32z1 lib32stdc++6 libncurses5:i386 libcurl3-gnutls:i386 libsdl2-2.0-0:i386 gdb libsdl1.2debian libfontconfig1 telnet net-tools netcat \
    && useradd -d /home/container -m container \
    && cd /tmp/ \
    && curl -sSL https://github.com/gorcon/rcon-cli/releases/download/v0.10.3/rcon-0.10.3-amd64_linux.tar.gz > rcon.tar.gz \
    && tar xvf rcon.tar.gz \
    && mv rcon-0.10.3-amd64_linux/rcon /usr/local/bin/

USER container
ENV  USER container
ENV  HOME /home/container

WORKDIR /home/container

COPY ./source/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]