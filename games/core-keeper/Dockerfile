# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-core-keeper
# ----------------------------------


FROM debian:bookworm-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN dpkg --add-architecture i386 \
    && apt update \
    && apt upgrade -y \
    && apt-get install -y tar curl gcc g++ lib32gcc-s1 libgcc-12-dev libgcc-11-dev libcurl4-gnutls-dev:i386 libssl-dev:i386 libcurl4:i386 lib32tinfo6 libtinfo6:i386 lib32z1 lib32stdc++6 libncurses5:i386 libcurl3-gnutls:i386 libsdl2-2.0-0:i386 libsdl2-2.0-0 iproute2 gdb libsdl1.2debian libfontconfig1 telnet net-tools netcat-traditional tzdata numactl xvfb wget tini \
    && useradd -d /home/container -m container

RUN         if [ "$(uname -m)" = "x86_64" ]; then \
                wget http://archive.ubuntu.com/ubuntu/pool/main/o/openssl/libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
                dpkg -i libssl1.1_1.1.0g-2ubuntu4_amd64.deb && \
                rm libssl1.1_1.1.0g-2ubuntu4_amd64.deb; \
            fi

USER container
ENV  USER container
ENV  HOME /home/container

WORKDIR /home/container

COPY ./core-keeper/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]