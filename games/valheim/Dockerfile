# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-valheim
# ----------------------------------


FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN dpkg --add-architecture i386 \
    && apt update \
    && apt upgrade -y \
    && apt install -y lib32tinfo6 libtinfo6:i386 lib32z1 lib32stdc++6 libncurses5:i386 libcurl3-gnutls:i386 libsdl2-2.0-0:i386 gcc g++ libgcc1 libc++-dev gdb libc6 curl tar iproute2 net-tools libatomic1 libsdl1.2debian libsdl2-2.0-0 libfontconfig locales libcurl3-gnutls libpulse-dev libpulse0 libnss-wrapper gettext


ENV         USER=container HOME=/home/container
WORKDIR     /home/container


ENV  NSS_WRAPPER_PASSWD=/tmp/passwd NSS_WRAPPER_GROUP=/tmp/group
RUN  touch ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chgrp 0 ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chmod g+rw ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP}
ADD  /valheim/passwd.template /passwd.template

COPY  ./valheim/entrypoint.sh /entrypoint.sh
CMD   [ "/bin/bash", "/entrypoint.sh" ]