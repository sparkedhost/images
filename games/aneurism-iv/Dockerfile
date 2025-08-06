# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-aneurism-iv
# ----------------------------------

FROM debian:bookworm-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND=noninteractive


RUN dpkg --add-architecture i386 \
 && apt update \
 && apt upgrade -y \
 && apt install -y --no-install-recommends tar curl gcc g++ gdb lib32gcc-s1 libgcc-s1 libcurl4:i386 libssl3 libtinfo6:i386 lib32z1 lib32stdc++6 libncurses5:i386 libsdl2-2.0-0:i386 libsdl1.2debian libfontconfig1 telnet net-tools gcc-multilib sqlite3 libsqlite3-dev ca-certificates \
 && useradd -d /home/container -m container 
USER container
ENV USER container
ENV HOME /home/container

WORKDIR /home/container

COPY ./aneurism-iv/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
