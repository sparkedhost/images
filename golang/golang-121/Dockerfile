# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:golang-121
# ----------------------------------

FROM golang:1.21

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

USER container
ENV  USER=container HOME=/home/container LANG=C.UTF-8

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]