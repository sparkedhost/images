# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:bot-lavalink
# ----------------------------------

FROM        openjdk:13-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install -y curl ca-certificates openssl git tar sqlite fontconfig tzdata iproute2 \
 && useradd -d /home/container -m container
 
USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./lavalink/entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
