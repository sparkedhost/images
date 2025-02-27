# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:python-3.8
# ----------------------------------

FROM        python:3.8-slim-buster

LABEL       author="DevOps Team at Sparked Host" maintainer="devops@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates dnsutils curl iproute2 ffmpeg procps libpq-dev python3-dev pkg-config \
 && useradd -m -d /home/container container \
 && /usr/local/bin/python -m pip install --upgrade pip



USER container
ENV  USER=container HOME=/home/container TMPDIR=/home/container/.tmp

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
