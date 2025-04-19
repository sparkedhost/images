# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-tuxlauncher
# ----------------------------------
FROM debian:bookworm-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

# Install required packages and WineHQ repo configuration
RUN dpkg --add-architecture i386 \
           && apt update -y \
           && apt install -y --no-install-recommends \
               curl numactl libntlm0 winbind xauth libncurses5:i386 libncurses6:i386 libsdl2-2.0-0:i386 jq libgnutls30 \
               cabextract wine-binfmt ca-certificates \
           && curl -sSL -o /etc/apt/keyrings/winehq-archive.key https://dl.winehq.org/wine-builds/winehq.key \
           && curl -sSL -o /etc/apt/sources.list.d/winehq-bookworm.sources https://dl.winehq.org/wine-builds/debian/dists/bookworm/winehq-bookworm.sources \
           && apt update -y \
           && apt install -y --no-install-recommends winehq-staging \
           && rm -rf /var/lib/apt/lists/*

# Create the container user before trying to use it
RUN useradd -m container

# Set environment variables
ENV USER=container \
        HOME=/home/container \
        WINEPREFIX=/home/container/.wine \
        WINEDLLOVERRIDES="mscoree,mshtml=" \
        AUTO_UPDATE=1

# Create and set ownership of the home directory
RUN mkdir -p /home/container && chown -R container:container /home/container

# Copy the entrypoint script as root, then set permissions as root
COPY ./tuxlauncher/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER container
WORKDIR /home/container

# Set entrypoint
CMD ["/bin/bash", "/entrypoint.sh"]



