# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-arma3
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN  dpkg --add-architecture i386 \
     && apt-get update \
     && apt-get upgrade -y \
     && apt-get install -y curl locales gettext-base ca-certificates libssl-dev lib32gcc-s1 libsdl2-2.0-0 libsdl2-2.0-0:i386 libstdc++6 libstdc++6:i386 lib32stdc++6 libnss-wrapper libnss-wrapper:i386 libtbb2 libtbb2:i386 wget unzip


ENV         USER=container HOME=/home/container
WORKDIR     /home/container


ENV  NSS_WRAPPER_PASSWD=/tmp/passwd NSS_WRAPPER_GROUP=/tmp/group
RUN  touch ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chgrp 0 ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chmod g+rw ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP}
ADD  /arma3/passwd.template /passwd.template

COPY ./arma3/entrypoint.sh /entrypoint.sh

CMD ["/bin/bash", "/entrypoint.sh"]
