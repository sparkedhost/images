# ----------------------------------
# Sparked Host Custom Image
# Image: ghcr.io/sparkedhost/images:python-3.11
# ----------------------------------

FROM        python:3.11-slim-buster

LABEL       author="DevOps Team at Sparked Host" maintainer="sysadmin@sparkedhost.com"

RUN apt update \
 && apt -y install git gcc g++ ca-certificates dnsutils curl iproute2 ffmpeg procps libpq-dev python3-dev zlib1g \
    libtiff5-dev libjpeg8-dev libopenjp2-7-dev zlib1g-dev \
    libfreetype6-dev liblcms2-dev libwebp-dev tcl8.6-dev tk8.6-dev python3-tk \
    libharfbuzz-dev libfribidi-dev libxcb1-dev \
 && useradd -m -d /home/container container \
 && /usr/local/bin/python -m pip install --upgrade pip

USER container
ENV  USER=container HOME=/home/container TMPDIR=/home/container/.tmp

WORKDIR     /home/container

COPY        ./entrypoint.sh /entrypoint.sh

CMD         ["/bin/bash", "/entrypoint.sh"]
