# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-liteloader-bds
# ----------------------------------

FROM    debian:trixie-slim

LABEL   author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

## install required packages
RUN     dpkg --add-architecture i386 \
        && apt update -y \
        && apt install -y --no-install-recommends ca-certificates wget gnupg2 numactl tzdata libntlm0 winbind xvfb xauth python3 libncurses6:i386 libsdl2-2.0-0 libsdl2-2.0-0:i386

# Install winehq-stable and with recommends
RUN     mkdir -p /etc/apt/keyrings \
        && wget -qO /etc/apt/keyrings/winehq-archive.key https://dl.winehq.org/wine-builds/winehq.key \
        && wget -qO /etc/apt/sources.list.d/winehq-trixie.sources https://dl.winehq.org/wine-builds/debian/dists/trixie/winehq-trixie.sources \
        && apt update \
        && DEBIAN_FRONTEND=noninteractive apt install -y --install-recommends winehq-stable cabextract

# Set up Winetricks
RUN	wget -q -O /usr/sbin/winetricks https://raw.githubusercontent.com/Winetricks/winetricks/master/src/winetricks \
        && chmod +x /usr/sbin/winetricks

ENV     HOME=/home/container
ENV     WINEPREFIX=/home/container/.wine
ENV     WINEDLLOVERRIDES="mscoree,mshtml="
ENV     DISPLAY=:0
ENV     DISPLAY_WIDTH=1024
ENV     DISPLAY_HEIGHT=768
ENV     DISPLAY_DEPTH=16
ENV     AUTO_UPDATE=1
ENV     XVFB=1

USER    container
WORKDIR	/home/container

COPY     ./liteloader-bds/entrypoint.sh /entrypoint.sh
CMD      [ "/bin/bash", "/entrypoint.sh" ]
