# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:rust-1.60
# ----------------------------------

FROM rust:1.60-slim

LABEL author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

ENV DEBIAN_FRONTEND noninteractive

RUN apt update && apt -y install git dnsutils curl iproute2 ffmpeg tini pkg-config \
    && useradd -m -d /home/container container

USER container
ENV USER=container HOME=/home/container CARGO_HOME=/home/container/.cargo
WORKDIR /home/container

COPY        ./entrypoint.sh /entrypoint.sh
ENTRYPOINT    ["/usr/bin/tini", "-g", "--"]
CMD         ["/bin/bash", "/entrypoint.sh"]
