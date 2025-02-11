# ----------------------------------
# Sparked Host Custom Docker Image
# Image: ghcr.io/sparkedhost/images:postgres-17
# ----------------------------------


FROM postgres:17-alpine

LABEL author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN adduser -D -h /home/container container

RUN  apk add --no-cache curl iproute2 ca-certificates fontconfig git openssl sqlite tar tzdata
USER container
ENV HOME /home/container
WORKDIR /home/container


COPY --chmod=755 --chown=root:root ../entrypoint.sh /entrypoint.sh
ENTRYPOINT         ["/entrypoint.sh"]