# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:redis-7
# ----------------------------------

FROM redis:7-bookworm

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

ENV     DEBIAN_FRONTEND noninteractive

RUN apt update \
 && apt install iproute2 -y \
 && useradd -d /home/container -m container -s /bin/bash


USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
