# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:wine-game-latest
# ----------------------------------

FROM debian:trixie-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN dpkg --add-architecture i386 \
    && apt update -y \
    && apt install -y --no-install-recommends gnupg2 ca-certificates libntlm0 winbind xvfb xauth python3 libncurses6:i386 libsdl2-2.0-0 libsdl2-2.0-0:i386 wget curl telnet netcat-openbsd libgcc-s1 jq unzip \
    && mkdir -pm755 /etc/apt/keyrings \
    && wget -O /etc/apt/keyrings/winehq-archive.key https://dl.winehq.org/wine-builds/winehq.key \
    && wget -NP /etc/apt/sources.list.d/ https://dl.winehq.org/wine-builds/debian/dists/trixie/winehq-trixie.sources \
    && apt update \
    && apt install -y --install-recommends winehq-stable cabextract wine-binfmt \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

RUN wget -q -O /usr/sbin/winetricks https://raw.githubusercontent.com/Winetricks/winetricks/master/src/winetricks \
    && chmod +x /usr/sbin/winetricks

ENV HOME=/home/container
ENV WINEPREFIX=/home/container/.wine
ENV WINEDLLOVERRIDES="mscoree,mshtml="
ENV DISPLAY=:0
ENV DISPLAY_WIDTH=1024
ENV DISPLAY_HEIGHT=768
ENV DISPLAY_DEPTH=16
ENV AUTO_UPDATE=1
ENV XVFB=1

COPY ./entrypoint.sh /entrypoint.sh
COPY ./spark-utils/spark-utils.sh /spark-utils.sh
CMD ["/bin/bash", "/entrypoint.sh"]
