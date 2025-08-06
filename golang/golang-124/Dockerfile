# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:golang-124
# ----------------------------------

FROM golang:1.24

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt-get update -y \
 && apt-get install fontconfig iproute2 curl ca-certificates unzip tar jq -y \
 && useradd -d /home/container -m container

USER container
ENV  USER=container HOME=/home/container LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]