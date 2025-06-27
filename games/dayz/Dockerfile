# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-dayz
# ----------------------------------

FROM debian:bullseye-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN  dpkg --add-architecture i386 \
     && apt-get update \
     && apt-get upgrade -y \
     && apt-get install -y curl locales gettext-base ca-certificates libssl-dev lib32gcc-s1 libsdl2-2.0-0 libsdl2-2.0-0:i386 libstdc++6 libstdc++6:i386 lib32stdc++6 libnss-wrapper libnss-wrapper:i386 libtbb2 libtbb2:i386 tini tzdata libcap2 gwenhywfar-tools

ENV         USER=container HOME=/home/container
WORKDIR     /home/container

RUN         cd /tmp/ \
            && curl -sSL https://github.com/WoozyMasta/bercon/releases/download/1.0.0/bercon > bercon \
            && mv bercon /usr/local/bin/ \
            && chmod +x /usr/local/bin/bercon

RUN         update-locale lang=en_US.UTF-8 \
            && dpkg-reconfigure --frontend noninteractive locales

ENV  NSS_WRAPPER_PASSWD=/tmp/passwd NSS_WRAPPER_GROUP=/tmp/group
RUN  touch ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chgrp 0 ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP} \
     && chmod g+rw ${NSS_WRAPPER_PASSWD} ${NSS_WRAPPER_GROUP}
ADD  /dayz/passwd.template /passwd.template

RUN         useradd -m -d /home/container -s /bin/bash container
USER        container
ENV         USER=container HOME=/home/container
WORKDIR     /home/container

STOPSIGNAL  SIGINT

COPY ./dayz/entrypoint.sh /entrypoint.sh
ENTRYPOINT  ["/usr/bin/tini", "-g", "--"]
CMD ["/bin/bash", "/entrypoint.sh"]