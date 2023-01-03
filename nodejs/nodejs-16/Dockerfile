# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:nodejs-16
# ----------------------------------

FROM        node:16-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates dnsutils curl iproute2 ffmpeg procps python3 build-essential libcairo2-dev libpango1.0-dev libgif-dev librsvg2-dev libjpeg62-turbo-dev libc6 fonts-noto libtool \
 && useradd -m -d /home/container container

USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
