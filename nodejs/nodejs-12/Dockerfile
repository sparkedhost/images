# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:nodejs-12
# ----------------------------------

FROM        node:12-bullseye-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates dnsutils curl iproute2 ffmpeg procps python3 build-essential libcairo2-dev libpango1.0-dev libgif-dev librsvg2-dev libjpeg62-turbo-dev libc6 musl-dev libnss3 \
 && useradd -m -d /home/container container



RUN ln -s /usr/lib/x86_64-linux-musl/libc.so /lib/libc.musl-x86_64.so.1

USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
