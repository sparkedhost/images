# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:azul-prime-17
# ----------------------------------

FROM        azul/prime:17

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install fontconfig iproute2 curl -y \
 && useradd -d /home/container -m container

USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./java/entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]