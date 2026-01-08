# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-fivem
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN dpkg --add-architecture i386 \
    && apt update \
    && apt upgrade -y \
    && apt install -y tar curl gcc g++ lib32gcc-s1 libgcc1 libcurl4-gnutls-dev:i386 libssl1.1:i386 libcurl4:i386 lib32tinfo6 libtinfo6:i386 lib32z1 lib32stdc++6 libncurses5:i386 libcurl3-gnutls:i386 libsdl2-2.0-0:i386 gdb libsdl1.2debian libfontconfig1 telnet net-tools netcat gcc-multilib jq xz-utils file git imagemagick \
    && useradd -d /home/container -m container 

USER container
ENV  USER container
ENV  HOME /home/container

WORKDIR /home/container

COPY ./fivem/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
