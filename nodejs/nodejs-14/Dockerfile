# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:nodejs-14
# ----------------------------------

FROM        node:14-bullseye-slim

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates screen dnsutils curl iproute2 ffmpeg procps python3 build-essential libcairo2-dev libpango1.0-dev libgif-dev librsvg2-dev libjpeg62-turbo-dev libc6 libnss3 \
 && useradd -m -d /home/container container

ADD ca.crt /usr/local/share/ca-certificates/sparked-ca.crt
RUN chmod 644 /usr/local/share/ca-certificates/sparked-ca.crt && update-ca-certificates

USER container
ENV  USER=container HOME=/home/container

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
