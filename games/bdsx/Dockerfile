# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:games-bdsx
# ----------------------------------

FROM    alpine:latest

LABEL   author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN     apk add --no-cache --update nodejs npm wine freetype git bash \
        && adduser -D -h /home/container container

USER    container
ENV     USER=container HOME=/home/container

WORKDIR /home/container

COPY    ./bdsx/entrypoint.sh /entrypoint.sh

CMD     [ "/bin/bash", "/entrypoint.sh" ]
